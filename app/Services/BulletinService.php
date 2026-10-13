<?php

namespace App\Services;

use App\Models\Bulletin;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\SchoolGradingConfig;
use App\Models\StudentSchoolYear;
use Illuminate\Support\Collection;

class BulletinService
{
    /**
     * Calcule les données d'un bulletin pour un élève et une période.
     * Retourne un tableau structuré sans écrire en base.
     */
    public function calculate(StudentSchoolYear $ssy, string $period): array
    {
        $config = GradingConfigService::get($ssy->student->school_id);
        $class  = $ssy->schoolClass()->with('classSubjects.subject', 'classSubjects.teacher.user')->first();

        $grades = $this->gradesFor($ssy->id, $period);

        [$subjectLines, $generalAverage] = $this->buildSubjectLines($class, $grades, $config);

        // Camarades de classe — chargés une seule fois, notes récupérées en une
        // seule requête groupée (évite de refaire une requête par camarade).
        $classmates = StudentSchoolYear::where('school_class_id', $ssy->school_class_id)
            ->where('academic_year_id', $ssy->academic_year_id)
            ->get();

        $rank = $generalAverage !== null
            ? $this->calculateRank($class, $classmates, $ssy->id, $period, $generalAverage, $config)
            : null;

        return [
            'ssy'             => $ssy,
            'period'          => $period,
            'subject_lines'   => $subjectLines,
            'general_average' => $generalAverage,
            'mention'         => $generalAverage !== null ? GradingConfigService::mention($generalAverage, $config) : '—',
            'rank'            => $rank,
            'class_count'     => $classmates->count(),
            'total_coeffs'    => $subjectLines->sum('coefficient'),
        ];
    }

    /**
     * Génère ou met à jour un bulletin en base.
     */
    public function generateBulletin(StudentSchoolYear $ssy, string $period, string $comment = ''): Bulletin
    {
        $data = $this->calculate($ssy, $period);

        return Bulletin::updateOrCreate(
            ['student_school_year_id' => $ssy->id, 'period' => $period],
            [
                'average'         => $data['general_average'],
                'rank'            => $data['rank'],
                'general_comment' => $comment ?: null,
                'generated_at'    => now(),
            ]
        );
    }

    /**
     * Notes d'un élève pour une période, avec l'évaluation et sa matière chargées.
     */
    private function gradesFor(int $ssyId, string $period): Collection
    {
        return Grade::where('student_school_year_id', $ssyId)
            ->whereHas('evaluation', fn ($q) => $q->where('period', $period))
            ->with(['evaluation.subject'])
            ->get();
    }

    /**
     * Construit les lignes par matière (moyenne pondérée selon la config de
     * l'école — barème normalisé, poids par type d'évaluation, note la plus
     * basse ignorée si configuré) ainsi que la moyenne générale pondérée par
     * coefficient (ou moyenne simple selon `average_method`).
     *
     * @return array{0: Collection, 1: ?float} [lignes par matière, moyenne générale]
     */
    private function buildSubjectLines(SchoolClass $class, Collection $grades, SchoolGradingConfig $config): array
    {
        $subjectLines = collect();
        $totalPoints  = 0;
        $totalWeight  = 0;

        foreach ($class->classSubjects as $cs) {
            $subject       = $cs->subject;
            $subjectGrades = $grades->filter(
                fn ($g) => $g->evaluation->subject_id === $subject->id
            );

            if ($subjectGrades->isEmpty()) {
                $subjectLines->push([
                    'subject_id'          => $subject->id,
                    'subject_name'        => $subject->name,
                    'subject_code'        => $subject->code,
                    'subject_color'       => $subject->color,
                    'coefficient'         => $subject->coefficient,
                    'teacher'             => $cs->teacher?->user?->name ?? '—',
                    'grades'              => [],
                    'average'             => null,
                    'mention'             => '—',
                    'appreciation'        => '',
                    'insufficient_grades' => false,
                ]);
                continue;
            }

            $avg = GradingConfigService::computeAverage(
                $subjectGrades->map(fn ($g) => [
                    'score' => $g->score,
                    'max'   => $g->evaluation->max_score,
                    'type'  => $g->evaluation->type,
                ])->all(),
                $config
            );

            $coeff  = $subject->coefficient;
            // Moyenne simple : chaque matière pèse 1, indépendamment de son coefficient.
            $weight = $config->average_method === 'simple_average' ? 1 : $coeff;

            if ($avg !== null) {
                $totalPoints += $avg * $weight;
                $totalWeight += $weight;
            }

            $subjectLines->push([
                'subject_id'          => $subject->id,
                'subject_name'        => $subject->name,
                'subject_code'        => $subject->code,
                'subject_color'       => $subject->color,
                'coefficient'         => $coeff,
                'teacher'             => $cs->teacher?->user?->name ?? '—',
                'grades'              => $subjectGrades->map(fn ($g) => [
                    'score' => $g->score,
                    'max'   => $g->evaluation->max_score,
                    'type'  => $g->evaluation->type,
                    'date'  => $g->evaluation->date?->format('d/m/Y'),
                ])->values()->toArray(),
                'average'             => $avg,
                'mention'             => $avg !== null ? GradingConfigService::mention($avg, $config) : '—',
                'appreciation'        => $avg !== null ? GradingConfigService::appreciation($avg, $config) : '',
                'insufficient_grades' => $subjectGrades->count() < $config->min_grades_per_period,
            ]);
        }

        $generalAverage = $totalWeight > 0
            ? round($totalPoints / $totalWeight, $config->decimal_places)
            : null;

        return [$subjectLines, $generalAverage];
    }

    /**
     * Calcule le rang de l'élève dans la classe pour une période, avec la
     * même méthode de calcul que sa propre moyenne (barème, poids, coefficients).
     * Une seule requête groupée pour les notes de tous les camarades.
     */
    private function calculateRank(
        SchoolClass $class,
        Collection $classmates,
        int $excludeSsyId,
        string $period,
        float $myAverage,
        SchoolGradingConfig $config
    ): int {
        $classmateIds = $classmates->where('id', '!=', $excludeSsyId)->pluck('id');

        if ($classmateIds->isEmpty()) {
            return 1;
        }

        $allGrades = Grade::whereIn('student_school_year_id', $classmateIds)
            ->whereHas('evaluation', fn ($q) => $q->where('period', $period))
            ->with(['evaluation.subject'])
            ->get()
            ->groupBy('student_school_year_id');

        $rank = 1;
        foreach ($classmateIds as $mateId) {
            $mateGrades = $allGrades->get($mateId, collect());
            if ($mateGrades->isEmpty()) continue;

            [, $mateAverage] = $this->buildSubjectLines($class, $mateGrades, $config);

            if ($mateAverage !== null && $mateAverage > $myAverage) {
                $rank++;
            }
        }

        return $rank;
    }
}
