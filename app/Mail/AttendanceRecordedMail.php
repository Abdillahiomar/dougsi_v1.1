<?php

namespace App\Mail;

use App\Models\Attendance;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AttendanceRecordedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Attendance $attendance)
    {
    }

    public function envelope(): Envelope
    {
        $student    = $this->attendance->studentSchoolYear->student;
        $schoolName = $student->school?->name ?? 'Dugsi';
        $label      = $this->attendance->status === 'late' ? 'retard' : 'absence';

        return new Envelope(
            subject: "{$schoolName} — {$label} de {$student->fullName()}",
        );
    }

    public function content(): Content
    {
        $attendance = $this->attendance;
        $student    = $attendance->studentSchoolYear->student;

        return new Content(
            view: 'emails.attendance-recorded',
            with: [
                'studentName'  => $student->fullName(),
                'schoolName'   => $student->school?->name ?? 'Dugsi',
                'isLate'       => $attendance->status === 'late',
                'date'         => $attendance->date->format('d/m/Y'),
                'lateMinutes'  => $attendance->late_minutes,
                'sessionLabel' => $attendance->sessionLabel(),
                'className'    => $attendance->studentSchoolYear->schoolClass?->name,
            ],
        );
    }
}
