<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'school_id', 'plan_id', 'custom_monthly_amount', 'discount_percent',
        'billing_cycle', 'status', 'starts_at', 'ends_at', 'auto_renew',
    ];

    protected $casts = [
        'custom_monthly_amount' => 'decimal:2',
        'discount_percent'      => 'decimal:2',
        'starts_at'  => 'date',
        'ends_at'    => 'date',
        'auto_renew' => 'boolean',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function monthsPerCycle(): int
    {
        return match ($this->billing_cycle) {
            'quarterly'  => 3,
            'semiannual' => 6,
            'annual'     => 12,
            default      => 1,
        };
    }

    public function effectiveMonthlyAmount(): float
    {
        return (float) ($this->custom_monthly_amount ?? $this->plan?->price ?? 0);
    }

    public function cycleAmount(): float
    {
        $base = $this->effectiveMonthlyAmount() * $this->monthsPerCycle();
        $discount = $base * ($this->discount_percent / 100);

        return round($base - $discount, 2);
    }

    public function cycleLabel(): string
    {
        return match ($this->billing_cycle) {
            'quarterly'  => 'Trimestriel',
            'semiannual' => 'Semestriel',
            'annual'     => 'Annuel',
            default      => 'Mensuel',
        };
    }
}