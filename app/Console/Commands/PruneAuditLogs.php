<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('audit-logs:prune')]
#[Description("Supprime les entrées du journal d'audit (connexions/déconnexions) de plus d'un mois")]
class PruneAuditLogs extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deleted = AuditLog::where('created_at', '<', now()->subMonth())->delete();

        $this->info("{$deleted} entrée(s) du journal d'audit supprimée(s) (rétention : 1 mois).");
    }
}
