<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'school_id', 'fiscal_year_id', 'accounting_account_id', 'journal_entry_id',
        'label', 'amount', 'date', 'payment_method', 'receipt_path', 'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(AccountingAccount::class, 'accounting_account_id');
    }

    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receiptUrl(): ?string
    {
        return $this->receipt_path ? asset('storage/'.$this->receipt_path) : null;
    }
}
