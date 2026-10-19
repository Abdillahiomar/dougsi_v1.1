<?php

namespace App\Console\Commands;

use App\Services\InvoiceGenerator;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:renew-subscriptions')]
#[Description("Renouvelle les abonnements à renouvellement automatique dont la fin de contrat approche, en générant les factures de l'année suivante")]
class RenewSubscriptions extends Command
{
    public function handle(InvoiceGenerator $generator): void
    {
        $count = $generator->renewDueSubscriptions();

        $this->info("{$count} abonnement(s) renouvelé(s).");
    }
}
