<?php

namespace App\Notifications;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use App\Services\SchoolMailerService;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

/**
 * Envoyée uniquement quand l'école de l'utilisateur a une config SMTP active
 * (vérifié en amont dans la page "mot de passe oublié" — voir
 * resources/views/pages/auth/forgot-password.blade.php). N'est jamais
 * envoyée via le mailer par défaut de la plateforme.
 */
class ResetPasswordNotification extends Notification
{
    public function __construct(private readonly string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): Mailable
    {
        /** @var User $notifiable */
        $schoolId = $notifiable->school_id;

        $mailer = app(SchoolMailerService::class);
        [$fromEmail, $fromName] = $mailer->fromAddressFor($schoolId);

        $resetUrl = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $mail = (new ResetPasswordMail($notifiable, $resetUrl))
            ->mailer($mailer->mailerNameFor($schoolId))
            ->to($notifiable->email);

        if ($fromEmail) {
            $mail->from($fromEmail, $fromName ?: ($notifiable->school?->name ?? 'Dugsi'));
        }

        return $mail;
    }
}
