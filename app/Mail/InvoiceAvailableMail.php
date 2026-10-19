<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceAvailableMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Invoice $invoice)
    {
    }

    public function envelope(): Envelope
    {
        $schoolName = $this->invoice->school?->name ?? 'Dugsi';

        return new Envelope(
            subject: "{$schoolName} — Facture {$this->invoice->invoice_number} disponible",
        );
    }

    public function content(): Content
    {
        $invoice = $this->invoice;

        return new Content(
            view: 'emails.invoice-available',
            with: [
                'schoolName'     => $invoice->school?->name ?? 'Dugsi',
                'invoiceNumber'  => $invoice->invoice_number,
                'amount'         => $invoice->amount,
                'issuedAt'       => $invoice->issued_at?->format('d/m/Y'),
                'dueAt'          => $invoice->due_at?->format('d/m/Y'),
                'periodLabel'    => $invoice->subscription?->cycleLabel(),
            ],
        );
    }
}
