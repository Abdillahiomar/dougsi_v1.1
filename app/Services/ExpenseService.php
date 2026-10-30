<?php

namespace App\Services;

use App\Models\AccountingAccount;
use App\Models\AccountingJournal;
use App\Models\Expense;
use App\Models\FiscalYear;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpenseService
{
    /**
     * Enregistre une dépense et génère automatiquement son écriture comptable
     * (Débit compte de charge / Crédit Caisse ou Banque selon le mode de paiement).
     */
    public static function create(
        FiscalYear $fiscalYear,
        AccountingAccount $chargeAccount,
        string $label,
        int $amount,
        string $date,
        string $paymentMethod, // 'cash' | 'bank'
        ?string $receiptPath,
        ?int $createdBy,
    ): Expense {
        $creditCode    = $paymentMethod === 'bank' ? '521000' : '571000';
        $creditAccount = AccountingAccount::where('code', $creditCode)->first();
        abort_unless($creditAccount, 500, "Compte {$creditCode} introuvable dans le plan comptable.");

        $journal = AccountingJournal::where('code', $paymentMethod === 'bank' ? 'BQ' : 'CA')->first();
        abort_unless($journal, 500, 'Journal de caisse/banque introuvable.');

        return DB::transaction(function () use ($fiscalYear, $chargeAccount, $creditAccount, $journal, $label, $amount, $date, $paymentMethod, $receiptPath, $createdBy) {
            $expense = Expense::create([
                'school_id'             => $fiscalYear->school_id,
                'fiscal_year_id'        => $fiscalYear->id,
                'accounting_account_id' => $chargeAccount->id,
                'label'                 => $label,
                'amount'                => $amount,
                'date'                  => $date,
                'payment_method'        => $paymentMethod,
                'receipt_path'          => $receiptPath,
                'created_by'            => $createdBy,
            ]);

            $entry = JournalEntryService::createEntry(
                $fiscalYear,
                $journal,
                $date,
                $label,
                [
                    ['accounting_account_id' => $chargeAccount->id, 'label' => $label, 'debit' => $amount, 'credit' => 0],
                    ['accounting_account_id' => $creditAccount->id, 'label' => $label, 'debit' => 0, 'credit' => $amount],
                ],
                sourceType: 'expense',
                sourceId: $expense->id,
                createdBy: $createdBy,
            );

            $expense->update(['journal_entry_id' => $entry->id]);

            return $expense;
        });
    }

    public static function delete(Expense $expense): void
    {
        abort_unless($expense->fiscalYear->isOpen(), 403, "Cet exercice comptable est clôturé.");

        DB::transaction(function () use ($expense) {
            if ($expense->journal_entry_id) {
                JournalEntry::where('id', $expense->journal_entry_id)->delete();
            }
            if ($expense->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }
            $expense->delete();
        });
    }
}
