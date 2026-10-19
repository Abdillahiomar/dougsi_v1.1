<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Invoice $invoice,
        public readonly Payment $payment,
    ) {
    }

    public function envelope(): Envelope
    {
        $schoolName = $this->invoice->school?->name ?? 'Dugsi';

        return new Envelope(
            subject: "{$schoolName} — Reçu de paiement {$this->invoice->invoice_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-receipt',
            with: [
                'schoolName'    => $this->invoice->school?->name ?? 'Dugsi',
                'invoiceNumber' => $this->invoice->invoice_number,
                'amount'        => $this->payment->amount,
                'method'        => $this->payment->method,
                'reference'     => $this->payment->reference,
                'paidAt'        => $this->payment->paid_at?->format('d/m/Y'),
            ],
        );
    }
}
