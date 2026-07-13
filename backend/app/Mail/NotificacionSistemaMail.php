<?php

namespace App\Mail;

use App\Models\CampanaNotificacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificacionSistemaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CampanaNotificacion $campana, public ?string $recipientName = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->campana->asunto_correo);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.notificacion-sistema');
    }
}
