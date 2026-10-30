<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class AccountingAccount extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'school_id', 'code', 'label', 'class', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }
}
