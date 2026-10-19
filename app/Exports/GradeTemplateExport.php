<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Modèle Excel pré-rempli avec la liste des élèves d'une classe pour une
 * évaluation donnée, pour import en masse des notes (voir grades/index.blade.php).
 */
class GradeTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
     * @param  Collection<int, \App\Models\StudentSchoolYear>  $students
     * @param  Collection<int, \App\Models\Grade>  $existing  indexée par student_school_year_id
     */
    public function __construct(
        private readonly Collection $students,
        private readonly Collection $existing,
        private readonly int|float $maxScore,
    ) {
    }

    public function headings(): array
    {
        // Le libellé reste fixe (pas de barème dans l'en-tête) : la validation
        // par ligne lit la colonne par son nom normalisé ("note"), qui doit
        // rester stable quel que soit le barème de l'évaluation.
        return ['Matricule', 'Nom complet', 'Note'];
    }

    public function collection(): Collection
    {
        return $this->students->map(function ($ssy) {
            $grade = $this->existing->get($ssy->id);

            return [
                $ssy->student->matricule,
                $ssy->student->fullName(),
                $grade->score ?? '',
            ];
        });
    }
}
