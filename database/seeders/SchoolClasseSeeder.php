<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class SchoolClasseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Structure :
        // École 1 (Ecole Les Pyramides) → année 2025-2026 (id=2) + 2026-2027 (id=3)
        // École 2 (Institut El Amal)    → année 2025-2026 (id=1) + 2026-2027 (id=3)
        //
        // Niveaux : CP=1, CE1=2, CE2=3, CM1=4, CM2=5 (primaire)
        // Staff ids : 1 à 5

        $classes = [

            // ════════════════════════════════════════════════
            // ECOLE LES PYRAMIDES (school_id=1)
            // ════════════════════════════════════════════════

            // Année 2025-2026 (academic_year_id=2)
            ['school_id'=>1,'academic_year_id'=>2,'level_id'=>1,'name'=>'CP A',  'main_teacher_id'=>1,'capacity'=>30],
            ['school_id'=>1,'academic_year_id'=>2,'level_id'=>1,'name'=>'CP B',  'main_teacher_id'=>2,'capacity'=>30],
            ['school_id'=>1,'academic_year_id'=>2,'level_id'=>2,'name'=>'CE1 A', 'main_teacher_id'=>3,'capacity'=>30],
            ['school_id'=>1,'academic_year_id'=>2,'level_id'=>2,'name'=>'CE1 B', 'main_teacher_id'=>4,'capacity'=>30],
            ['school_id'=>1,'academic_year_id'=>2,'level_id'=>3,'name'=>'CE2 A', 'main_teacher_id'=>5,'capacity'=>30],
            ['school_id'=>1,'academic_year_id'=>2,'level_id'=>4,'name'=>'CM1 A', 'main_teacher_id'=>1,'capacity'=>28],
            ['school_id'=>1,'academic_year_id'=>2,'level_id'=>5,'name'=>'CM2 A', 'main_teacher_id'=>2,'capacity'=>28],

            // Année 2026-2027 (academic_year_id=3)
            ['school_id'=>1,'academic_year_id'=>3,'level_id'=>1,'name'=>'CP A',  'main_teacher_id'=>1,'capacity'=>30],
            ['school_id'=>1,'academic_year_id'=>3,'level_id'=>1,'name'=>'CP B',  'main_teacher_id'=>2,'capacity'=>30],
            ['school_id'=>1,'academic_year_id'=>3,'level_id'=>2,'name'=>'CE1 A', 'main_teacher_id'=>3,'capacity'=>30],
            ['school_id'=>1,'academic_year_id'=>3,'level_id'=>2,'name'=>'CE1 B', 'main_teacher_id'=>4,'capacity'=>30],
            ['school_id'=>1,'academic_year_id'=>3,'level_id'=>3,'name'=>'CE2 A', 'main_teacher_id'=>5,'capacity'=>30],
            ['school_id'=>1,'academic_year_id'=>3,'level_id'=>4,'name'=>'CM1 A', 'main_teacher_id'=>1,'capacity'=>28],
            ['school_id'=>1,'academic_year_id'=>3,'level_id'=>5,'name'=>'CM2 A', 'main_teacher_id'=>2,'capacity'=>28],

            // ════════════════════════════════════════════════
            // INSTITUT EL AMAL (school_id=2)
            // ════════════════════════════════════════════════

            // Année 2025-2026 (academic_year_id=1)
            ['school_id'=>2,'academic_year_id'=>1,'level_id'=>1,'name'=>'CP A',  'main_teacher_id'=>3,'capacity'=>35],
            ['school_id'=>2,'academic_year_id'=>1,'level_id'=>2,'name'=>'CE1 A', 'main_teacher_id'=>4,'capacity'=>35],
            ['school_id'=>2,'academic_year_id'=>1,'level_id'=>3,'name'=>'CE2 A', 'main_teacher_id'=>5,'capacity'=>35],
            ['school_id'=>2,'academic_year_id'=>1,'level_id'=>4,'name'=>'CM1 A', 'main_teacher_id'=>3,'capacity'=>32],
            ['school_id'=>2,'academic_year_id'=>1,'level_id'=>5,'name'=>'CM2 A', 'main_teacher_id'=>4,'capacity'=>32],

            // Année 2026-2027 (academic_year_id=3)
            ['school_id'=>2,'academic_year_id'=>3,'level_id'=>1,'name'=>'CP A',  'main_teacher_id'=>3,'capacity'=>35],
            ['school_id'=>2,'academic_year_id'=>3,'level_id'=>2,'name'=>'CE1 A', 'main_teacher_id'=>4,'capacity'=>35],
            ['school_id'=>2,'academic_year_id'=>3,'level_id'=>3,'name'=>'CE2 A', 'main_teacher_id'=>5,'capacity'=>35],
            ['school_id'=>2,'academic_year_id'=>3,'level_id'=>4,'name'=>'CM1 A', 'main_teacher_id'=>3,'capacity'=>32],
            ['school_id'=>2,'academic_year_id'=>3,'level_id'=>5,'name'=>'CM2 A', 'main_teacher_id'=>4,'capacity'=>32],
        ];

        foreach ($classes as $class) {
            SchoolClass::firstOrCreate(
                [
                    'academic_year_id' => $class['academic_year_id'],
                    'level_id'         => $class['level_id'],
                    'name'             => $class['name'],
                ],
                [
                    'school_id'       => $class['school_id'],
                    'main_teacher_id' => $class['main_teacher_id'],
                    'capacity'        => $class['capacity'],
                ]
            );
        }

        $this->command->info('✓ ' . count($classes) . ' classes créées.');
    }
}