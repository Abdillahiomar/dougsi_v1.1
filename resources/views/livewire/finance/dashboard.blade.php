<?php

use App\Models\CashSession;
use App\Models\PaymentReceipt;
use App\Models\StudentInvoice;
use App\Services\AcademicYearService;
use Livewire\Component;

new class extends Component
{
    public string $scope = 'year'; // year | month | quarter

    /**
     * Base des factures.
     *
     * PostgreSQL compatible.
     */
    private function invoiceBase(int $schoolId, int $yearId)
    {
        return StudentInvoice::withoutGlobalScopes()
            ->where('student_invoices.school_id', $schoolId)
            ->where('student_invoices.academic_year_id', $yearId)
            ->where('student_invoices.status', '!=', 'cancelled');
    }

    /**
     * Base des reçus de paiement.
     *
     * PostgreSQL compatible.
     */
    private function receiptBase(int $schoolId, int $yearId)
    {
        return PaymentReceipt::withoutGlobalScopes()
            ->where('payment_receipts.school_id', $schoolId)
            ->where('payment_receipts.academic_year_id', $yearId)
            ->whereNull('payment_receipts.voided_at');
    }

    public function with(): array
    {
        $schoolId = auth()->user()->school_id;
        $year     = AcademicYearService::current();

        if (! $year) {
            return [
                'year'  => null,
                'ready' => false,
            ];
        }

        // ============================================================
        // KPI GLOBAUX
        // ============================================================

        $agg = $this->invoiceBase($schoolId, $year->id)
            ->selectRaw('
                SUM(student_invoices.amount_due) AS due,
                SUM(student_invoices.amount_paid) AS paid
            ')
            ->first();

        $due  = (int) ($agg->due ?? 0);
        $paid = (int) ($agg->paid ?? 0);

        $left = $due - $paid;

        $rate = $due > 0
            ? round(($paid / $due) * 100, 1)
            : 0.0;


        // ============================================================
        // ENCAISSEMENT CE MOIS
        // ============================================================

        $thisMonth = (int) $this->receiptBase(
            $schoolId,
            $year->id
        )
            ->whereBetween(
                'payment_receipts.paid_at',
                [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ]
            )
            ->sum('payment_receipts.amount');


        // ============================================================
        // ENCAISSEMENT MOIS PRÉCÉDENT
        // ============================================================

        $lastMonth = (int) $this->receiptBase(
            $schoolId,
            $year->id
        )
            ->whereBetween(
                'payment_receipts.paid_at',
                [
                    now()->subMonthNoOverflow()->startOfMonth(),
                    now()->subMonthNoOverflow()->endOfMonth(),
                ]
            )
            ->sum('payment_receipts.amount');


        // ============================================================
        // ÉVOLUTION
        // ============================================================

        $delta = $lastMonth > 0
            ? round(
                (($thisMonth - $lastMonth) / $lastMonth) * 100,
                1
            )
            : null;


        // ============================================================
        // BALANCE ÂGÉE
        //
        // Syntaxe PostgreSQL :
        // CURRENT_DATE
        // INTERVAL '30 days'
        // ============================================================

        $aging = $this->invoiceBase(
            $schoolId,
            $year->id
        )
            ->whereRaw(
                'student_invoices.amount_paid < student_invoices.amount_due'
            )

            ->selectRaw("
                SUM(
                    CASE
                        WHEN student_invoices.due_at IS NULL
                             OR student_invoices.due_at >= CURRENT_DATE
                        THEN
                            student_invoices.amount_due
                            - student_invoices.amount_paid
                        ELSE 0
                    END
                ) AS non_echu,

                SUM(
                    CASE
                        WHEN student_invoices.due_at < CURRENT_DATE
                             AND student_invoices.due_at >= CURRENT_DATE - INTERVAL '30 days'
                        THEN
                            student_invoices.amount_due
                            - student_invoices.amount_paid
                        ELSE 0
                    END
                ) AS b30,

                SUM(
                    CASE
                        WHEN student_invoices.due_at < CURRENT_DATE - INTERVAL '30 days'
                             AND student_invoices.due_at >= CURRENT_DATE - INTERVAL '60 days'
                        THEN
                            student_invoices.amount_due
                            - student_invoices.amount_paid
                        ELSE 0
                    END
                ) AS b60,

                SUM(
                    CASE
                        WHEN student_invoices.due_at < CURRENT_DATE - INTERVAL '60 days'
                        THEN
                            student_invoices.amount_due
                            - student_invoices.amount_paid
                        ELSE 0
                    END
                ) AS b90
            ")
            ->first();


        // ============================================================
        // RECOUVREMENT PAR CLASSE
        // ============================================================

        $byClass = $this->invoiceBase(
            $schoolId,
            $year->id
        )

            ->join(
                'student_school_years AS ssy',
                'ssy.id',
                '=',
                'student_invoices.student_school_year_id'
            )

            ->join(
                'school_classes AS sc',
                'sc.id',
                '=',
                'ssy.school_class_id'
            )

            ->groupBy(
                'sc.id',
                'sc.name'
            )

            ->selectRaw('
                sc.id,
                sc.name,

                SUM(student_invoices.amount_due) AS due,

                SUM(student_invoices.amount_paid) AS paid,

                COUNT(DISTINCT ssy.student_id) AS nb
            ')

            ->get()

            ->map(fn ($r) => [

                'name' => $r->name,

                'nb' => (int) $r->nb,

                'due' => (int) $r->due,

                'paid' => (int) $r->paid,

                'left' =>
                    (int) $r->due
                    - (int) $r->paid,

                'rate' => $r->due > 0
                    ? round(
                        ($r->paid / $r->due) * 100,
                        1
                    )
                    : 0.0,
            ])

            ->sortBy('rate')
            ->values();


        // ============================================================
        // COURBE MENSUELLE
        //
        // MySQL :
        // DATE_FORMAT(paid_at, '%Y-%m')
        //
        // PostgreSQL :
        // TO_CHAR(paid_at, 'YYYY-MM')
        // ============================================================

        $raw = $this->receiptBase(
            $schoolId,
            $year->id
        )

            ->selectRaw("
                TO_CHAR(
                    payment_receipts.paid_at,
                    'YYYY-MM'
                ) AS m,

                SUM(
                    payment_receipts.amount
                ) AS total
            ")

            ->groupByRaw("
                TO_CHAR(
                    payment_receipts.paid_at,
                    'YYYY-MM'
                )
            ")

            ->orderBy('m')

            ->pluck(
                'total',
                'm'
            );


        // ============================================================
        // SÉRIE CONTINUE DES 12 DERNIERS MOIS
        // ============================================================

        $monthly = collect(range(11, 0))
            ->map(function ($i) use ($raw) {

                $d = now()->subMonthsNoOverflow($i);

                $key = $d->format('Y-m');

                return [
                    'key' => $key,

                    'label' =>
                        $d->locale('fr')
                            ->isoFormat('MMM'),

                    'total' =>
                        (int) ($raw[$key] ?? 0),
                ];
            });

        $monthlyMax = max(
            1,
            $monthly->max('total')
        );


        // ============================================================
        // PAR TYPE DE FRAIS
        //
        // ATTENTION :
        // Le fichier original ne contient pas encore la requête.
        // Le nom exact de la table/colonne fee_structures n'est pas
        // suffisamment défini.
        //
        // On initialise donc la variable pour éviter :
        // "Undefined variable $byFee"
        // ============================================================

        $byFee = collect();


        // ============================================================
        // TOP DÉBITEURS
        // ============================================================

        $debtors = $this->invoiceBase(
            $schoolId,
            $year->id
        )

            ->join(
                'student_school_years AS ssy',
                'ssy.id',
                '=',
                'student_invoices.student_school_year_id'
            )

            ->join(
                'students AS s',
                's.id',
                '=',
                'ssy.student_id'
            )

            ->leftJoin(
                'school_classes AS sc',
                'sc.id',
                '=',
                'ssy.school_class_id'
            )

            ->groupBy(
                's.id',
                's.first_name',
                's.last_name',
                's.matricule',
                'sc.name'
            )

            ->selectRaw('
                s.id,
                s.first_name,
                s.last_name,
                s.matricule,
                sc.name AS class_name,

                SUM(
                    student_invoices.amount_due
                    - student_invoices.amount_paid
                ) AS reste,

                SUM(
                    CASE
                        WHEN student_invoices.status = \'overdue\'
                        THEN 1
                        ELSE 0
                    END
                ) AS nb_retard
            ')

            /*
             * PostgreSQL :
             * On ne peut pas utiliser directement l'alias
             * "reste" dans HAVING.
             */
            ->havingRaw('
                SUM(
                    student_invoices.amount_due
                    - student_invoices.amount_paid
                ) > 0
            ')

            ->orderByDesc('reste')

            ->limit(10)

            ->get();


        // ============================================================
        // CAISSES OUVERTES
        // ============================================================

        $openSessions = CashSession::withoutGlobalScopes()

            ->where(
                'school_id',
                $schoolId
            )

            ->where(
                'status',
                'open'
            )

            ->with('user')

            ->get();


        // ============================================================
        // ÉCARTS DE CAISSE DU MOIS
        // ============================================================

        $variance = (int) CashSession::withoutGlobalScopes()

            ->where(
                'school_id',
                $schoolId
            )

            ->where(
                'status',
                'closed'
            )

            ->whereBetween(
                'closed_at',
                [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ]
            )

            ->sum('variance');


        // ============================================================
        // NOMBRE D'ÉCHÉANCES EN RETARD
        // ============================================================

        $overdueCount = $this->invoiceBase(
            $schoolId,
            $year->id
        )

            ->where(
                'student_invoices.status',
                'overdue'
            )

            ->count();


        // ============================================================
        // RETOUR
        // ============================================================

        return compact(
            'year',
            'due',
            'paid',
            'left',
            'rate',
            'thisMonth',
            'lastMonth',
            'delta',
            'aging',
            'byClass',
            'monthly',
            'monthlyMax',
            'byFee',
            'debtors',
            'openSessions',
            'variance',
            'overdueCount'
        ) + [
            'ready' => true,
        ];
    }
}; ?>
