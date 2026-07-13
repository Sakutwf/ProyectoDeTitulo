<?php

namespace App\Services;

use App\Jobs\EnviarCorreoCampana;
use App\Models\Actividad;
use App\Models\BoletaViatico;
use App\Models\CampanaNotificacion;
use App\Models\SolicitudHojaVida;
use App\Models\User;
use App\Models\Voluntario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NotificationCampaignService
{
    public function availableVolunteers(): Collection
    {
        return Voluntario::query()
            ->with('user')
            ->whereNotNull('correo_electronico')
            ->where('correo_electronico', '<>', '')
            ->orderBy('nombres')
            ->orderBy('apellidos')
            ->get()
            ->filter(fn (Voluntario $volunteer) => filter_var($volunteer->correo_electronico, FILTER_VALIDATE_EMAIL))
            ->map(fn (Voluntario $volunteer) => [
                'id' => $volunteer->id,
                'user_id' => $volunteer->user_id,
                'nombre' => trim($volunteer->nombres.' '.$volunteer->apellidos),
                'correo' => $volunteer->correo_electronico,
                'filial_id' => $volunteer->filial_id,
            ])
            ->values();
    }

    public function sendActivity(Actividad $activity, string $kind, array $volunteerIds, User $actor): CampanaNotificacion
    {
        if (! in_array($kind, ['nueva_actividad', 'actividad_modificada'], true)) {
            throw ValidationException::withMessages(['tipo' => ['El tipo de aviso no es válido.']]);
        }

        $fingerprint = hash('sha256', json_encode($this->activityMetadata($activity), JSON_UNESCAPED_UNICODE));
        $query = CampanaNotificacion::query()
            ->where('tipo', $kind)
            ->where('asunto_type', $activity->getMorphClass())
            ->where('asunto_id', $activity->id);

        if ($kind === 'nueva_actividad' ? $query->exists() : $query->where('huella', $fingerprint)->exists()) {
            throw ValidationException::withMessages([
                'notificacion' => [$kind === 'nueva_actividad'
                    ? 'El aviso inicial de esta actividad ya fue autorizado.'
                    : 'Estas mismas modificaciones ya fueron notificadas.'],
            ]);
        }

        $selected = $this->availableVolunteers()
            ->whereIn('id', array_map('intval', $volunteerIds))
            ->values();

        if ($selected->isEmpty()) {
            throw ValidationException::withMessages(['destinatarios' => ['Selecciona al menos un voluntario con correo válido.']]);
        }

        $isNew = $kind === 'nueva_actividad';
        $subject = ($isNew ? 'Nueva actividad: ' : 'Actividad modificada: ').$activity->nombre;
        $message = ($isNew
            ? "Se ha publicado una nueva actividad y te invitamos a revisarla."
            : "Se realizaron modificaciones en una actividad previamente informada.")
            .$this->activitySummary($activity);

        return $this->createCampaign(
            $kind,
            $activity,
            $subject,
            $message,
            [...$this->activityMetadata($activity), 'url' => $this->frontendUrl('/mis-actividades')],
            $selected,
            $actor,
            $fingerprint
        );
    }

    public function notifyReceiptSubmitted(BoletaViatico $receipt, ?User $actor): ?CampanaNotificacion
    {
        $receipt->loadMissing('actividad', 'voluntario.user', 'voluntario.filial');
        $reviewers = $this->reviewersForFilial($receipt->voluntario?->filial_id);
        if ($reviewers->isEmpty()) {
            return null;
        }

        return $this->createCampaign(
            'boleta_ingresada',
            $receipt,
            'Nueva boleta pendiente de revisión',
            "{$receipt->voluntario?->nombres} {$receipt->voluntario?->apellidos} registró una boleta por $".number_format((float) $receipt->monto, 0, ',', '.').".\n\nActividad: {$receipt->actividad?->nombre}\nDetalle: {$receipt->detalle_compra}",
            ['url' => $this->frontendUrl('/boletas'), 'estado' => $receipt->estado],
            $reviewers,
            $actor,
            hash('sha256', 'boleta-ingresada-'.$receipt->id)
        );
    }

    public function notifyReceiptStatus(BoletaViatico $receipt, User $actor): ?CampanaNotificacion
    {
        $receipt->loadMissing('actividad', 'voluntario.user');
        $recipient = $this->volunteerRecipient($receipt->voluntario);
        if (! $recipient) {
            return null;
        }

        return $this->createCampaign(
            'estado_boleta',
            $receipt,
            'Actualización de tu boleta: '.ucfirst($receipt->estado),
            "El estado de tu boleta para la actividad {$receipt->actividad?->nombre} cambió a: ".strtoupper($receipt->estado).'.'
                .($receipt->motivo_revision ? "\n\nMotivo: {$receipt->motivo_revision}" : ''),
            ['url' => $this->frontendUrl('/mis-boletas'), 'estado' => $receipt->estado],
            collect([$recipient]),
            $actor,
            hash('sha256', 'boleta-'.$receipt->id.'-'.$receipt->estado)
        );
    }

    public function notifyLifeSheetRequest(SolicitudHojaVida $request, ?User $actor): ?CampanaNotificacion
    {
        $request->loadMissing('voluntario.filial');
        $reviewers = $this->reviewersForFilial($request->voluntario?->filial_id);
        if ($reviewers->isEmpty()) {
            return null;
        }

        return $this->createCampaign(
            'solicitud_hoja_vida',
            $request,
            'Nueva modificación de hoja de vida pendiente',
            trim($request->voluntario?->nombres.' '.$request->voluntario?->apellidos)." solicitó {$request->accion} un registro de {$request->tipo_registro}.",
            ['url' => $this->frontendUrl('/solicitudes-hoja-vida')],
            $reviewers,
            $actor,
            hash('sha256', 'solicitud-hoja-'.$request->id)
        );
    }

    public function notifyLifeSheetDecision(SolicitudHojaVida $request, User $actor): ?CampanaNotificacion
    {
        $request->loadMissing('voluntario.user');
        $recipient = $this->volunteerRecipient($request->voluntario);
        if (! $recipient) {
            return null;
        }

        return $this->createCampaign(
            'resultado_solicitud_hoja_vida',
            $request,
            'Resultado de tu solicitud de hoja de vida',
            "Tu solicitud para {$request->accion} un registro de {$request->tipo_registro} fue {$request->estado}.".($request->motivo_revision ? "\n\nMotivo: {$request->motivo_revision}" : ''),
            ['url' => $this->frontendUrl('/historial/'.$request->voluntario?->user_id)],
            collect([$recipient]),
            $actor,
            hash('sha256', 'resultado-solicitud-'.$request->id.'-'.$request->estado)
        );
    }

    private function createCampaign(string $type, Model $subject, string $emailSubject, string $message, array $metadata, Collection $recipients, ?User $actor, string $fingerprint): CampanaNotificacion
    {
        $campaign = DB::transaction(function () use ($type, $subject, $emailSubject, $message, $metadata, $recipients, $actor, $fingerprint) {
            $campaign = CampanaNotificacion::create([
                'tipo' => $type,
                'asunto_type' => $subject->getMorphClass(),
                'asunto_id' => $subject->getKey(),
                'asunto_correo' => $emailSubject,
                'mensaje' => $message,
                'metadatos' => $metadata,
                'huella' => $fingerprint,
                'estado' => 'encolada',
                'autorizada_por' => $actor?->id,
                'autorizada_en' => now(),
            ]);

            foreach ($recipients->unique('correo') as $recipient) {
                $campaign->destinatarios()->create([
                    'user_id' => $recipient['user_id'] ?? null,
                    'nombre' => $recipient['nombre'] ?? null,
                    'correo' => mb_strtolower(trim($recipient['correo'])),
                    'estado' => 'pendiente',
                ]);
            }

            return $campaign->load('destinatarios');
        });

        foreach ($campaign->destinatarios as $recipient) {
            EnviarCorreoCampana::dispatch($recipient->id)->afterCommit();
        }

        return $campaign;
    }

    private function reviewersForFilial(?int $filialId): Collection
    {
        return User::query()
            ->with(['roles', 'voluntario'])
            ->whereHas('roles', fn ($query) => $query->whereIn('clave', ['administrador', 'secretario-directiva']))
            ->get()
            ->filter(function (User $user) use ($filialId) {
                if (! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                    return false;
                }
                $reviewerFilial = $user->voluntario?->filial_id;
                return ! $reviewerFilial || ! $filialId || (int) $reviewerFilial === (int) $filialId;
            })
            ->map(fn (User $user) => ['user_id' => $user->id, 'nombre' => $user->name, 'correo' => $user->email])
            ->values();
    }

    private function volunteerRecipient(?Voluntario $volunteer): ?array
    {
        if (! $volunteer || ! filter_var($volunteer->correo_electronico, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return [
            'user_id' => $volunteer->user_id,
            'nombre' => trim($volunteer->nombres.' '.$volunteer->apellidos),
            'correo' => $volunteer->correo_electronico,
        ];
    }

    private function activityMetadata(Actividad $activity): array
    {
        return [
            'nombre' => $activity->nombre,
            'tipo' => $activity->tipo,
            'fecha_inicio' => optional($activity->fecha_inicio)->format('Y-m-d'),
            'fecha_termino' => optional($activity->fecha_termino)->format('Y-m-d'),
            'hora_inicio' => $activity->hora_inicio,
            'hora_termino' => $activity->hora_termino,
            'lugar' => $activity->lugar,
            'objetivo' => $activity->objetivo,
        ];
    }

    private function activitySummary(Actividad $activity): string
    {
        return "\n\nFecha: ".optional($activity->fecha_inicio)->format('d-m-Y')
            .($activity->hora_inicio ? "\nHora: {$activity->hora_inicio}" : '')
            .($activity->lugar ? "\nLugar: {$activity->lugar}" : '')
            .($activity->objetivo ? "\nObjetivo: {$activity->objetivo}" : '');
    }

    private function frontendUrl(string $path): string
    {
        return rtrim((string) config('app.frontend_url', config('app.url')), '/').'/'.ltrim($path, '/');
    }
}
