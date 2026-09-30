<?php

namespace App\Notifications;

use App\Mail\AttendanceRecordedMail;
use App\Models\Attendance;
use App\Services\SchoolMailerService;
use Illuminate\Mail\Mailable;
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

    public function toMail(object $notifiable): Mailable
    {
        $student  = $this->attendance->studentSchoolYear->student;
        $schoolId = $student->school_id;

        $mailer = app(SchoolMailerService::class);
        [$fromEmail, $fromName] = $mailer->fromAddressFor($schoolId);

        $mail = (new AttendanceRecordedMail($this->attendance))
            ->mailer($mailer->mailerNameFor($schoolId))
            ->to($notifiable->email);

        if ($fromEmail) {
            $mail->from($fromEmail, $fromName ?: ($student->school?->name ?? 'Dugsi'));
        }

        return $mail;
    }
}
