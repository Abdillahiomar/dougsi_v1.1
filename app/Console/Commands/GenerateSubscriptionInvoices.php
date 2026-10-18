<?php

namespace App\Console\Commands;

use App\Services\InvoiceGenerator;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:generate-subscription-invoices')]
#[Description("Génère les factures dues pour tous les abonnements actifs (facturation Dugsi → écoles)")]
class GenerateSubscriptionInvoices extends Command
{
    public function handle(InvoiceGenerator $generator): void
    {
        $count = $generator->generateDueInvoices();

        $this->info("{$count} facture(s) générée(s).");
    }
}
