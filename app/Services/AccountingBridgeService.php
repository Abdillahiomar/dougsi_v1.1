<?php

namespace App\Services;

use App\Models\AccountingAccount;
use App\Models\AccountingJournal;
use App\Models\FiscalYear;
use App\Models\JournalEntry;
use App\Models\PaymentReceipt;
use App\Models\ReceiptMethod;
use Illuminate\Support\Facades\Log;

/**
 * Fait le pont entre les flux financiers existants (encaissements élèves) et
 * le module de comptabilité en partie double. Toute erreur ici est absorbée :
 * l'encaissement réel ne doit jamais être bloqué par un souci côté
 * comptabilité, qui reste une fonctionnalité annexe/optionnelle.
 */
class AccountingBridgeService
{
    public static function recordStudentPayment(PaymentReceipt $receipt): void
    {
        try {
            self::doRecordStudentPayment($receipt);
        } catch (\Throwable $e) {
            Log::error('AccountingBridgeService::recordStudentPayment a échoué', [
                'receipt_id' => $receipt->id,
                'school_id'  => $receipt->school_id,
                'message'    => $e->getMessage(),
            ]);
        }
    }

    public static function reverseStudentPayment(PaymentReceipt $receipt): void
    {
        try {
            if ($receipt->journal_entry_id) {
                JournalEntry::where('id', $receipt->journal_entry_id)->delete();
                $receipt->update(['journal_entry_id' => null]);
            }
        } catch (\Throwable $e) {
            Log::error('AccountingBridgeService::reverseStudentPayment a échoué', [
                'receipt_id' => $receipt->id,
                'message'    => $e->getMessage(),
            ]);
        }
    }

    private static function doRecordStudentPayment(PaymentReceipt $receipt): void
    {
        $date = $receipt->paid_at?->format('Y-m-d') ?? now()->format('Y-m-d');

        $fiscalYear = FiscalYear::where('school_id', $receipt->school_id)
            ->where('status', 'open')
            ->whereDate('starts_at', '<=', $date)
            ->whereDate('ends_at', '>=', $date)
            ->first();

        // Pas de comptabilité configurée (ou pas d'exercice couvrant cette date)
        // pour cette école : on n'écrit rien, silencieusement.
        if (! $fiscalYear) {
            return;
        }

        $methods = ReceiptMethod::where('payment_receipt_id', $receipt->id)->get();
        if ($methods->isEmpty()) {
            return;
        }

        $cashAmount    = (int) $methods->where('method', 'cash')->sum('amount');
        $nonCashAmount = (int) $methods->where('method', '!=', 'cash')->sum('amount');
        $total         = $cashAmount + $nonCashAmount;

        if ($total <= 0) {
            return;
        }

        $produitsAccount = AccountingAccount::where('code', '706000')->first();
        abort_unless($produitsAccount, 500, 'Compte 706000 (Produits de scolarité) introuvable.');

        $lines = [
            ['accounting_account_id' => $produitsAccount->id, 'label' => 'Encaissement scolarité', 'debit' => 0, 'credit' => $total],
        ];

        if ($cashAmount > 0) {
            $caisse = AccountingAccount::where('code', '571000')->first();
            abort_unless($caisse, 500, 'Compte 571000 (Caisse) introuvable.');
            $lines[] = ['accounting_account_id' => $caisse->id, 'label' => 'Encaissement espèces', 'debit' => $cashAmount, 'credit' => 0];
        }

        if ($nonCashAmount > 0) {
            $banque = AccountingAccount::where('code', '521000')->first();
            abort_unless($banque, 500, 'Compte 521000 (Banque) introuvable.');
            $lines[] = ['accounting_account_id' => $banque->id, 'label' => 'Encaissement non-espèces', 'debit' => $nonCashAmount, 'credit' => 0];
        }

        $journalCode = $cashAmount >= $nonCashAmount ? 'CA' : 'BQ';
        $journal     = AccountingJournal::where('code', $journalCode)->first();
        abort_unless($journal, 500, "Journal {$journalCode} introuvable.");

        $entry = JournalEntryService::createEntry(
            $fiscalYear,
            $journal,
            $date,
            "Encaissement scolarité — reçu {$receipt->receipt_number}",
            $lines,
            sourceType: 'payment_receipt',
            sourceId: $receipt->id,
            createdBy: $receipt->received_by,
        );

        $receipt->update(['journal_entry_id' => $entry->id]);
    }
}
