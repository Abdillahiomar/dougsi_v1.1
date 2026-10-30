<?php

namespace App\Services;

use App\Models\AccountingAccount;
use App\Models\AccountingJournal;

class AccountingSetupService
{
    /**
     * Pré-remplit le plan comptable SYSCOHADA (référentiel OHADA, utilisé à
     * Djibouti) et les journaux standards pour une école. Idempotent : les
     * comptes/journaux déjà créés (même code) ne sont pas dupliqués.
     */
    public static function seedForSchool(int $schoolId): void
    {
        foreach (self::defaultAccounts() as $account) {
            AccountingAccount::firstOrCreate(
                ['school_id' => $schoolId, 'code' => $account['code']],
                ['label' => $account['label'], 'class' => $account['class']]
            );
        }

        foreach (self::defaultJournals() as $journal) {
            AccountingJournal::firstOrCreate(
                ['school_id' => $schoolId, 'code' => $journal['code']],
                ['label' => $journal['label']]
            );
        }
    }

    public static function defaultJournals(): array
    {
        return [
            ['code' => 'CA', 'label' => 'Journal de caisse'],
            ['code' => 'BQ', 'label' => 'Journal de banque'],
            ['code' => 'AC', 'label' => 'Journal des achats'],
            ['code' => 'VE', 'label' => 'Journal des ventes'],
            ['code' => 'OD', 'label' => 'Journal des opérations diverses'],
        ];
    }

    /**
     * Sélection de comptes SYSCOHADA révisé, adaptée à une école privée.
     * Le directeur/comptable peut en ajouter d'autres ensuite.
     */
    public static function defaultAccounts(): array
    {
        return [
            // Classe 1 — Comptes de capitaux
            ['code' => '101000', 'label' => 'Capital',                                   'class' => 1],
            ['code' => '106000', 'label' => 'Réserves',                                  'class' => 1],
            ['code' => '110000', 'label' => 'Report à nouveau',                          'class' => 1],
            ['code' => '120000', 'label' => "Résultat de l'exercice",                    'class' => 1],
            ['code' => '164000', 'label' => 'Emprunts auprès des établissements de crédit', 'class' => 1],

            // Classe 2 — Immobilisations
            ['code' => '211000', 'label' => 'Terrains',                                  'class' => 2],
            ['code' => '213000', 'label' => 'Bâtiments',                                 'class' => 2],
            ['code' => '218100', 'label' => 'Matériel informatique',                     'class' => 2],
            ['code' => '218200', 'label' => 'Matériel et mobilier de bureau',            'class' => 2],
            ['code' => '218300', 'label' => 'Matériel de transport',                     'class' => 2],
            ['code' => '281800', 'label' => "Amortissements des immobilisations corporelles", 'class' => 2],

            // Classe 3 — Stocks
            ['code' => '322000', 'label' => 'Fournitures scolaires en stock',            'class' => 3],

            // Classe 4 — Comptes de tiers
            ['code' => '401000', 'label' => 'Fournisseurs',                              'class' => 4],
            ['code' => '411000', 'label' => 'Clients — élèves (scolarité)',              'class' => 4],
            ['code' => '421000', 'label' => 'Personnel — rémunérations dues',            'class' => 4],
            ['code' => '431000', 'label' => 'Caisse de sécurité sociale',                'class' => 4],
            ['code' => '441000', 'label' => 'État — impôts et taxes',                    'class' => 4],
            ['code' => '455000', 'label' => 'Direction — comptes courants',              'class' => 4],
            ['code' => '470000', 'label' => 'Débiteurs et créditeurs divers',            'class' => 4],

            // Classe 5 — Comptes financiers
            ['code' => '521000', 'label' => 'Banque',                                    'class' => 5],
            ['code' => '571000', 'label' => 'Caisse',                                    'class' => 5],
            ['code' => '580000', 'label' => 'Virements internes',                        'class' => 5],

            // Classe 6 — Charges
            ['code' => '601000', 'label' => 'Achats de fournitures',                     'class' => 6],
            ['code' => '604000', 'label' => "Achats d'études et prestations de services",'class' => 6],
            ['code' => '605000', 'label' => 'Autres achats',                             'class' => 6],
            ['code' => '611000', 'label' => 'Transports',                                'class' => 6],
            ['code' => '613000', 'label' => 'Locations (loyer)',                         'class' => 6],
            ['code' => '616000', 'label' => 'Entretien, réparations et maintenance',     'class' => 6],
            ['code' => '618000', 'label' => 'Documentation, charges diverses',           'class' => 6],
            ['code' => '622000', 'label' => 'Honoraires, commissions',                   'class' => 6],
            ['code' => '624000', 'label' => 'Télécommunications',                        'class' => 6],
            ['code' => '628000', 'label' => 'Autres charges externes',                   'class' => 6],
            ['code' => '631000', 'label' => 'Impôts et taxes',                           'class' => 6],
            ['code' => '641000', 'label' => 'Rémunérations du personnel enseignant',     'class' => 6],
            ['code' => '642000', 'label' => 'Rémunérations du personnel administratif',  'class' => 6],
            ['code' => '645000', 'label' => 'Charges sociales',                          'class' => 6],
            ['code' => '658000', 'label' => 'Charges diverses de gestion courante',      'class' => 6],
            ['code' => '661000', 'label' => "Charges d'intérêts",                        'class' => 6],
            ['code' => '681000', 'label' => 'Dotations aux amortissements',              'class' => 6],

            // Classe 7 — Produits
            ['code' => '706000', 'label' => 'Produits de scolarité',                     'class' => 7],
            ['code' => '707000', 'label' => 'Produits accessoires (cantine, transport...)', 'class' => 7],
            ['code' => '758000', 'label' => 'Produits divers de gestion courante',       'class' => 7],
            ['code' => '771000', 'label' => 'Produits financiers',                       'class' => 7],
            ['code' => '781000', 'label' => 'Subventions reçues',                        'class' => 7],
        ];
    }
}
