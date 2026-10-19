<?php

namespace App\Http\Controllers\Superadmin;

use App\Models\Invoice;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfController extends Controller
{
    public function show(Invoice $invoice)
    {
        abort_unless(auth('superadmin')->check(), 403);

        $invoice->load(['school', 'subscription.plan']);

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'))->setPaper('a4', 'portrait');

        return $pdf->stream($invoice->invoice_number . '.pdf');
    }
}
