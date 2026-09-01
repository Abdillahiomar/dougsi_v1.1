<?php

namespace App\Services\Finance;

use App\Models\PaymentReceipt;

class ReceiptNumberService
{
    /**
     * Génère le prochain numéro de reçu séquentiel pour une école.
     * Format : REC-{schoolId}-{year}-{00001}
     */
    public function next(int $schoolId): string
    {
        $year   = now()->format('Y');
        $prefix = 'REC';

        $lastNumber = PaymentReceipt::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->where('receipt_number', 'like', "{$prefix}-{$schoolId}-{$year}-%")
            ->selectRaw("MAX(CAST(SPLIT_PART(receipt_number, '-', 4) AS INTEGER)) AS max_num")
            ->value('max_num') ?? 0;

        $count  = $lastNumber + 1;
        $number = sprintf('%s-%d-%s-%05d', $prefix, $schoolId, $year, $count);

        while (PaymentReceipt::withoutGlobalScopes()->where('receipt_number', $number)->exists()) {
            $count++;
            $number = sprintf('%s-%d-%s-%05d', $prefix, $schoolId, $year, $count);
        }

        return $number;
    }
}