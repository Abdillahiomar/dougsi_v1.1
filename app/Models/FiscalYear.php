<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class FiscalYear extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'school_id', 'label', 'starts_at', 'ends_at', 'status', 'is_active',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at'   => 'date',
        'is_active' => 'boolean',
    ];

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
