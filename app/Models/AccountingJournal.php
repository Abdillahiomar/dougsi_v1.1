<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class AccountingJournal extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'school_id', 'code', 'label',
    ];

    public function entries()
    {
        return $this->hasMany(JournalEntry::class);
    }
}
