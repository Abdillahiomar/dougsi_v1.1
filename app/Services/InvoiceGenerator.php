<?php

namespace App\Services;

use App\Models\{Subscription, Invoice};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceGenerator
{
    /**
     * Génère les factures dues pour tous les abonnements actifs.
     * Retourne le nombre de factures créées.
     */
    public function generateDueInvoices(?Carbon $onDate = null): int
    {
        $onDate = $onDate ?? now();
        $created = 0;

        // withoutGlobalScopes pour être sûr de parcourir toutes les écoles,
        // même hors contexte superadmin (ex: cron)
        $subscriptions = Subscription::withoutGlobalScopes()
            ->where('status', 'active')
            ->get();

        foreach ($subscriptions as $sub) {
            if ($this->shouldInvoice($sub, $onDate)) {
                $this->createInvoice($sub, $onDate);
                $created++;
            }
        }

        return $created;
    }

    /**
     * Génère la facture d'un abonnement précis (utilisé par le bouton manuel).
     * Retourne l'Invoice créée, ou null si une facture couvre déjà la période.
     */
    public function generateForSubscription(Subscription $sub, ?Carbon $onDate = null): ?Invoice
    {
        $onDate = $onDate ?? now();

        if (! $this->shouldInvoice($sub, $onDate)) {
            return null;
        }

        return $this->createInvoice($sub, $onDate);
    }

    /**
     * Détermine si une facture doit être émise pour cet abonnement à cette date.
     * Règle : on facture si aucune facture n'existe encore pour la période courante.
     */
    protected function shouldInvoice(Subscription $sub, Carbon $onDate): bool
    {
        $periodStart = $this->currentPeriodStart($sub, $onDate);

        // Existe-t-il déjà une facture émise à partir de ce début de période ?
        $exists = Invoice::withoutGlobalScopes()
            ->where('subscription_id', $sub->id)
            ->whereDate('issued_at', '>=', $periodStart)
            ->exists();

        return ! $exists;
    }

    /**
     * Début de la période de facturation courante selon la périodicité.
     * On ancre sur starts_at pour respecter le rythme du contrat.
     */
    protected function currentPeriodStart(Subscription $sub, Carbon $onDate): Carbon
    {
        $months = $sub->monthsPerCycle();

        // Point d'ancrage : la date de début du contrat, sinon le début du mois courant
        $anchor = $sub->starts_at ? $sub->starts_at->copy() : $onDate->copy()->startOfMonth();

        // On avance de $months en $months jusqu'à englober la date courante
        $periodStart = $anchor->copy();
        while ($periodStart->copy()->addMonths($months)->lte($onDate)) {
            $periodStart->addMonths($months);
        }

        return $periodStart->startOfDay();
    }

    /**
     * Génère en une seule fois toutes les factures de la période contractuelle
     * (ex : 12 factures mensuelles pour un abonnement d'un an), à partir de la
     * date donnée (par défaut : la date de début de l'abonnement).
     * Idempotent : ne recrée jamais une facture déjà émise pour une période donnée.
     * Retourne le nombre de factures effectivement créées.
     */
    public function generateYearSchedule(Subscription $sub, ?Carbon $from = null): int
    {
        $from = ($from ?? $sub->starts_at)->copy()->startOfDay();
        $months = $sub->monthsPerCycle();
        $periodsCount = intdiv(12, $months);
        $created = 0;

        for ($i = 0; $i < $periodsCount; $i++) {
            $periodStart = $from->copy()->addMonths($i * $months);

            $exists = Invoice::withoutGlobalScopes()
                ->where('subscription_id', $sub->id)
                ->whereDate('issued_at', $periodStart)
                ->exists();

            if (! $exists) {
                $this->createInvoice($sub, $periodStart);
                $created++;
            }
        }

        return $created;
    }

    /**
     * Met à jour le montant des factures pas encore émises/en retard/payées
     * (statut "unpaid") quand l'abonnement est modifié (tarif, remise...).
     * Les factures déjà en retard, payées ou annulées ne sont jamais touchées.
     */
    public function syncUnpaidInvoiceAmounts(Subscription $sub): int
    {
        return Invoice::withoutGlobalScopes()
            ->where('subscription_id', $sub->id)
            ->where('status', 'unpaid')
            ->update(['amount' => $sub->cycleAmount()]);
    }

    /**
     * Renouvelle les abonnements actifs en auto-renouvellement dont la fin de
     * contrat approche (30 jours) ou est dépassée : génère les factures de la
     * période suivante et repousse la date de fin d'un an. Retourne le nombre
     * d'abonnements renouvelés.
     */
    public function renewDueSubscriptions(?Carbon $onDate = null): int
    {
        $onDate = $onDate ?? now();
        $renewed = 0;

        $subscriptions = Subscription::withoutGlobalScopes()
            ->where('status', 'active')
            ->where('auto_renew', true)
            ->whereNotNull('ends_at')
            ->whereDate('ends_at', '<=', $onDate->copy()->addDays(30))
            ->get();

        foreach ($subscriptions as $sub) {
            $nextStart = $sub->ends_at->copy()->addDay();

            if ($this->generateYearSchedule($sub, $nextStart) > 0) {
                $sub->update(['ends_at' => $nextStart->copy()->addYear()]);
                $renewed++;
            }
        }

        return $renewed;
    }

    protected function createInvoice(Subscription $sub, Carbon $onDate): Invoice
    {
        return DB::transaction(function () use ($sub, $onDate) {
            $number = $this->nextInvoiceNumber($onDate);

            return Invoice::create([
                'school_id'       => $sub->school_id,
                'subscription_id' => $sub->id,
                'invoice_number'  => $number,
                'amount'          => $sub->cycleAmount(),
                'issued_at'       => $onDate->toDateString(),
                'due_at'          => $onDate->copy()->addDays(15)->toDateString(), // échéance +15j
                'status'          => 'unpaid',
            ]);
        });
    }

    /**
     * Numéro de facture unique : INV-AAAA-MM-XXXX
     */
    protected function nextInvoiceNumber(Carbon $onDate): string
    {
        $prefix = 'INV-' . $onDate->format('Y-m') . '-';

        $last = Invoice::withoutGlobalScopes()
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}