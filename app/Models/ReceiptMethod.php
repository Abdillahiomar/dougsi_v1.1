<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptMethod extends Model
{
    protected $fillable = [
        'payment_receipt_id', 'method', 'amount', 'reference',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function receipt()
    {
        return $this->belongsTo(PaymentReceipt::class, 'payment_receipt_id');
    }

    public function methodLabel(): string
    {
        return PaymentReceipt::METHODS[$this->method] ?? $this->method;
    }
}