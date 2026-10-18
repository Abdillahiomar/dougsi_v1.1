<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

/**
 * Enregistre un paiement réel (montant, méthode, référence) contre une
 * facture d'abonnement SaaS, au lieu de se contenter de marquer son statut.
 */
class SubscriptionPaymentService
{
    public function recordPayment(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $payment = Payment::create([
                'school_id'       => $invoice->school_id,
                'subscription_id' => $invoice->subscription_id,
                'reference'       => $data['reference'] ?: $this->generateReference(),
                'amount'          => $data['amount'],
                'method'          => $data['method'] ?: null,
                'status'          => 'paid',
                'paid_at'         => $data['paid_at'] ?? now(),
            ]);

            $invoice->update([
                'payment_id' => $payment->id,
                'status'     => 'paid',
            ]);

            return $payment;
        });
    }

    protected function generateReference(): string
    {
        return 'PAY-' . now()->format('YmdHis') . '-' . random_int(100, 999);
    }
}
