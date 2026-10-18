<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:mark-subscription-invoices-overdue')]
#[Description("Bascule en retard les factures d'abonnement (Dugsi → écoles) dont l'échéance est dépassée")]
class MarkSubscriptionInvoicesOverdue extends Command
{
    public function handle(): void
    {
        $count = Invoice::withoutGlobalScopes()
            ->where('status', 'unpaid')
            ->whereNotNull('due_at')
            ->whereDate('due_at', '<', today())
            ->update(['status' => 'overdue']);

        $this->info("{$count} facture(s) basculée(s) en retard.");
    }
}
