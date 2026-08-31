<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $resetUrl,
        public int $expireMinutes = 60,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Restablecer contraseña - Andrea',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildHtml(),
        );
    }

    private function buildHtml(): string
    {
        $name = e($this->user->name);
        $url = e($this->resetUrl);
        $minutes = e((string) $this->expireMinutes);

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; color: #222; line-height: 1.5;">
  <p>Hola {$name},</p>
  <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en <strong>Andrea</strong>.</p>
  <p>
    <a href="{$url}" style="display:inline-block;padding:10px 16px;background:#0d6efd;color:#fff;text-decoration:none;border-radius:4px;">
      Restablecer contraseña
    </a>
  </p>
  <p>O copia y pega este enlace en tu navegador:</p>
  <p style="word-break:break-all;">{$url}</p>
  <p>Este enlace expirará en {$minutes} minutos.</p>
  <p>Si no solicitaste este cambio, puedes ignorar este correo.</p>
  <p>— Equipo Andrea</p>
</body>
</html>
HTML;
    }
}
