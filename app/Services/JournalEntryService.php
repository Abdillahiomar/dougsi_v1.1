<?php

namespace App\Services;

use App\Models\AccountingJournal;
use App\Models\FiscalYear;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class JournalEntryService
{
    /**
     * Crée une écriture comptable équilibrée (total débit = total crédit).
     *
     * @param  array<int, array{accounting_account_id:int, label?:string, debit?:int, credit?:int}>  $lines
     */
    public static function createEntry(
        FiscalYear $fiscalYear,
        AccountingJournal $journal,
        string $date,
        string $description,
        array $lines,
        ?string $sourceType = null,
        ?int $sourceId = null,
        ?int $createdBy = null,
    ): JournalEntry {
        abort_unless($fiscalYear->isOpen(), 403, "Cet exercice comptable est clôturé.");

        $lines = array_values(array_filter($lines, fn ($l) =>
            ((int) ($l['debit'] ?? 0)) > 0 || ((int) ($l['credit'] ?? 0)) > 0
        ));

        if (count($lines) < 2) {
            throw new InvalidArgumentException("Une écriture doit comporter au moins deux lignes.");
        }

        $totalDebit  = array_sum(array_map(fn ($l) => (int) ($l['debit'] ?? 0), $lines));
        $totalCredit = array_sum(array_map(fn ($l) => (int) ($l['credit'] ?? 0), $lines));

        if ($totalDebit !== $totalCredit || $totalDebit === 0) {
            throw new InvalidArgumentException("L'écriture n'est pas équilibrée (débit {$totalDebit} ≠ crédit {$totalCredit}).");
        }

        return DB::transaction(function () use ($fiscalYear, $journal, $date, $description, $lines, $sourceType, $sourceId, $createdBy) {
            $entry = JournalEntry::create([
                'school_id'             => $fiscalYear->school_id,
                'fiscal_year_id'        => $fiscalYear->id,
                'accounting_journal_id' => $journal->id,
                'entry_number'          => self::nextEntryNumber($fiscalYear, $journal),
                'date'                  => $date,
                'description'           => $description,
                'source_type'           => $sourceType,
                'source_id'             => $sourceId,
                'created_by'            => $createdBy,
            ]);

            foreach ($lines as $line) {
                $entry->lines()->create([
                    'accounting_account_id' => $line['accounting_account_id'],
                    'label'                 => $line['label'] ?? null,
                    'debit'                 => (int) ($line['debit'] ?? 0),
                    'credit'                => (int) ($line['credit'] ?? 0),
                ]);
            }

            return $entry;
        });
    }

    private static function nextEntryNumber(FiscalYear $fiscalYear, AccountingJournal $journal): string
    {
        $count = JournalEntry::where('fiscal_year_id', $fiscalYear->id)
            ->where('accounting_journal_id', $journal->id)
            ->count();

        return sprintf('%s-%s-%04d', $journal->code, $fiscalYear->id, $count + 1);
    }
}
