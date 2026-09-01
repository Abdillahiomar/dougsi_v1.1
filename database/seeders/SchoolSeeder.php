<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\School;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        // Créer d'abord les plans s'ils n'existent pas
        $this->ensurePlansExist();

        $schools = [
            ['name' => 'Votre Ecole 1', 'slug' => 'votre-ecole1', 'plan' => 'pro'],
            ['name' => 'Votre Ecole 2', 'slug' => 'votre-ecole2', 'plan' => 'basique'],
        ];

        foreach ($schools as $data) {
            $school = School::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'email' => $data['slug'] . '@example.dj',
                    'phone' => '77' . random_int(100000, 999999),
                    'address' => 'Djibouti Ville, Djibouti',
                    'status' => 'active',
                ]
            );

            $plan = Plan::where('slug', $data['plan'])->first();

            if (!$plan) {
                throw new \Exception("Plan '{$data['plan']}' n'existe pas. Veuillez vérifier les slugs des plans.");
            }

            Subscription::updateOrCreate(
                ['school_id' => $school->id, 'plan_id' => $plan->id],
                [
                    'status' => 'active',
                    'starts_at' => now()->subMonths(2),
                    'ends_at' => now()->addMonths(10),
                    'auto_renew' => true,
                ]
            );
        }
    }

    private function ensurePlansExist(): void
    {
        $planCount = Plan::count();
        
        if ($planCount === 0) {
            $this->command->info('Aucun plan trouvé. Création des plans par défaut...');
            
            $defaultPlans = [
                [
                    'name' => 'Basique',
                    'slug' => 'basique',
                    'description' => 'Plan basique pour les petites écoles',
                    'price' => 0,
                    'billing_cycle' => 'monthly',
                    'max_students' => 50,
                    'max_staff' => 5,
                    'features' => ['Gestion des étudiants', 'Gestion des classes', 'Présences'],
                    'is_active' => true,
                ],
                [
                    'name' => 'Pro',
                    'slug' => 'pro',
                    'description' => 'Plan professionnel pour les écoles',
                    'price' => 29.99,
                    'billing_cycle' => 'monthly',
                    'max_students' => 200,
                    'max_staff' => 20,
                    'features' => ['Gestion des étudiants', 'Gestion des classes', 'Présences', 'Notes', 'Paiements'],
                    'is_active' => true,
                ],
                [
                    'name' => 'Premium',
                    'slug' => 'premium',
                    'description' => 'Plan premium pour les grandes écoles',
                    'price' => 49.99,
                    'billing_cycle' => 'monthly',
                    'max_students' => 500,
                    'max_staff' => 50,
                    'features' => ['Gestion des étudiants', 'Gestion des classes', 'Présences', 'Notes', 'Paiements', 'Import/Export', 'API'],
                    'is_active' => true,
                ],
            ];
            
            foreach ($defaultPlans as $planData) {
                Plan::create($planData);
            }
            
            $this->command->info('Plans créés avec succès.');
        }
    }
}