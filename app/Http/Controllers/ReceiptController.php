<?php

namespace App\Http\Controllers;

use App\Models\PaymentReceipt;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function __invoke(Request $request, PaymentReceipt $receipt)
    {
        // Double barrière : le scope tenant + une vérification explicite
        abort_unless($receipt->school_id === $request->user()->school_id, 404);

        $receipt->load(['student.currentSchoolYear.schoolClass', 'lines.invoice', 'receivedBy', 'academicYear']);
        //$receipt = PaymentReceipt::with(['lines.invoice', 'student', 'receivedBy', 'academicYear', 'methods'])->findOrFail(...);
        return view('finance.receipt', [
            'receipt' => $receipt,
            'school'  => $request->user()->school,
        ]);
    }
}