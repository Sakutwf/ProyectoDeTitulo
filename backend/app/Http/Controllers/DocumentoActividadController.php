<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\DocumentoActividad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DocumentoActividadController extends Controller
{
    private const DOCUMENT_TYPES = [
        'analisis_contexto',
        'informe_narrativo',
    ];

    private const DOCUMENT_STATES = [
        'borrador',
        'final',
    ];

    private const RELATIONS = [
        'actividad.filial',
        'actividad.creador',
        'generador',
    ];

    public function index(Actividad $actividad)
    {
        return response()->json(
            $actividad->documentos()
                ->with(['generador'])
                ->latest('updated_at')
                ->latest('id')
                ->get(),
            200
        );
    }

    public function prefill(Actividad $actividad, Request $request)
    {
        $type = $this->normalizeDocumentType($request->query('tipo_documento'));

        return response()->json([
            'tipo_documento' => $type,
            'titulo_sugerido' => $this->buildSuggestedTitle($actividad, $type),
            'prefill' => $this->buildPrefillPayload($actividad, $type),
            'contenido_inicial' => $this->buildInitialContent($type),
        ], 200);
    }

    public function store(Actividad $actividad, Request $request)
    {
        $validated = $this->validateDocumentRequest($request, $actividad);

        $documento = DB::transaction(function () use ($actividad, $request, $validated) {
            return DocumentoActividad::create([
                'actividad_id' => $actividad->id,
                'tipo_documento' => $validated['tipo_documento'],
                'titulo' => $validated['titulo'],
                'estado' => $validated['estado'] ?? 'borrador',
                'fecha_documento' => $validated['fecha_documento'] ?? now()->toDateString(),
                'datos_contexto' => $validated['datos_contexto'] ?? $this->buildPrefillPayload($actividad, $validated['tipo_documento']),
                'contenido' => $validated['contenido'] ?? $this->buildInitialContent($validated['tipo_documento']),
                'ruta_pdf' => $validated['ruta_pdf'] ?? null,
                'generado_por' => $validated['generado_por'] ?? $request->user()?->id,
            ]);
        });

        return response()->json($documento->load(self::RELATIONS), 201);
    }

    public function show(DocumentoActividad $documentoActividad)
    {
        return response()->json(
            $documentoActividad->load(self::RELATIONS),
            200
        );
    }

    public function update(Request $request, DocumentoActividad $documentoActividad)
    {
        $validated = $this->validateDocumentRequest($request, $documentoActividad->actividad, true);

        $documentoActividad = DB::transaction(function () use ($documentoActividad, $request, $validated) {
            $documentoActividad->update([
                'tipo_documento' => $validated['tipo_documento'] ?? $documentoActividad->tipo_documento,
                'titulo' => $validated['titulo'] ?? $documentoActividad->titulo,
                'estado' => $validated['estado'] ?? $documentoActividad->estado,
                'fecha_documento' => $validated['fecha_documento'] ?? $documentoActividad->fecha_documento,
                'datos_contexto' => $validated['datos_contexto'] ?? $documentoActividad->datos_contexto,
                'contenido' => $validated['contenido'] ?? $documentoActividad->contenido,
                'ruta_pdf' => $validated['ruta_pdf'] ?? $documentoActividad->ruta_pdf,
                'generado_por' => $validated['generado_por'] ?? $documentoActividad->generado_por ?? $request->user()?->id,
            ]);

            return $documentoActividad->fresh()->load(self::RELATIONS);
        });

        return response()->json($documentoActividad, 200);
    }

    public function destroy(DocumentoActividad $documentoActividad)
    {
        $documentoActividad->delete();

        return response()->json(null, 204);
    }

    private function validateDocumentRequest(Request $request, Actividad $actividad, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';
        $request->merge([
            'tipo_documento' => $this->normalizeDocumentType($request->input('tipo_documento')),
        ]);

        return $request->validate([
            'tipo_documento' => [$required, 'string', Rule::in(self::DOCUMENT_TYPES)],
            'titulo' => [$required, 'string', 'max:200'],
            'estado' => ['nullable', 'string', Rule::in(self::DOCUMENT_STATES)],
            'fecha_documento' => ['nullable', 'date'],
            'datos_contexto' => ['nullable', 'array'],
            'contenido' => ['nullable', 'array'],
            'ruta_pdf' => ['nullable', 'string', 'max:255'],
            'generado_por' => ['nullable', 'integer', 'exists:users,id'],
        ]);
    }

    private function normalizeDocumentType($type): string
    {
        $normalized = is_string($type) ? trim(mb_strtolower($type)) : '';

        return in_array($normalized, self::DOCUMENT_TYPES, true)
            ? $normalized
            : self::DOCUMENT_TYPES[0];
    }

    private function buildSuggestedTitle(Actividad $actividad, string $type): string
    {
        $prefix = $type === 'informe_narrativo' ? 'Informe narrativo' : 'Analisis de contexto';

        return trim($prefix.' - '.$actividad->nombre);
    }

    private function buildInitialContent(string $type): array
    {
        $base = [
            'resumen' => '',
            'desarrollo' => '',
            'resultados' => '',
            'dificultades' => '',
            'conclusiones' => '',
            'recomendaciones' => '',
            'observaciones' => '',
        ];

        if ($type === 'analisis_contexto') {
            return array_merge($base, [
                'analisis_contextual' => '',
                'diagnostico' => '',
                'oportunidades' => '',
                'riesgos' => '',
            ]);
        }

        return array_merge($base, [
            'introduccion' => '',
            'metodologia' => '',
            'participacion_comunitaria' => '',
        ]);
    }

    private function buildPrefillPayload(Actividad $actividad, string $type): array
    {
        $actividad->loadMissing([
            'filial',
            'creador',
            'voluntarios.user',
            'galeria.archivo',
            'boletasViatico.voluntario.user',
            'boletasViatico.archivo',
        ]);

        $participants = $actividad->voluntarios
            ->map(function ($voluntario) {
                return [
                    'id' => $voluntario->id,
                    'nombre' => trim(collect([$voluntario->nombres, $voluntario->apellidos])->filter()->implode(' ')),
                    'registro_filial' => $voluntario->registro_filial,
                    'rut' => $voluntario->rut,
                    'horas_asistidas' => (float) ($voluntario->pivot?->horas_asistidas ?? 0),
                ];
            })
            ->values()
            ->all();

        $boletas = $actividad->boletasViatico
            ->map(function ($boleta) {
                return [
                    'id' => $boleta->id,
                    'voluntario' => trim(collect([
                        $boleta->voluntario?->nombres,
                        $boleta->voluntario?->apellidos,
                    ])->filter()->implode(' ')),
                    'detalle_compra' => $boleta->detalle_compra,
                    'monto' => (float) $boleta->monto,
                    'fecha_compra' => optional($boleta->fecha_compra)->format('Y-m-d'),
                    'estado' => $boleta->estado,
                ];
            })
            ->values()
            ->all();

        $galeria = $actividad->galeria
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'titulo' => $item->titulo,
                    'descripcion' => $item->descripcion,
                    'fecha' => optional($item->fecha)->format('Y-m-d'),
                    'imagen_url' => $item->imagen_url,
                ];
            })
            ->values()
            ->all();

        return [
            'tipo_documento' => $type,
            'actividad' => [
                'id' => $actividad->id,
                'nombre' => $actividad->nombre,
                'tipo' => $actividad->tipo,
                'objetivo' => $actividad->objetivo,
                'fecha_inicio' => optional($actividad->fecha_inicio)->format('Y-m-d'),
                'fecha_termino' => optional($actividad->fecha_termino)->format('Y-m-d'),
                'hora_inicio' => $actividad->hora_inicio,
                'hora_termino' => $actividad->hora_termino,
                'lugar' => $actividad->lugar,
                'horas_totales' => (float) ($actividad->horas_totales ?? 0),
                'colaborador_externo' => $actividad->colaborador_externo,
            ],
            'filial' => [
                'id' => $actividad->filial?->id,
                'nombre' => $actividad->filial?->nombre,
                'cut' => $actividad->filial?->cut,
                'comite_regional' => $actividad->filial?->comite_regional,
                'direccion' => $actividad->filial?->direccion,
                'comuna' => $actividad->filial?->comuna,
            ],
            'creador' => [
                'id' => $actividad->creador?->id,
                'name' => $actividad->creador?->name,
                'username' => $actividad->creador?->username,
            ],
            'participantes' => $participants,
            'resumen' => [
                'total_participantes' => count($participants),
                'total_horas_participantes' => round(collect($participants)->sum('horas_asistidas'), 2),
                'total_evidencias' => count($galeria),
                'total_boletas' => count($boletas),
                'monto_total_boletas' => round(collect($boletas)->sum('monto'), 2),
            ],
            'boletas' => $boletas,
            'galeria' => $galeria,
        ];
    }
}
