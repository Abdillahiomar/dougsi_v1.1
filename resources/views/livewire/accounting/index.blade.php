<?php

use App\Models\AccountingAccount;
use App\Models\AccountingJournal;
use App\Models\Expense;
use App\Models\FiscalYear;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Services\AccountingSetupService;
use App\Services\ExpenseService;
use App\Services\JournalEntryService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $activeTab = 'accounts';

    // ── Plan comptable ──────────────────────────────────────────────
    public bool   $showAccountForm = false;
    public string $accCode  = '';
    public string $accLabel = '';
    public string $accClass = '6';

    public ?int   $editAccountId = null;
    public string $editAccCode  = '';
    public string $editAccLabel = '';
    public string $editAccClass = '6';

    // ── Exercices comptables ────────────────────────────────────────
    public bool   $showFyForm = false;
    public string $fyLabel = '';
    public string $fyStart = '';
    public string $fyEnd   = '';

    // ── Écritures ────────────────────────────────────────────────────
    public string $filterFiscalYearId = '';
    public string $filterJournalId    = '';

    public bool    $showEntryForm    = false;
    public string  $entryJournalId   = '';
    public string  $entryDate        = '';
    public string  $entryDescription = '';
    public array   $entryLines       = [];
    public ?string $entryError       = null;

    // ── Dépenses ─────────────────────────────────────────────────────
    public string  $expenseFiscalYearId  = '';
    public bool    $showExpenseForm      = false;
    public string  $expenseAccountId     = '';
    public string  $expenseLabel         = '';
    public string  $expenseAmount        = '';
    public string  $expenseDate          = '';
    public string  $expensePaymentMethod = 'cash';
    public $receiptFile = null;
    public ?string $expenseError = null;

    // ── Grand livre ──────────────────────────────────────────────────
    public string $ledgerFiscalYearId = '';
    public string $ledgerAccountId    = '';

    // ── Balance générale ─────────────────────────────────────────────
    public string $balanceFiscalYearId = '';

    // ── Compte de résultat ───────────────────────────────────────────
    public string $incomeFiscalYearId = '';

    // ── Bilan ────────────────────────────────────────────────────────
    public string $bilanFiscalYearId = '';

    public function mount(): void
    {
        $schoolId = auth()->user()->school_id;

        if (! AccountingAccount::query()->exists()) {
            AccountingSetupService::seedForSchool($schoolId);
        }

        $activeFy = FiscalYear::where('is_active', true)->first();
        $fyId = $activeFy ? (string) $activeFy->id : '';

        $this->filterFiscalYearId  = $fyId;
        $this->expenseFiscalYearId = $fyId;
        $this->ledgerFiscalYearId  = $fyId;
        $this->balanceFiscalYearId = $fyId;
        $this->incomeFiscalYearId  = $fyId;
        $this->bilanFiscalYearId   = $fyId;
        $this->entryDate           = now()->format('Y-m-d');
        $this->expenseDate         = now()->format('Y-m-d');

        $this->resetEntryLines();
    }

    private function resetEntryLines(): void
    {
        $this->entryLines = [
            ['accounting_account_id' => '', 'label' => '', 'debit' => '', 'credit' => ''],
            ['accounting_account_id' => '', 'label' => '', 'debit' => '', 'credit' => ''],
        ];
    }

    // ══ PLAN COMPTABLE ════════════════════════════════════════════════

    public function saveAccount(): void
    {
        abort_unless(auth()->user()->can('accounting.manage'), 403);

        $this->validate([
            'accCode'  => ['required', 'string', 'max:10', Rule::unique('accounting_accounts', 'code')->where('school_id', auth()->user()->school_id)],
            'accLabel' => 'required|string|max:150',
            'accClass' => 'required|integer|min:1|max:8',
        ]);

        AccountingAccount::create([
            'school_id' => auth()->user()->school_id,
            'code'      => $this->accCode,
            'label'     => $this->accLabel,
            'class'     => $this->accClass,
        ]);

        $this->reset(['accCode', 'accLabel', 'showAccountForm']);
        $this->accClass = '6';
    }

    public function startEditAccount(int $id): void
    {
        $acc = AccountingAccount::find($id);
        if (! $acc) return;

        $this->editAccountId = $id;
        $this->editAccCode   = $acc->code;
        $this->editAccLabel  = $acc->label;
        $this->editAccClass  = (string) $acc->class;
    }

    public function saveEditAccount(): void
    {
        abort_unless(auth()->user()->can('accounting.manage'), 403);

        $this->validate([
            'editAccCode'  => ['required', 'string', 'max:10', Rule::unique('accounting_accounts', 'code')->where('school_id', auth()->user()->school_id)->ignore($this->editAccountId)],
            'editAccLabel' => 'required|string|max:150',
            'editAccClass' => 'required|integer|min:1|max:8',
        ]);

        AccountingAccount::where('id', $this->editAccountId)->update([
            'code'  => $this->editAccCode,
            'label' => $this->editAccLabel,
            'class' => $this->editAccClass,
        ]);

        $this->editAccountId = null;
    }

    public function toggleAccountActive(int $id): void
    {
        abort_unless(auth()->user()->can('accounting.manage'), 403);

        $acc = AccountingAccount::find($id);
        if ($acc) {
            $acc->update(['is_active' => ! $acc->is_active]);
        }
    }

    // ══ EXERCICES COMPTABLES ══════════════════════════════════════════

    public function saveFiscalYear(): void
    {
        abort_unless(auth()->user()->can('accounting.manage'), 403);

        $this->validate([
            'fyLabel' => 'required|string|max:50',
            'fyStart' => 'required|date',
            'fyEnd'   => 'required|date|after:fyStart',
        ]);

        $isFirst = ! FiscalYear::query()->exists();

        FiscalYear::create([
            'school_id' => auth()->user()->school_id,
            'label'     => $this->fyLabel,
            'starts_at' => $this->fyStart,
            'ends_at'   => $this->fyEnd,
            'status'    => 'open',
            'is_active' => $isFirst,
        ]);

        $this->reset(['fyLabel', 'fyStart', 'fyEnd', 'showFyForm']);
    }

    public function setActiveFiscalYear(int $id): void
    {
        abort_unless(auth()->user()->can('accounting.manage'), 403);

        FiscalYear::query()->update(['is_active' => false]);
        FiscalYear::where('id', $id)->update(['is_active' => true]);
    }

    public function toggleFiscalYearStatus(int $id): void
    {
        abort_unless(auth()->user()->can('accounting.manage'), 403);

        $fy = FiscalYear::find($id);
        if ($fy) {
            $fy->update(['status' => $fy->status === 'open' ? 'closed' : 'open']);
        }
    }

    // ══ ÉCRITURES ═════════════════════════════════════════════════════

    public function addEntryLine(): void
    {
        $this->entryLines[] = ['accounting_account_id' => '', 'label' => '', 'debit' => '', 'credit' => ''];
    }

    public function removeEntryLine(int $i): void
    {
        if (count($this->entryLines) <= 2) return;
        array_splice($this->entryLines, $i, 1);
    }

    public function toggleEntryForm(): void
    {
        $this->showEntryForm = ! $this->showEntryForm;
        $this->entryError    = null;
        if ($this->showEntryForm) {
            $this->entryJournalId   = '';
            $this->entryDescription = '';
            $this->entryDate        = now()->format('Y-m-d');
            $this->resetEntryLines();
        }
    }

    public function saveEntry(): void
    {
        abort_unless(auth()->user()->can('accounting.manage'), 403);
        $this->entryError = null;

        $this->validate([
            'entryJournalId'   => 'required|exists:accounting_journals,id',
            'entryDate'        => 'required|date',
            'entryDescription' => 'required|string|max:255',
        ]);

        $fiscalYear = $this->filterFiscalYearId ? FiscalYear::find($this->filterFiscalYearId) : null;
        if (! $fiscalYear) {
            $this->entryError = "Sélectionne d'abord un exercice comptable en haut de la page.";
            return;
        }

        $journal = AccountingJournal::find($this->entryJournalId);
        if (! $journal) return;

        try {
            JournalEntryService::createEntry(
                $fiscalYear,
                $journal,
                $this->entryDate,
                $this->entryDescription,
                $this->entryLines,
                createdBy: auth()->id(),
            );
        } catch (\InvalidArgumentException $e) {
            $this->entryError = $e->getMessage();
            return;
        }

        $this->reset(['entryJournalId', 'entryDescription', 'showEntryForm']);
        $this->resetEntryLines();
    }

    // ══ DÉPENSES ══════════════════════════════════════════════════════

    public function toggleExpenseForm(): void
    {
        $this->showExpenseForm = ! $this->showExpenseForm;
        $this->expenseError    = null;
        if ($this->showExpenseForm) {
            $this->reset(['expenseAccountId', 'expenseLabel', 'expenseAmount', 'receiptFile']);
            $this->expenseDate          = now()->format('Y-m-d');
            $this->expensePaymentMethod = 'cash';
        }
    }

    public function saveExpense(): void
    {
        abort_unless(auth()->user()->can('accounting.manage'), 403);
        $this->expenseError = null;

        $this->validate([
            'expenseAccountId'     => 'required|exists:accounting_accounts,id',
            'expenseLabel'         => 'required|string|max:255',
            'expenseAmount'        => 'required|integer|min:1',
            'expenseDate'          => 'required|date',
            'expensePaymentMethod' => 'required|in:cash,bank',
            'receiptFile'          => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $fiscalYear = $this->expenseFiscalYearId ? FiscalYear::find($this->expenseFiscalYearId) : null;
        if (! $fiscalYear) {
            $this->expenseError = "Sélectionne d'abord un exercice comptable en haut de la page.";
            return;
        }

        $account = AccountingAccount::find($this->expenseAccountId);
        if (! $account) return;

        $receiptPath = $this->receiptFile ? $this->receiptFile->store('expenses/receipts', 'public') : null;

        try {
            ExpenseService::create(
                $fiscalYear,
                $account,
                $this->expenseLabel,
                (int) $this->expenseAmount,
                $this->expenseDate,
                $this->expensePaymentMethod,
                $receiptPath,
                auth()->id(),
            );
        } catch (\InvalidArgumentException $e) {
            $this->expenseError = $e->getMessage();
            return;
        }

        $this->reset(['expenseAccountId', 'expenseLabel', 'expenseAmount', 'receiptFile', 'showExpenseForm']);
    }

    public function deleteExpense(int $id): void
    {
        abort_unless(auth()->user()->can('accounting.manage'), 403);

        $expense = Expense::find($id);
        if ($expense) {
            ExpenseService::delete($expense);
        }
    }

    // ══ RENDU ═════════════════════════════════════════════════════════

    public function with(): array
    {
        $accounts    = AccountingAccount::orderBy('code')->get();
        $fiscalYears = FiscalYear::orderByDesc('starts_at')->get();
        $journals    = AccountingJournal::orderBy('code')->get();

        $entries = collect();
        if ($this->filterFiscalYearId) {
            $entries = JournalEntry::with(['lines.account', 'journal'])
                ->where('fiscal_year_id', $this->filterFiscalYearId)
                ->when($this->filterJournalId, fn ($q) => $q->where('accounting_journal_id', $this->filterJournalId))
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get();
        }

        $ledgerLines   = collect();
        $ledgerBalance = 0;
        if ($this->ledgerFiscalYearId && $this->ledgerAccountId) {
            $ledgerLines = JournalEntryLine::with('journalEntry.journal')
                ->where('accounting_account_id', $this->ledgerAccountId)
                ->whereHas('journalEntry', fn ($q) => $q->where('fiscal_year_id', $this->ledgerFiscalYearId))
                ->get()
                ->sortBy(fn ($l) => $l->journalEntry->date)
                ->values();

            $running = 0;
            $ledgerLines->each(function ($l) use (&$running) {
                $running += $l->debit - $l->credit;
                $l->running_balance = $running;
            });
            $ledgerBalance = $running;
        }

        $balanceRows = collect();
        if ($this->balanceFiscalYearId) {
            $balanceRows = $this->accountTotalsFor($this->balanceFiscalYearId)
                ->filter(fn ($a) => ($a->total_debit ?? 0) > 0 || ($a->total_credit ?? 0) > 0)
                ->values();
        }

        $expenses = collect();
        if ($this->expenseFiscalYearId) {
            $expenses = Expense::with(['account', 'creator'])
                ->where('fiscal_year_id', $this->expenseFiscalYearId)
                ->orderByDesc('date')
                ->get();
        }

        // ── Compte de résultat (classes 6 Charges / 7 Produits) ───────
        $chargesRows  = collect();
        $produitsRows = collect();
        $totalCharges = 0;
        $totalProduits = 0;
        $netResult    = 0;
        if ($this->incomeFiscalYearId) {
            $rows = $this->accountTotalsFor($this->incomeFiscalYearId)
                ->filter(fn ($a) => ($a->total_debit ?? 0) > 0 || ($a->total_credit ?? 0) > 0);

            $chargesRows  = $rows->where('class', 6)->values();
            $produitsRows = $rows->where('class', 7)->values();
            $totalCharges = (int) $chargesRows->sum('total_debit');
            $totalProduits = (int) $produitsRows->sum('total_credit');
            $netResult    = $totalProduits - $totalCharges;
        }

        // ── Bilan (classes 1 à 5, classées par solde débiteur/créditeur) ──
        $actifRows   = collect();
        $passifRows  = collect();
        $actifTotal  = 0;
        $passifTotal = 0;
        $bilanResult = 0;
        if ($this->bilanFiscalYearId) {
            $rows = $this->accountTotalsFor($this->bilanFiscalYearId)
                ->whereIn('class', [1, 2, 3, 4, 5])
                ->map(function ($a) {
                    $a->solde = ((int) ($a->total_debit ?? 0)) - ((int) ($a->total_credit ?? 0));
                    return $a;
                })
                ->filter(fn ($a) => $a->solde !== 0);

            $actifRows  = $rows->filter(fn ($a) => $a->solde > 0)->values();
            $passifRows = $rows->filter(fn ($a) => $a->solde < 0)->values();

            $resultRows  = $this->accountTotalsFor($this->bilanFiscalYearId)->whereIn('class', [6, 7]);
            $bilanResult = (int) $resultRows->where('class', 7)->sum('total_credit')
                         - (int) $resultRows->where('class', 6)->sum('total_debit');

            $actifTotal  = $actifRows->sum('solde');
            $passifTotal = $passifRows->sum(fn ($a) => abs($a->solde)) + $bilanResult;
        }

        // ── Résultat de chaque exercice (affiché dans l'onglet Exercices) ──
        $fiscalYearResults = [];
        foreach ($fiscalYears as $fy) {
            $r = $this->accountTotalsFor((string) $fy->id)->whereIn('class', [6, 7]);
            $fiscalYearResults[$fy->id] = (int) $r->where('class', 7)->sum('total_credit')
                                        - (int) $r->where('class', 6)->sum('total_debit');
        }

        return compact(
            'accounts', 'fiscalYears', 'journals', 'entries', 'ledgerLines', 'ledgerBalance', 'balanceRows', 'expenses',
            'chargesRows', 'produitsRows', 'totalCharges', 'totalProduits', 'netResult',
            'actifRows', 'passifRows', 'actifTotal', 'passifTotal', 'bilanResult', 'fiscalYearResults'
        );
    }

    /**
     * Pour chaque compte, calcule le total débit/crédit cumulé sur un exercice
     * donné. Base commune réutilisée par la balance générale, le compte de
     * résultat et le bilan.
     */
    private function accountTotalsFor(string $fiscalYearId): \Illuminate\Support\Collection
    {
        return AccountingAccount::query()
            ->withSum(['lines as total_debit' => fn ($q) =>
                $q->whereHas('journalEntry', fn ($qq) => $qq->where('fiscal_year_id', $fiscalYearId))
            ], 'debit')
            ->withSum(['lines as total_credit' => fn ($q) =>
                $q->whereHas('journalEntry', fn ($qq) => $qq->where('fiscal_year_id', $fiscalYearId))
            ], 'credit')
            ->orderBy('code')
            ->get();
    }
}; ?>

<style>
    .tabs { display:flex; background:var(--paper); border:1px solid var(--line); border-radius:10px; padding:4px; margin-bottom:1.5rem; gap:.25rem; flex-wrap:wrap; }
    .tab  { display:inline-flex; align-items:center; gap:6px; padding:.45rem 1rem; border-radius:7px; font-size:.875rem; font-weight:500; font-family:'Inter',sans-serif; color:var(--ink); border:none; cursor:pointer; background:none; opacity:.55; transition:all .12s; }
    .tab:hover { opacity:.9; background:var(--paper-raised); }
    .tab.active { background:var(--sidebar); color:#FFFFFF; opacity:1; }

    .card { border-radius:12px; border:1px solid var(--line); background:var(--paper-raised); overflow:hidden; margin-bottom:1.25rem; }
    .card:last-child { margin-bottom:0; }
    .card-header { padding:.875rem 1.5rem; border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; }
    .card-title  { font-family:'Fraunces',serif; font-size:.95rem; font-weight:600; color:var(--ink); }
    .card-body   { padding:1.25rem 1.5rem; }

    .form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem; }
    .form-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem; margin-bottom:1rem; }
    .form-grid-4 { display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:1rem; margin-bottom:1rem; }
    @media(max-width:700px) { .form-grid-2,.form-grid-3,.form-grid-4 { grid-template-columns:1fr; } }
    .form-field { display:flex; flex-direction:column; gap:.35rem; }
    .form-label { font-family:'JetBrains Mono',monospace; font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:var(--ink); opacity:.5; }
    .form-input, .form-select-inp { padding:.5rem .75rem; border-radius:8px; border:1px solid var(--line); background:var(--paper); font-size:.875rem; font-family:'Inter',sans-serif; color:var(--ink); outline:none; width:100%; transition:border-color .15s; }
    .form-input:focus,.form-select-inp:focus { border-color:var(--sidebar-soft); box-shadow:0 0 0 3px rgba(42,63,126,.08); }
    .form-error { font-size:.75rem; color:var(--accent-red); margin-top:2px; }
    .form-actions { display:flex; justify-content:flex-end; gap:.65rem; padding-top:1.25rem; border-top:1px solid var(--line); margin-top:1.25rem; }

    .btn-save { display:inline-flex; align-items:center; gap:5px; padding:.5rem 1.25rem; border-radius:8px; background:var(--sidebar); color:#FFFFFF; font-size:.875rem; font-weight:600; font-family:'Inter',sans-serif; border:none; cursor:pointer; }
    .btn-save:hover { background:var(--sidebar-soft); }
    .btn-save svg { width:14px; height:14px; }
    .btn-cancel-sm { padding:.5rem 1.1rem; border-radius:8px; border:1px solid var(--line); background:var(--paper); font-size:.875rem; font-weight:500; font-family:'Inter',sans-serif; color:var(--ink); cursor:pointer; }
    .btn-sm { display:inline-flex; align-items:center; gap:4px; padding:.35rem .75rem; border-radius:6px; font-size:.8125rem; font-weight:600; font-family:'Inter',sans-serif; border:none; cursor:pointer; }
    .btn-sm svg { width:13px; height:13px; }
    .btn-add  { background:rgba(42,63,126,.08); color:var(--sidebar-soft); }
    .btn-add:hover { background:rgba(42,63,126,.16); }
    .btn-del  { background:rgba(224,92,58,.08); color:var(--accent-red); }
    .btn-del:hover { background:rgba(224,92,58,.16); }

    .select-inp { padding:.45rem .75rem; border-radius:8px; border:1px solid var(--line); background:var(--paper-raised); font-size:.875rem; font-family:'Inter',sans-serif; color:var(--ink); outline:none; cursor:pointer; }

    /* Plan comptable */
    .class-group { margin-bottom:1.25rem; }
    .class-group:last-child { margin-bottom:0; }
    .class-group-title { font-family:'JetBrains Mono',monospace; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--ink); opacity:.45; padding:0 1.5rem; margin-bottom:.4rem; }
    .acc-row { display:flex; align-items:center; gap:.75rem; padding:.6rem 1.5rem; border-bottom:1px solid var(--line); }
    .acc-row:last-child { border-bottom:none; }
    .acc-row.inactive { opacity:.4; }
    .acc-code { font-family:'JetBrains Mono',monospace; font-size:.8125rem; font-weight:700; color:var(--sidebar-soft); width:70px; flex-shrink:0; }
    .acc-label { flex:1; font-size:.875rem; color:var(--ink); }
    .badge { font-family:'JetBrains Mono',monospace; font-size:9px; font-weight:700; padding:2px 8px; border-radius:4px; text-transform:uppercase; }
    .badge-ok    { background:rgba(30,120,80,.1); color:#166534; }
    .badge-off   { background:rgba(0,0,0,.06); color:var(--ink); opacity:.5; }
    .badge-warn  { background:rgba(232,168,56,.18); color:#8A6010; }

    /* Exercices */
    .fy-row { display:flex; align-items:center; gap:.875rem; padding:.875rem 1.5rem; border-bottom:1px solid var(--line); flex-wrap:wrap; }
    .fy-row:last-child { border-bottom:none; }
    .fy-label { font-family:'Fraunces',serif; font-size:1rem; font-weight:600; color:var(--ink); }
    .fy-dates { font-family:'JetBrains Mono',monospace; font-size:11px; color:var(--ink); opacity:.5; }

    /* Écritures */
    .toolbar-row { display:flex; align-items:center; gap:.75rem; flex-wrap:wrap; margin-bottom:1.25rem; }
    .entry-card { border:1px solid var(--line); border-radius:10px; margin-bottom:.875rem; overflow:hidden; }
    .entry-card:last-child { margin-bottom:0; }
    .entry-card-head { display:flex; align-items:center; gap:.75rem; padding:.75rem 1.1rem; background:var(--paper); flex-wrap:wrap; }
    .entry-number { font-family:'JetBrains Mono',monospace; font-size:11px; font-weight:700; color:var(--sidebar-soft); }
    .entry-date { font-family:'JetBrains Mono',monospace; font-size:11px; color:var(--ink); opacity:.45; }
    .entry-desc { font-size:.875rem; font-weight:500; color:var(--ink); flex:1; }
    .entry-amount { font-family:'JetBrains Mono',monospace; font-size:.8125rem; font-weight:700; color:var(--ink); }
    .entry-lines { width:100%; border-collapse:collapse; }
    .entry-lines th { padding:.4rem 1.1rem; text-align:left; font-family:'JetBrains Mono',monospace; font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--ink); opacity:.4; }
    .entry-lines th:nth-child(3), .entry-lines th:nth-child(4) { text-align:right; }
    .entry-lines td { padding:.45rem 1.1rem; font-size:.8125rem; color:var(--ink); border-top:1px solid var(--line); }
    .entry-lines td:nth-child(3), .entry-lines td:nth-child(4) { text-align:right; font-family:'JetBrains Mono',monospace; }

    .entry-line-row { display:grid; grid-template-columns:1.6fr 1.3fr .7fr .7fr 32px; gap:.5rem; align-items:center; margin-bottom:.5rem; }
    @media(max-width:800px) { .entry-line-row { grid-template-columns:1fr 1fr; } }
    .entry-totals { display:flex; gap:1.5rem; padding:.75rem 0; margin-top:.5rem; border-top:1px solid var(--line); font-family:'JetBrains Mono',monospace; font-size:.8125rem; font-weight:700; }
    .entry-totals.balanced { color:#166534; }
    .entry-totals.unbalanced { color:var(--accent-red); }

    /* Grand livre / balance */
    table.gl-table { width:100%; border-collapse:collapse; }
    table.gl-table th { padding:.6rem 1rem; text-align:left; font-family:'JetBrains Mono',monospace; font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:var(--ink); opacity:.45; border-bottom:1px solid var(--line); }
    table.gl-table th.num, table.gl-table td.num { text-align:right; font-family:'JetBrains Mono',monospace; }
    table.gl-table td { padding:.55rem 1rem; font-size:.8125rem; color:var(--ink); border-bottom:1px solid var(--line); }
    table.gl-table tfoot td { font-weight:700; border-top:2px solid var(--line); border-bottom:none; }

    .empty { padding:3rem 2rem; text-align:center; font-size:.875rem; color:var(--ink); opacity:.45; }
</style>

<div>

    {{-- Tabs --}}
    <div class="tabs">
        @foreach ([
            ['k'=>'accounts', 'l'=>'Plan comptable'],
            ['k'=>'fiscal-years', 'l'=>'Exercices'],
            ['k'=>'entries', 'l'=>'Écritures'],
            ['k'=>'expenses', 'l'=>'Dépenses'],
            ['k'=>'ledger', 'l'=>'Grand livre'],
            ['k'=>'balance', 'l'=>'Balance générale'],
            ['k'=>'income-statement', 'l'=>'Compte de résultat'],
            ['k'=>'balance-sheet', 'l'=>'Bilan'],
        ] as $t)
            <button type="button" wire:click="$set('activeTab','{{ $t['k'] }}')"
                    class="tab {{ $activeTab===$t['k'] ? 'active' : '' }}">
                {{ $t['l'] }}
            </button>
        @endforeach
    </div>

    {{-- ══ PLAN COMPTABLE ══ --}}
    @if ($activeTab === 'accounts')
        <div class="card">
            <div class="card-header">
                <span class="card-title">Plan comptable (SYSCOHADA)</span>
                <button wire:click="$toggle('showAccountForm')" class="btn-sm btn-add">+ Nouveau compte</button>
            </div>
            <div class="card-body">
                @if ($showAccountForm)
                    <div class="form-grid-3" style="margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid var(--line);">
                        <div class="form-field">
                            <label class="form-label">Code</label>
                            <input wire:model="accCode" type="text" class="form-input" placeholder="Ex: 616000" maxlength="10">
                            @error('accCode') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-field">
                            <label class="form-label">Libellé</label>
                            <input wire:model="accLabel" type="text" class="form-input" placeholder="Ex: Entretien et réparations">
                            @error('accLabel') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-field">
                            <label class="form-label">Classe</label>
                            <select wire:model="accClass" class="form-select-inp">
                                <option value="1">1 — Capitaux</option>
                                <option value="2">2 — Immobilisations</option>
                                <option value="3">3 — Stocks</option>
                                <option value="4">4 — Tiers</option>
                                <option value="5">5 — Financiers</option>
                                <option value="6">6 — Charges</option>
                                <option value="7">7 — Produits</option>
                                <option value="8">8 — Autres</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions" style="margin-top:0;padding-top:0;border-top:none;">
                        <button wire:click="$set('showAccountForm', false)" class="btn-cancel-sm">Annuler</button>
                        <button wire:click="saveAccount" class="btn-save">Créer</button>
                    </div>
                @endif
            </div>

            @php
                $classLabels = [
                    1 => 'Classe 1 — Comptes de capitaux', 2 => 'Classe 2 — Immobilisations',
                    3 => 'Classe 3 — Stocks', 4 => 'Classe 4 — Comptes de tiers',
                    5 => 'Classe 5 — Comptes financiers', 6 => 'Classe 6 — Charges',
                    7 => 'Classe 7 — Produits', 8 => 'Classe 8 — Autres',
                ];
                $grouped = $accounts->groupBy('class');
            @endphp

            @foreach ($grouped as $classNum => $classAccounts)
                <div class="class-group">
                    <div class="class-group-title">{{ $classLabels[$classNum] ?? 'Classe '.$classNum }}</div>
                    @foreach ($classAccounts as $acc)
                        <div class="acc-row {{ $acc->is_active ? '' : 'inactive' }}">
                            <span class="acc-code">{{ $acc->code }}</span>
                            <span class="acc-label">{{ $acc->label }}</span>
                            <span class="badge {{ $acc->is_active ? 'badge-ok' : 'badge-off' }}">
                                {{ $acc->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                            <button wire:click="startEditAccount({{ $acc->id }})" class="btn-sm btn-add">Modifier</button>
                            <button wire:click="toggleAccountActive({{ $acc->id }})" class="btn-sm {{ $acc->is_active ? 'btn-del' : 'btn-add' }}">
                                {{ $acc->is_active ? 'Désactiver' : 'Activer' }}
                            </button>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        {{-- Modale édition compte --}}
        @if ($editAccountId)
            <div style="position:fixed;inset:0;z-index:50;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;padding:1rem;">
                <div style="background:var(--paper-raised);border-radius:14px;border:1px solid var(--line);padding:1.75rem;max-width:460px;width:100%;">
                    <div style="font-family:'Fraunces',serif;font-size:1.1rem;font-weight:600;margin-bottom:1.25rem;">Modifier le compte</div>
                    <div class="form-grid-3">
                        <div class="form-field">
                            <label class="form-label">Code</label>
                            <input wire:model="editAccCode" type="text" class="form-input" maxlength="10">
                            @error('editAccCode') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-field" style="grid-column:span 2;">
                            <label class="form-label">Libellé</label>
                            <input wire:model="editAccLabel" type="text" class="form-input">
                            @error('editAccLabel') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-field" style="grid-column:1/-1;">
                            <label class="form-label">Classe</label>
                            <select wire:model="editAccClass" class="form-select-inp">
                                <option value="1">1 — Capitaux</option>
                                <option value="2">2 — Immobilisations</option>
                                <option value="3">3 — Stocks</option>
                                <option value="4">4 — Tiers</option>
                                <option value="5">5 — Financiers</option>
                                <option value="6">6 — Charges</option>
                                <option value="7">7 — Produits</option>
                                <option value="8">8 — Autres</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button wire:click="$set('editAccountId', null)" class="btn-cancel-sm">Annuler</button>
                        <button wire:click="saveEditAccount" class="btn-save">Enregistrer</button>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- ══ EXERCICES ══ --}}
    @if ($activeTab === 'fiscal-years')
        <div class="card">
            <div class="card-header">
                <span class="card-title">Exercices comptables</span>
                <button wire:click="$toggle('showFyForm')" class="btn-sm btn-add">+ Nouvel exercice</button>
            </div>
            <div class="card-body" style="{{ $showFyForm ? '' : 'display:none;' }}">
                <div class="form-grid-3">
                    <div class="form-field">
                        <label class="form-label">Libellé</label>
                        <input wire:model="fyLabel" type="text" class="form-input" placeholder="Ex: Exercice 2026">
                        @error('fyLabel') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-field">
                        <label class="form-label">Début</label>
                        <input wire:model="fyStart" type="date" class="form-input">
                        @error('fyStart') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-field">
                        <label class="form-label">Fin</label>
                        <input wire:model="fyEnd" type="date" class="form-input">
                        @error('fyEnd') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="form-actions">
                    <button wire:click="$set('showFyForm', false)" class="btn-cancel-sm">Annuler</button>
                    <button wire:click="saveFiscalYear" class="btn-save">Créer</button>
                </div>
            </div>

            @forelse ($fiscalYears as $fy)
                <div class="fy-row">
                    <div style="flex:1;min-width:160px;">
                        <div class="fy-label">{{ $fy->label }}</div>
                        <div class="fy-dates">{{ $fy->starts_at->format('d/m/Y') }} → {{ $fy->ends_at->format('d/m/Y') }}</div>
                    </div>
                    @if ($fy->is_active)
                        <span class="badge badge-ok">Exercice courant</span>
                    @endif
                    @php $fyResult = $fiscalYearResults[$fy->id] ?? 0; @endphp
                    <span class="badge {{ $fyResult >= 0 ? 'badge-ok' : 'badge-warn' }}">
                        Résultat : {{ number_format($fyResult, 0, ',', ' ') }} FDJ
                    </span>
                    <span class="badge {{ $fy->status === 'open' ? 'badge-ok' : 'badge-off' }}">
                        {{ $fy->status === 'open' ? 'Ouvert' : 'Clôturé' }}
                    </span>
                    @if (! $fy->is_active)
                        <button wire:click="setActiveFiscalYear({{ $fy->id }})" class="btn-sm btn-add">Définir comme courant</button>
                    @endif
                    <button wire:click="toggleFiscalYearStatus({{ $fy->id }})" class="btn-sm {{ $fy->status === 'open' ? 'btn-del' : 'btn-add' }}">
                        {{ $fy->status === 'open' ? 'Clôturer' : 'Rouvrir' }}
                    </button>
                </div>
            @empty
                <div class="empty">Aucun exercice comptable. Crée le premier ci-dessus.</div>
            @endforelse
        </div>
    @endif

    {{-- ══ ÉCRITURES ══ --}}
    @if ($activeTab === 'entries')
        <div class="toolbar-row">
            <select wire:model.live="filterFiscalYearId" class="select-inp">
                <option value="">— Exercice —</option>
                @foreach ($fiscalYears as $fy)
                    <option value="{{ $fy->id }}">{{ $fy->label }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterJournalId" class="select-inp">
                <option value="">Tous les journaux</option>
                @foreach ($journals as $j)
                    <option value="{{ $j->id }}">{{ $j->code }} — {{ $j->label }}</option>
                @endforeach
            </select>
            @if ($filterFiscalYearId)
                <button wire:click="toggleEntryForm" class="btn-sm btn-add" style="margin-left:auto;">+ Nouvelle écriture</button>
            @endif
        </div>

        @if (! $filterFiscalYearId)
            <div class="card"><div class="empty">Sélectionne un exercice comptable pour voir ou saisir des écritures.</div></div>
        @else
            @if ($showEntryForm)
                <div class="card">
                    <div class="card-header"><span class="card-title">Nouvelle écriture</span></div>
                    <div class="card-body">
                        <div class="form-grid-3">
                            <div class="form-field">
                                <label class="form-label">Journal</label>
                                <select wire:model="entryJournalId" class="form-select-inp">
                                    <option value="">— Choisir —</option>
                                    @foreach ($journals as $j)
                                        <option value="{{ $j->id }}">{{ $j->code }} — {{ $j->label }}</option>
                                    @endforeach
                                </select>
                                @error('entryJournalId') <span class="form-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-field">
                                <label class="form-label">Date</label>
                                <input wire:model="entryDate" type="date" class="form-input">
                                @error('entryDate') <span class="form-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-field">
                                <label class="form-label">Libellé de l'écriture</label>
                                <input wire:model="entryDescription" type="text" class="form-input" placeholder="Ex: Paiement loyer novembre">
                                @error('entryDescription') <span class="form-error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div style="margin-top:1rem;">
                            <div class="entry-line-row" style="margin-bottom:.35rem;">
                                <span class="form-label">Compte</span>
                                <span class="form-label">Libellé ligne</span>
                                <span class="form-label">Débit</span>
                                <span class="form-label">Crédit</span>
                                <span></span>
                            </div>
                            @foreach ($entryLines as $i => $line)
                                <div class="entry-line-row" wire:key="entry-line-{{ $i }}">
                                    <select wire:model.live="entryLines.{{ $i }}.accounting_account_id" class="form-select-inp">
                                        <option value="">— Compte —</option>
                                        @foreach ($accounts->where('is_active', true) as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->label }}</option>
                                        @endforeach
                                    </select>
                                    <input wire:model="entryLines.{{ $i }}.label" type="text" class="form-input" placeholder="Optionnel">
                                    <input wire:model.live="entryLines.{{ $i }}.debit" type="number" min="0" class="form-input" placeholder="0">
                                    <input wire:model.live="entryLines.{{ $i }}.credit" type="number" min="0" class="form-input" placeholder="0">
                                    <button type="button" wire:click="removeEntryLine({{ $i }})" class="btn-sm btn-del" style="padding:.35rem;">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            @endforeach

                            <button type="button" wire:click="addEntryLine" class="btn-sm btn-add" style="margin-top:.35rem;">+ Ligne</button>

                            @php
                                $totalDebit  = collect($entryLines)->sum(fn ($l) => (float) ($l['debit'] ?: 0));
                                $totalCredit = collect($entryLines)->sum(fn ($l) => (float) ($l['credit'] ?: 0));
                                $isBalanced  = $totalDebit === $totalCredit && $totalDebit > 0;
                            @endphp
                            <div class="entry-totals {{ $isBalanced ? 'balanced' : 'unbalanced' }}">
                                <span>Débit : {{ number_format($totalDebit, 0, ',', ' ') }} FDJ</span>
                                <span>Crédit : {{ number_format($totalCredit, 0, ',', ' ') }} FDJ</span>
                                <span>{{ $isBalanced ? '✓ Équilibrée' : '✗ Non équilibrée' }}</span>
                            </div>
                        </div>

                        @if ($entryError)
                            <div class="form-error" style="margin-top:.75rem;">{{ $entryError }}</div>
                        @endif

                        <div class="form-actions">
                            <button wire:click="toggleEntryForm" class="btn-cancel-sm">Annuler</button>
                            <button wire:click="saveEntry" class="btn-save">Enregistrer l'écriture</button>
                        </div>
                    </div>
                </div>
            @endif

            @forelse ($entries as $entry)
                <div class="entry-card">
                    <div class="entry-card-head">
                        <span class="entry-number">{{ $entry->entry_number }}</span>
                        <span class="entry-date">{{ $entry->date->format('d/m/Y') }}</span>
                        <span class="entry-desc">{{ $entry->description }}</span>
                        <span class="badge badge-ok">{{ $entry->journal->code }}</span>
                        <span class="entry-amount">{{ number_format($entry->totalDebit(), 0, ',', ' ') }} FDJ</span>
                    </div>
                    <table class="entry-lines">
                        <thead>
                            <tr><th>Compte</th><th>Libellé</th><th>Débit</th><th>Crédit</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($entry->lines as $line)
                                <tr>
                                    <td>{{ $line->account->code }} — {{ $line->account->label }}</td>
                                    <td>{{ $line->label ?: '—' }}</td>
                                    <td>{{ $line->debit > 0 ? number_format($line->debit, 0, ',', ' ') : '' }}</td>
                                    <td>{{ $line->credit > 0 ? number_format($line->credit, 0, ',', ' ') : '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @empty
                <div class="card"><div class="empty">Aucune écriture pour cette sélection.</div></div>
            @endforelse
        @endif
    @endif

    {{-- ══ DÉPENSES ══ --}}
    @if ($activeTab === 'expenses')
        <div class="toolbar-row">
            <select wire:model.live="expenseFiscalYearId" class="select-inp">
                <option value="">— Exercice —</option>
                @foreach ($fiscalYears as $fy)
                    <option value="{{ $fy->id }}">{{ $fy->label }}</option>
                @endforeach
            </select>
            @if ($expenseFiscalYearId)
                <button wire:click="toggleExpenseForm" class="btn-sm btn-add" style="margin-left:auto;">+ Nouvelle dépense</button>
            @endif
        </div>

        @if (! $expenseFiscalYearId)
            <div class="card"><div class="empty">Sélectionne un exercice comptable pour voir ou saisir des dépenses.</div></div>
        @else
            @if ($showExpenseForm)
                <div class="card">
                    <div class="card-header"><span class="card-title">Nouvelle dépense</span></div>
                    <div class="card-body">
                        <div class="form-grid-3">
                            <div class="form-field">
                                <label class="form-label">Compte de charge</label>
                                <select wire:model="expenseAccountId" class="form-select-inp">
                                    <option value="">— Choisir —</option>
                                    @foreach ($accounts->where('class', 6)->where('is_active', true) as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->label }}</option>
                                    @endforeach
                                </select>
                                @error('expenseAccountId') <span class="form-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-field">
                                <label class="form-label">Libellé</label>
                                <input wire:model="expenseLabel" type="text" class="form-input" placeholder="Ex: Loyer novembre">
                                @error('expenseLabel') <span class="form-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-field">
                                <label class="form-label">Montant (FDJ)</label>
                                <input wire:model="expenseAmount" type="number" min="1" class="form-input" placeholder="0">
                                @error('expenseAmount') <span class="form-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-field">
                                <label class="form-label">Date</label>
                                <input wire:model="expenseDate" type="date" class="form-input">
                                @error('expenseDate') <span class="form-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-field">
                                <label class="form-label">Mode de paiement</label>
                                <select wire:model="expensePaymentMethod" class="form-select-inp">
                                    <option value="cash">Espèces (caisse)</option>
                                    <option value="bank">Banque</option>
                                </select>
                            </div>
                            <div class="form-field">
                                <label class="form-label">Justificatif (optionnel)</label>
                                <input wire:model="receiptFile" type="file" accept=".jpg,.jpeg,.png,.pdf" class="form-input">
                                @error('receiptFile') <span class="form-error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        @if ($expenseError)
                            <div class="form-error" style="margin-bottom:.75rem;">{{ $expenseError }}</div>
                        @endif

                        <div class="form-actions">
                            <button wire:click="toggleExpenseForm" class="btn-cancel-sm">Annuler</button>
                            <button wire:click="saveExpense" class="btn-save">Enregistrer la dépense</button>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card">
                <table class="gl-table">
                    <thead>
                        <tr><th>Date</th><th>Compte</th><th>Libellé</th><th>Mode</th><th class="num">Montant</th><th>Justificatif</th><th>Écriture</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($expenses as $exp)
                            <tr>
                                <td>{{ $exp->date->format('d/m/Y') }}</td>
                                <td>{{ $exp->account->code }} — {{ $exp->account->label }}</td>
                                <td>{{ $exp->label }}</td>
                                <td>{{ $exp->payment_method === 'bank' ? 'Banque' : 'Espèces' }}</td>
                                <td class="num">{{ number_format($exp->amount, 0, ',', ' ') }} FDJ</td>
                                <td>
                                    @if ($exp->receipt_path)
                                        <a href="{{ $exp->receiptUrl() }}" target="_blank" style="color:var(--sidebar-soft);font-size:.8125rem;">Voir</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="mono" style="font-size:.75rem;opacity:.6;">{{ $exp->journalEntry?->entry_number ?? '—' }}</td>
                                <td>
                                    <button wire:click="deleteExpense({{ $exp->id }})" wire:confirm="Supprimer cette dépense et son écriture associée ?" class="btn-sm btn-del">Suppr.</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="empty">Aucune dépense pour cet exercice.</td></tr>
                        @endforelse
                    </tbody>
                    @if ($expenses->isNotEmpty())
                        <tfoot>
                            <tr><td colspan="4">Total</td><td class="num">{{ number_format($expenses->sum('amount'), 0, ',', ' ') }} FDJ</td><td colspan="3"></td></tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        @endif
    @endif

    {{-- ══ GRAND LIVRE ══ --}}
    @if ($activeTab === 'ledger')
        <div class="toolbar-row">
            <select wire:model.live="ledgerFiscalYearId" class="select-inp">
                <option value="">— Exercice —</option>
                @foreach ($fiscalYears as $fy)
                    <option value="{{ $fy->id }}">{{ $fy->label }}</option>
                @endforeach
            </select>
            <select wire:model.live="ledgerAccountId" class="select-inp">
                <option value="">— Compte —</option>
                @foreach ($accounts as $acc)
                    <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->label }}</option>
                @endforeach
            </select>
        </div>

        @if (! $ledgerFiscalYearId || ! $ledgerAccountId)
            <div class="card"><div class="empty">Sélectionne un exercice et un compte pour afficher son grand livre.</div></div>
        @else
            <div class="card">
                <table class="gl-table">
                    <thead>
                        <tr><th>Date</th><th>Journal</th><th>Écriture</th><th>Libellé</th><th class="num">Débit</th><th class="num">Crédit</th><th class="num">Solde</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($ledgerLines as $line)
                            <tr>
                                <td>{{ $line->journalEntry->date->format('d/m/Y') }}</td>
                                <td>{{ $line->journalEntry->journal->code }}</td>
                                <td class="mono">{{ $line->journalEntry->entry_number }}</td>
                                <td>{{ $line->label ?: $line->journalEntry->description }}</td>
                                <td class="num">{{ $line->debit > 0 ? number_format($line->debit, 0, ',', ' ') : '' }}</td>
                                <td class="num">{{ $line->credit > 0 ? number_format($line->credit, 0, ',', ' ') : '' }}</td>
                                <td class="num">{{ number_format($line->running_balance, 0, ',', ' ') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="empty">Aucun mouvement sur ce compte pour cet exercice.</td></tr>
                        @endforelse
                    </tbody>
                    @if ($ledgerLines->isNotEmpty())
                        <tfoot>
                            <tr><td colspan="6">Solde final</td><td class="num">{{ number_format($ledgerBalance, 0, ',', ' ') }} FDJ</td></tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        @endif
    @endif

    {{-- ══ BALANCE GÉNÉRALE ══ --}}
    @if ($activeTab === 'balance')
        <div class="toolbar-row">
            <select wire:model.live="balanceFiscalYearId" class="select-inp">
                <option value="">— Exercice —</option>
                @foreach ($fiscalYears as $fy)
                    <option value="{{ $fy->id }}">{{ $fy->label }}</option>
                @endforeach
            </select>
        </div>

        @if (! $balanceFiscalYearId)
            <div class="card"><div class="empty">Sélectionne un exercice pour afficher la balance générale.</div></div>
        @else
            <div class="card">
                <table class="gl-table">
                    <thead>
                        <tr><th>Compte</th><th>Libellé</th><th class="num">Total débit</th><th class="num">Total crédit</th><th class="num">Solde</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($balanceRows as $row)
                            @php $solde = ($row->total_debit ?? 0) - ($row->total_credit ?? 0); @endphp
                            <tr>
                                <td class="mono">{{ $row->code }}</td>
                                <td>{{ $row->label }}</td>
                                <td class="num">{{ number_format($row->total_debit ?? 0, 0, ',', ' ') }}</td>
                                <td class="num">{{ number_format($row->total_credit ?? 0, 0, ',', ' ') }}</td>
                                <td class="num" style="color:{{ $solde >= 0 ? '#166534' : 'var(--accent-red)' }}">{{ number_format($solde, 0, ',', ' ') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty">Aucun mouvement sur cet exercice.</td></tr>
                        @endforelse
                    </tbody>
                    @if ($balanceRows->isNotEmpty())
                        <tfoot>
                            <tr>
                                <td colspan="2">Total</td>
                                <td class="num">{{ number_format($balanceRows->sum('total_debit'), 0, ',', ' ') }}</td>
                                <td class="num">{{ number_format($balanceRows->sum('total_credit'), 0, ',', ' ') }}</td>
                                <td class="num">{{ number_format($balanceRows->sum('total_debit') - $balanceRows->sum('total_credit'), 0, ',', ' ') }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        @endif
    @endif

    {{-- ══ COMPTE DE RÉSULTAT ══ --}}
    @if ($activeTab === 'income-statement')
        <div class="toolbar-row">
            <select wire:model.live="incomeFiscalYearId" class="select-inp">
                <option value="">— Exercice —</option>
                @foreach ($fiscalYears as $fy)
                    <option value="{{ $fy->id }}">{{ $fy->label }}</option>
                @endforeach
            </select>
        </div>

        @if (! $incomeFiscalYearId)
            <div class="card"><div class="empty">Sélectionne un exercice pour afficher le compte de résultat.</div></div>
        @else
            <div class="form-grid-2">
                <div class="card">
                    <div class="card-header"><span class="card-title">Charges (classe 6)</span></div>
                    <table class="gl-table">
                        <thead><tr><th>Compte</th><th>Libellé</th><th class="num">Montant</th></tr></thead>
                        <tbody>
                            @forelse ($chargesRows as $row)
                                <tr>
                                    <td class="mono">{{ $row->code }}</td>
                                    <td>{{ $row->label }}</td>
                                    <td class="num">{{ number_format($row->total_debit ?? 0, 0, ',', ' ') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="empty">Aucune charge.</td></tr>
                            @endforelse
                        </tbody>
                        @if ($chargesRows->isNotEmpty())
                            <tfoot><tr><td colspan="2">Total charges</td><td class="num">{{ number_format($totalCharges, 0, ',', ' ') }}</td></tr></tfoot>
                        @endif
                    </table>
                </div>
                <div class="card">
                    <div class="card-header"><span class="card-title">Produits (classe 7)</span></div>
                    <table class="gl-table">
                        <thead><tr><th>Compte</th><th>Libellé</th><th class="num">Montant</th></tr></thead>
                        <tbody>
                            @forelse ($produitsRows as $row)
                                <tr>
                                    <td class="mono">{{ $row->code }}</td>
                                    <td>{{ $row->label }}</td>
                                    <td class="num">{{ number_format($row->total_credit ?? 0, 0, ',', ' ') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="empty">Aucun produit.</td></tr>
                            @endforelse
                        </tbody>
                        @if ($produitsRows->isNotEmpty())
                            <tfoot><tr><td colspan="2">Total produits</td><td class="num">{{ number_format($totalProduits, 0, ',', ' ') }}</td></tr></tfoot>
                        @endif
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;">
                    <span class="card-title">Résultat de l'exercice</span>
                    <span class="badge {{ $netResult >= 0 ? 'badge-ok' : 'badge-warn' }}" style="font-size:12px;padding:5px 12px;">
                        {{ $netResult >= 0 ? 'Bénéfice' : 'Perte' }} : {{ number_format($netResult, 0, ',', ' ') }} FDJ
                    </span>
                </div>
            </div>
        @endif
    @endif

    {{-- ══ BILAN ══ --}}
    @if ($activeTab === 'balance-sheet')
        <div class="toolbar-row">
            <select wire:model.live="bilanFiscalYearId" class="select-inp">
                <option value="">— Exercice —</option>
                @foreach ($fiscalYears as $fy)
                    <option value="{{ $fy->id }}">{{ $fy->label }}</option>
                @endforeach
            </select>
        </div>

        @if (! $bilanFiscalYearId)
            <div class="card"><div class="empty">Sélectionne un exercice pour afficher le bilan.</div></div>
        @else
            <div class="form-grid-2">
                <div class="card">
                    <div class="card-header"><span class="card-title">Actif</span></div>
                    <table class="gl-table">
                        <thead><tr><th>Compte</th><th>Libellé</th><th class="num">Montant</th></tr></thead>
                        <tbody>
                            @forelse ($actifRows as $row)
                                <tr>
                                    <td class="mono">{{ $row->code }}</td>
                                    <td>{{ $row->label }}</td>
                                    <td class="num">{{ number_format($row->solde, 0, ',', ' ') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="empty">Aucun actif.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot><tr><td colspan="2">Total actif</td><td class="num">{{ number_format($actifTotal, 0, ',', ' ') }}</td></tr></tfoot>
                    </table>
                </div>
                <div class="card">
                    <div class="card-header"><span class="card-title">Passif</span></div>
                    <table class="gl-table">
                        <thead><tr><th>Compte</th><th>Libellé</th><th class="num">Montant</th></tr></thead>
                        <tbody>
                            @forelse ($passifRows as $row)
                                <tr>
                                    <td class="mono">{{ $row->code }}</td>
                                    <td>{{ $row->label }}</td>
                                    <td class="num">{{ number_format(abs($row->solde), 0, ',', ' ') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="empty">Aucun passif.</td></tr>
                            @endforelse
                            <tr>
                                <td class="mono">—</td>
                                <td>Résultat de l'exercice</td>
                                <td class="num">{{ number_format($bilanResult, 0, ',', ' ') }}</td>
                            </tr>
                        </tbody>
                        <tfoot><tr><td colspan="2">Total passif</td><td class="num">{{ number_format($passifTotal, 0, ',', ' ') }}</td></tr></tfoot>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-body" style="display:flex;align-items:center;justify-content:center;">
                    @php $isBalanced = $actifTotal === $passifTotal; @endphp
                    <span class="badge {{ $isBalanced ? 'badge-ok' : 'badge-warn' }}" style="font-size:12px;padding:5px 12px;">
                        {{ $isBalanced ? '✓ Bilan équilibré' : '✗ Bilan non équilibré (vérifier les écritures)' }}
                    </span>
                </div>
            </div>
        @endif
    @endif

</div>
