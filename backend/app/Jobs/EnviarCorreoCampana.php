<?php

namespace App\Jobs;

use App\Mail\NotificacionSistemaMail;
use App\Models\DestinatarioNotificacion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EnviarCorreoCampana implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public int $recipientId) {}

    public function handle(): void
    {
        $recipient = DestinatarioNotificacion::with('campana')->find($this->recipientId);

        if (! $recipient || $recipient->estado === 'enviado' || ! $recipient->campana) {
            return;
        }

        Mail::to($recipient->correo, $recipient->nombre)
            ->send(new NotificacionSistemaMail($recipient->campana, $recipient->nombre));

        $recipient->update(['estado' => 'enviado', 'enviado_en' => now(), 'error' => null]);
        $this->refreshCampaignStatus($recipient);
    }

    public function failed(Throwable $exception): void
    {
        $recipient = DestinatarioNotificacion::with('campana')->find($this->recipientId);
        if (! $recipient) {
            return;
        }

        $recipient->update(['estado' => 'fallido', 'error' => mb_substr($exception->getMessage(), 0, 2000)]);
        $this->refreshCampaignStatus($recipient);
    }

    private function refreshCampaignStatus(DestinatarioNotificacion $recipient): void
    {
        $campaign = $recipient->campana;
        if ($campaign->destinatarios()->where('estado', 'pendiente')->exists()) {
            return;
        }

        $campaign->update([
            'estado' => $campaign->destinatarios()->where('estado', 'fallido')->exists()
                ? 'completada_con_errores'
                : 'completada',
        ]);
    }
}
