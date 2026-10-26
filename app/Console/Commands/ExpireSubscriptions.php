<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:expire-subscriptions')]
#[Description("Bascule en « expired » les abonnements (sans renouvellement automatique) dont la date de fin est dépassée")]
class ExpireSubscriptions extends Command
{
    public function handle(): void
    {
        // Les abonnements en auto-renouvellement sont gérés par
        // app:renew-subscriptions — on ne touche qu'aux autres ici.
        $count = Subscription::withoutGlobalScopes()
            ->where('auto_renew', false)
            ->whereNotNull('ends_at')
            ->whereDate('ends_at', '<', today())
            ->whereNotIn('status', ['expired', 'cancelled'])
            ->update(['status' => 'expired']);

        $this->info("{$count} abonnement(s) basculé(s) en expiré.");
    }
}
