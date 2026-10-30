<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'school_id', 'fiscal_year_id', 'accounting_journal_id', 'entry_number',
        'date', 'description', 'source_type', 'source_id', 'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function journal()
    {
        return $this->belongsTo(AccountingJournal::class, 'accounting_journal_id');
    }

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function totalDebit(): int
    {
        return (int) $this->lines->sum('debit');
    }

    public function totalCredit(): int
    {
        return (int) $this->lines->sum('credit');
    }
}
