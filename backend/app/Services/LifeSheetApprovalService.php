<?php

namespace App\Services;

use App\Models\Archivo;
use App\Models\CursoVoluntario;
use App\Models\HojaVidaAnual;
use App\Models\OtroDocumentoVoluntario;
use App\Models\SolicitudHojaVida;
use App\Models\TituloVoluntario;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Centraliza el flujo pendiente/aprobado/rechazado de antecedentes de la hoja
 * de vida. Es compartido por HojaVidaAnualController y
 * SolicitudHojaVidaController para evitar duplicar archivos y notificaciones.
 */
class LifeSheetApprovalService
{
    private const DEFINITIONS = [
        'titulo' => ['key' => 'titulos', 'relation' => 'titulos', 'model' => TituloVoluntario::class, 'fields' => ['titulo', 'entregado_por', 'codigo_titulo'], 'entity' => 'titulo_voluntario', 'category' => 'respaldo_titulo'],
        'curso' => ['key' => 'cursos', 'relation' => 'cursos', 'model' => CursoVoluntario::class, 'fields' => ['nombre_curso', 'entregado_por', 'codigo_curso'], 'entity' => 'curso_voluntario', 'category' => 'respaldo_curso'],
        'documento' => ['key' => 'otros_documentos', 'relation' => 'otrosDocumentos', 'model' => OtroDocumentoVoluntario::class, 'fields' => ['nombre_documento', 'motivo'], 'entity' => 'otro_documento_voluntario', 'category' => 'respaldo_otro_documento'],
    ];

    public function __construct(
        private readonly ImageOptimizer $imageOptimizer,
        private readonly NotificationCampaignService $notificationCampaigns
    ) {}

    public function capture(HojaVidaAnual $sheet, array $validated, Request $request): Collection
    {
        $created = collect();

        foreach (self::DEFINITIONS as $type => $definition) {
            $existing = $sheet->{$definition['relation']}()->with('archivosAdjuntos')->get()->keyBy('id');
            $submittedIds = collect();

            foreach ($validated[$definition['key']] ?? [] as $row) {
                $record = ! empty($row['id']) ? $existing->get((int) $row['id']) : null;
                if (! empty($row['id']) && ! $record instanceof $definition['model']) {
                    throw ValidationException::withMessages([$definition['key'] => ['Uno de los registros no pertenece a esta hoja de vida.']]);
                }

                if ($record) {
                    $submittedIds->push($record->id);
                }

                $data = collect($definition['fields'])->mapWithKeys(fn ($field) => [$field => $row[$field] ?? null])->all();
                $retainedIds = collect($row['archivo_ids'] ?? [])->filter()->map(fn ($id) => (int) $id)->unique()->values();
                $newFiles = collect($row['archivos'] ?? [])->filter()->values();

                if ($record && ! $this->hasChanges($record, $data, $retainedIds, $newFiles)) {
                    continue;
                }

                $pendingQuery = SolicitudHojaVida::query()->where('estado', 'pendiente')->where('tipo_registro', $type);
                $record
                    ? $pendingQuery->where('registro_id', $record->id)
                    : $pendingQuery->where('hoja_vida_anual_id', $sheet->id)->whereNull('registro_id')->where('datos', json_encode($data));

                if ($pendingQuery->exists()) {
                    throw ValidationException::withMessages([$definition['key'] => ['Ya existe una solicitud pendiente para uno de estos registros.']]);
                }

                $approval = SolicitudHojaVida::create([
                    'hoja_vida_anual_id' => $sheet->id,
                    'voluntario_id' => $sheet->voluntario_id,
                    'tipo_registro' => $type,
                    'accion' => $record ? 'actualizar' : 'crear',
                    'registro_id' => $record?->id,
                    'datos' => $data,
                    'archivo_ids_conservados' => $retainedIds->all(),
                    'estado' => 'pendiente',
                    'solicitada_por' => $request->user()?->id,
                ]);

                $this->stageFiles($approval, $newFiles, $request->user());
                $created->push($approval->load('archivos'));
            }

            foreach ($existing->except($submittedIds->all()) as $record) {
                if (SolicitudHojaVida::query()->where('estado', 'pendiente')->where('tipo_registro', $type)->where('registro_id', $record->id)->exists()) {
                    continue;
                }

                $approval = SolicitudHojaVida::create([
                    'hoja_vida_anual_id' => $sheet->id,
                    'voluntario_id' => $sheet->voluntario_id,
                    'tipo_registro' => $type,
                    'accion' => 'eliminar',
                    'registro_id' => $record->id,
                    'datos' => collect($definition['fields'])->mapWithKeys(fn ($field) => [$field => $record->{$field}])->all(),
                    'archivo_ids_conservados' => [],
                    'estado' => 'pendiente',
                    'solicitada_por' => $request->user()?->id,
                ]);
                $created->push($approval);
            }
        }

        foreach ($created as $approval) {
            $this->notificationCampaigns->notifyLifeSheetRequest($approval, $request->user());
        }

        return $created;
    }

    public function approve(SolicitudHojaVida $approval, User $reviewer, ?string $reason = null): SolicitudHojaVida
    {
        return DB::transaction(function () use ($approval, $reviewer, $reason) {
            $approval = SolicitudHojaVida::query()->lockForUpdate()->findOrFail($approval->id);
            $this->ensurePending($approval);
            $definition = $this->definition($approval->tipo_registro);
            $sheet = $approval->hojaVidaAnual;
            $record = $approval->registro_id ? $definition['model']::find($approval->registro_id) : null;

            if ($approval->accion === 'eliminar') {
                if ($record) {
                    $this->deleteRecord($record);
                }
            } else {
                if ($approval->accion === 'actualizar' && ! $record) {
                    throw ValidationException::withMessages(['solicitud' => ['El registro original ya no existe.']]);
                }

                $record = $record ?: $sheet->{$definition['relation']}()->create($approval->datos);
                $record->update($approval->datos);
                $this->applyFiles($approval, $record, $definition);
            }

            $approval->update([
                'estado' => 'aprobada',
                'revisada_por' => $reviewer->id,
                'motivo_revision' => $reason,
                'revisada_en' => now(),
            ]);

            return $approval->fresh(['voluntario.user', 'archivos', 'revisor.voluntario']);
        });
    }

    public function reject(SolicitudHojaVida $approval, User $reviewer, string $reason): SolicitudHojaVida
    {
        $this->ensurePending($approval);

        DB::transaction(function () use ($approval, $reviewer, $reason) {
            $approval->archivos()->get()->each(fn (Archivo $file) => $this->deleteFile($file));
            $approval->update([
                'estado' => 'rechazada',
                'revisada_por' => $reviewer->id,
                'motivo_revision' => $reason,
                'revisada_en' => now(),
            ]);
        });

        return $approval->fresh(['voluntario.user', 'revisor.voluntario']);
    }

    private function hasChanges(Model $record, array $data, Collection $retainedIds, Collection $newFiles): bool
    {
        foreach ($data as $field => $value) {
            if ((string) ($record->{$field} ?? '') !== (string) ($value ?? '')) {
                return true;
            }
        }

        $currentIds = $record->archivosAdjuntos->pluck('id')->map(fn ($id) => (int) $id)->sort()->values();
        return $newFiles->isNotEmpty() || $currentIds->all() !== $retainedIds->sort()->values()->all();
    }

    private function stageFiles(SolicitudHojaVida $approval, Collection $files, ?User $actor): void
    {
        foreach ($files as $file) {
            $directory = 'hoja-vida/solicitudes/'.$approval->id;
            if (str_starts_with((string) $file->getMimeType(), 'image/')) {
                $stored = $this->imageOptimizer->store($file, $directory);
                $path = $stored['path'];
                $extension = $stored['extension'];
                $mime = $stored['mime_type'];
                $size = $stored['size'];
                $name = $stored['original_name'];
            } else {
                $path = $file->store($directory, 'public');
                $extension = $file->getClientOriginalExtension();
                $mime = $file->getClientMimeType();
                $size = $file->getSize();
                $name = $file->getClientOriginalName();
            }

            Archivo::create([
                'entidad' => 'solicitud_hoja_vida', 'entidad_id' => $approval->id,
                'categoria' => 'respaldo_solicitud_hoja_vida', 'ruta' => $path,
                'nombre_original' => $name, 'extension' => $extension, 'mime_type' => $mime,
                'tamano' => $size, 'subido_por' => $actor?->id,
            ]);
        }
    }

    private function applyFiles(SolicitudHojaVida $approval, Model $record, array $definition): void
    {
        $retainedIds = collect($approval->archivo_ids_conservados ?? [])->map(fn ($id) => (int) $id);
        $current = $record->archivosAdjuntos()->get()->keyBy('id');
        if ($retainedIds->diff($current->keys())->isNotEmpty()) {
            throw ValidationException::withMessages(['archivos' => ['Uno de los archivos originales ya no está disponible.']]);
        }

        $current->except($retainedIds->all())->each(fn (Archivo $file) => $this->deleteFile($file));
        $approval->archivos()->get()->each(fn (Archivo $file) => $file->update([
            'entidad' => $definition['entity'], 'entidad_id' => $record->id, 'categoria' => $definition['category'],
        ]));
        $record->update(['archivo_id' => $record->archivosAdjuntos()->orderBy('id')->value('id')]);
    }

    private function deleteRecord(Model $record): void
    {
        $record->archivosAdjuntos()->get()->each(fn (Archivo $file) => $this->deleteFile($file));
        $record->delete();
    }

    private function deleteFile(Archivo $file): void
    {
        if ($file->ruta) Storage::disk('public')->delete($file->ruta);
        $file->delete();
    }

    private function ensurePending(SolicitudHojaVida $approval): void
    {
        if ($approval->estado !== 'pendiente') {
            throw ValidationException::withMessages(['solicitud' => ['Esta solicitud ya fue revisada.']]);
        }
    }

    private function definition(string $type): array
    {
        return self::DEFINITIONS[$type] ?? throw ValidationException::withMessages(['tipo_registro' => ['Tipo de registro no válido.']]);
    }
}
