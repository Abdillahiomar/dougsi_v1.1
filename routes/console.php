<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Purge quotidienne du journal d'audit (connexions/déconnexions) de plus d'un mois.
Schedule::command('audit-logs:prune')->daily();

// Facturation SaaS (Dugsi → écoles) : renouvelle les abonnements arrivant à
// échéance (génère l'année suivante), génère les factures dues (filet de
// sécurité pour les abonnements sans échéancier complet), puis bascule en
// retard celles dont l'échéance est dépassée.
Schedule::command('app:renew-subscriptions')->dailyAt('01:00');
Schedule::command('app:expire-subscriptions')->dailyAt('01:30');
Schedule::command('app:generate-subscription-invoices')->dailyAt('02:00');
Schedule::command('app:mark-subscription-invoices-overdue')->dailyAt('03:00');
