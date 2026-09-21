<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Purge quotidienne du journal d'audit (connexions/déconnexions) de plus d'un mois.
Schedule::command('audit-logs:prune')->daily();
