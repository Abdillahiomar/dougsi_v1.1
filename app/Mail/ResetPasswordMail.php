<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $resetUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        $schoolName = $this->user->school?->name ?? 'Dugsi';

        return new Envelope(
            subject: "{$schoolName} — Réinitialisation de votre mot de passe",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reset-password',
            with: [
                'userName'   => $this->user->name,
                'schoolName' => $this->user->school?->name ?? 'Dugsi',
                'resetUrl'   => $this->resetUrl,
            ],
        );
    }
}
