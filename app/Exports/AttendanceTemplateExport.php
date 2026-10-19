<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Modèle Excel pré-rempli avec la liste des élèves d'une classe, pour
 * import en masse des présences (voir absences/index.blade.php).
 */
class AttendanceTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
     * @param  Collection<int, \App\Models\StudentSchoolYear>  $students
     * @param  Collection<int, \App\Models\Attendance>  $existing  indexée par student_school_year_id
     */
    public function __construct(
        private readonly Collection $students,
        private readonly Collection $existing,
    ) {
    }

    public function headings(): array
    {
        return ['Matricule', 'Nom complet', 'Statut', 'Minutes de retard', 'Justification'];
    }

    public function collection(): Collection
    {
        return $this->students->map(function ($ssy) {
            $record = $this->existing->get($ssy->id);

            return [
                $ssy->student->matricule,
                $ssy->student->fullName(),
                $record->status ?? 'present',
                $record->late_minutes ?? '',
                $record->justification ?? '',
            ];
        });
    }
}
