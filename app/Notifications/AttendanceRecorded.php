<?php

namespace App\Notifications;

use App\Models\Attendance;
use App\Services\SchoolMailerService;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifie un tuteur qu'une absence ou un retard vient d'être enregistré
 * pour son enfant. Envoyée immédiatement (pas en file d'attente) pour
 * garantir la tentative d'envoi sans dépendre d'un worker de queue actif.
 */
class AttendanceRecorded extends Notification
{
    public function __construct(private readonly Attendance $attendance)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $student    = $this->attendance->studentSchoolYear->student;
        $schoolId   = $student->school_id;
        $schoolName = $student->school?->name ?? 'Dugsi';

        $mailer = app(SchoolMailerService::class);
        [$fromEmail, $fromName] = $mailer->fromAddressFor($schoolId);

        $isLate = $this->attendance->status === 'late';
        $label  = $isLate ? 'retard' : 'absence';

        $message = (new MailMessage)
            ->mailer($mailer->mailerNameFor($schoolId))
            ->subject("{$schoolName} — {$label} de {$student->fullName()}")
            ->greeting('Bonjour,')
            ->line(sprintf(
                "Nous vous informons que %s a été marqué%s %s le %s.",
                $student->fullName(),
                $isLate ? '' : '(e)',
                $isLate ? 'en retard' : 'absent(e)',
                $this->attendance->date->format('d/m/Y'),
            ));

        if ($isLate && $this->attendance->late_minutes) {
            $message->line("Durée du retard : {$this->attendance->late_minutes} minute(s).");
        }

        $sessionLabel = $this->attendance->sessionLabel();
        if ($sessionLabel) {
            $message->line("Séance concernée : {$sessionLabel}");
        }

        if ($className = $this->attendance->studentSchoolYear->schoolClass?->name) {
            $message->line("Classe : {$className}");
        }

        $message->salutation('— ' . ($fromName ?: $schoolName));

        if ($fromEmail) {
            $message->from($fromEmail, $fromName ?: $schoolName);
        }

        return $message;
    }
}
