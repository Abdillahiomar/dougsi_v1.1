<?php

use App\Models\StudentInvoice;
use App\Models\SchoolClass;
use App\Models\Level;
use App\Services\AcademicYearService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new class extends Component {
    use WithPagination;

    #[Url] public string $search = '';
    #[Url] public string $levelId = '';
    #[Url] public string $classId = '';
    #[Url] public string $status = '';

    // Edition
    public ?int   $editingId    = null;
    public string $editLabel    = '';
    public string $editAmountDue = '';
    public string $editAmountPaid = '';
    public string $editStatus   = '';
    public string $editIssuedAt = '';
    public string $editDueAt    = '';
    public string $editInvoiceNumber = '';

    // Vue détail
    public ?int $viewingId = null;

    // Suppression
    public ?int    $confirmDeleteId = null;
    public ?string $deleteBlockedMsg = null;

    public array $statuses = ['pending', 'partial', 'paid', 'overdue', 'cancelled'];

    public function updatedSearch()  { $this->resetPage(); }
    public function updatedLevelId() { $this->classId = ''; $this->resetPage(); }
    public function updatedClassId() { $this->resetPage(); }
    public function updatedStatus()  { $this->resetPage(); }

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    // ── Vue détail ─────────────────────────────────────────────
    public function showInvoice(int $id): void
    {
        $this->viewingId = $id;
    }

    // ── Edition ────────────────────────────────────────────────
    public function startEdit(int $id): void
    {
        $inv = StudentInvoice::withoutGlobalScopes()
            ->where('school_id', $this->schoolId())
            ->findOrFail($id);

        $this->editingId       = $inv->id;
        $this->editLabel       = (string) $inv->label;
        $this->editAmountDue   = (string) $inv->amount_due;
        $this->editAmountPaid  = (string) $inv->amount_paid;
        $this->editStatus      = (string) $inv->status;
        $this->editIssuedAt    = $inv->issued_at ? $inv->issued_at->format('Y-m-d') : '';
        $this->editDueAt       = $inv->due_at ? $inv->due_at->format('Y-m-d') : '';
        $this->editInvoiceNumber = (string) $inv->invoice_number;
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editLabel'         => 'required|string|max:150',
            'editAmountDue'     => 'required|integer|min:0',
            'editAmountPaid'    => 'required|integer|min:0',
            'editStatus'        => 'required|in:' . implode(',', $this->statuses),
            'editIssuedAt'      => 'nullable|date',
            'editDueAt'         => 'nullable|date',
            'editInvoiceNumber' => 'required|string|max:50',
        ]);

        // Garde-fou : le dû ne peut pas être inférieur au déjà payé
        if ((int) $this->editAmountDue < (int) $this->editAmountPaid) {
            $this->addError('editAmountDue',
                'Le montant dû ne peut pas être inférieur au montant déjà encaissé ('
                . number_format((int) $this->editAmountPaid, 0, ',', ' ') . ' DJF).');
            return;
        }

        StudentInvoice::withoutGlobalScopes()
            ->where('school_id', $this->schoolId())
            ->where('id', $this->editingId)
            ->update([
                'label'          => trim($this->editLabel),
                'amount_due'     => (int) $this->editAmountDue,
                'amount_paid'    => (int) $this->editAmountPaid,
                'status'         => $this->editStatus,
                'issued_at'      => $this->editIssuedAt ?: null,
                'due_at'         => $this->editDueAt ?: null,
                'invoice_number' => trim($this->editInvoiceNumber),
            ]);

        $this->editingId = null;
        session()->flash('ok', 'Facture mise à jour.');
    }

    // ── Suppression (bloquée si paiement lié) ──────────────────
    public function confirmDelete(int $id): void
    {
        $inv = StudentInvoice::withoutGlobalScopes()
            ->where('school_id', $this->schoolId())
            ->findOrFail($id);

        // Bloqué si un paiement a été alloué
        $hasReceiptLine = DB::table('payment_receipt_lines')
            ->where('student_invoice_id', $inv->id)
            ->exists();

        if ((int) $inv->amount_paid > 0 || $hasReceiptLine) {
            $this->deleteBlockedMsg =
                'Cette facture est liée à un ou plusieurs paiements. '
                . 'Elle ne peut pas être supprimée. Pour la retirer des totaux, '
                . 'passez son statut à « annulée » via Modifier.';
            $this->confirmDeleteId = null;
            return;
        }

        $this->deleteBlockedMsg = null;
        $this->confirmDeleteId  = $id;
    }

    public function deleteInvoice(): void
    {
        if (! $this->confirmDeleteId) return;

        $inv = StudentInvoice::withoutGlobalScopes()
            ->where('school_id', $this->schoolId())
            ->findOrFail($this->confirmDeleteId);

        // Re-vérification serveur (défense en profondeur)
        $hasReceiptLine = DB::table('payment_receipt_lines')
            ->where('student_invoice_id', $inv->id)
            ->exists();

        if ((int) $inv->amount_paid > 0 || $hasReceiptLine) {
            $this->deleteBlockedMsg = 'Suppression refusée : paiement lié.';
            $this->confirmDeleteId  = null;
            return;
        }

        $inv->delete();
        $this->confirmDeleteId = null;
        session()->flash('ok', 'Facture supprimée.');
    }

    public function with(): array
    {
        $schoolId = $this->schoolId();
        $year     = AcademicYearService::current();

        $levels = Level::where('school_id', $schoolId)
            ->orderBy('order')->get();

        $classes = SchoolClass::where('school_id', $schoolId)
            ->where('academic_year_id', $year?->id)
            ->when($this->levelId, fn ($q) => $q->where('level_id', $this->levelId))
            ->orderBy('name')->get();

        $invoices = StudentInvoice::withoutGlobalScopes()
            ->where('student_invoices.school_id', $schoolId)
            ->where('student_invoices.academic_year_id', $year?->id)
            ->join('student_school_years AS ssy', 'ssy.id', '=', 'student_invoices.student_school_year_id')
            ->join('students AS s', 's.id', '=', 'ssy.student_id')
            ->leftJoin('school_classes AS sc', 'sc.id', '=', 'ssy.school_class_id')
            ->leftJoin('academic_years AS ay', 'ay.id', '=', 'student_invoices.academic_year_id')
            ->whereNull('s.deleted_at')
            ->when($this->levelId, fn ($q) => $q->where('sc.level_id', $this->levelId))
            ->when($this->classId, fn ($q) => $q->where('sc.id', $this->classId))
            ->when($this->status, fn ($q) => $q->where('student_invoices.status', $this->status))
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('s.first_name', 'ilike', $term)
                        ->orWhere('s.last_name', 'ilike', $term)
                        ->orWhere('s.matricule', 'ilike', $term)
                        ->orWhere('student_invoices.invoice_number', 'ilike', $term);
                });
            })
            ->orderByDesc('student_invoices.issued_at')
            ->orderByDesc('student_invoices.id')
            ->select([
                'student_invoices.*',
                's.first_name', 's.last_name', 's.matricule',
                'sc.name AS class_name',
                'ay.label AS year_label',
            ])
            ->paginate(25);

        $viewing = $this->viewingId
            ? StudentInvoice::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->with(['studentSchoolYear.student', 'studentSchoolYear.schoolClass'])
                ->find($this->viewingId)
            : null;

        return compact('invoices', 'levels', 'classes', 'year', 'viewing');
    }
}; ?>

<div>
    @include('layouts.partials.finance-styles')

    <style>
        .inv-filters { display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:.75rem; margin-bottom:1.25rem; }
        @media (max-width:900px) { .inv-filters { grid-template-columns:1fr 1fr; } }
        @media (max-width:560px) { .inv-filters { grid-template-columns:1fr; } }
        .inv-filters input, .inv-filters select {
            padding:.5rem .75rem; border-radius:8px; border:1px solid var(--line);
            background:var(--paper-raised); font-size:.875rem; font-family:'Inter',sans-serif;
            color:var(--ink); outline:none; width:100%;
        }
        .st { display:inline-block; padding:2px 9px; border-radius:20px; font-size:11px; font-weight:600;
              font-family:'JetBrains Mono',monospace; }
        .st-paid      { background:rgba(74,222,128,.15); color:#166534; }
        .st-partial   { background:rgba(232,168,56,.15); color:#8A6010; }
        .st-overdue   { background:rgba(224,92,58,.12);  color:var(--accent-red); }
        .st-pending   { background:rgba(0,0,0,.06);      color:var(--ink); }
        .st-cancelled { background:rgba(0,0,0,.06);      color:var(--ink); opacity:.5; text-decoration:line-through; }
        .mono-num { font-family:'JetBrains Mono',monospace; font-size:11px; }
        .act-btns { display:inline-flex; gap:4px; }
        .btn-icon2 { width:30px; height:30px; border-radius:7px; border:none; cursor:pointer;
                     display:inline-flex; align-items:center; justify-content:center; transition:background .12s; }
        .btn-icon2 svg { width:15px; height:15px; }
        .ic-view { background:rgba(42,63,126,.08); color:var(--sidebar-soft); }
        .ic-view:hover { background:rgba(42,63,126,.16); }
        .ic-edit { background:rgba(232,168,56,.12); color:#8A6010; }
        .ic-edit:hover { background:rgba(232,168,56,.22); }
        .ic-del  { background:rgba(224,92,58,.08); color:var(--accent-red); }
        .ic-del:hover { background:rgba(224,92,58,.16); }
        .ov { position:fixed; inset:0; z-index:80; background:rgba(0,0,0,.4);
              display:flex; align-items:center; justify-content:center; padding:1rem; }
        .ov-panel { background:var(--paper-raised); border-radius:14px; border:1px solid var(--line);
                    padding:1.75rem; max-width:560px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,.2);
                    max-height:90vh; overflow-y:auto; }
        .ov-title { font-family:'Fraunces',serif; font-size:1.15rem; font-weight:600; margin-bottom:1.25rem; }
        .ed-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.25rem; }
        .ed-grid .full { grid-column:1 / -1; }
        .ff { display:flex; flex-direction:column; gap:.35rem; }
        .ff label { font-family:'JetBrains Mono',monospace; font-size:10px; font-weight:600;
                    text-transform:uppercase; letter-spacing:.08em; opacity:.5; }
        .ff input, .ff select { padding:.5rem .75rem; border-radius:8px; border:1px solid var(--line);
                                background:var(--paper); font-size:.875rem; font-family:'Inter',sans-serif;
                                color:var(--ink); outline:none; width:100%; }
        .ov-actions { display:flex; justify-content:flex-end; gap:.65rem; padding-top:1rem; border-top:1px solid var(--line); }
        .flash-ok { background:rgba(74,222,128,.12); border:1px solid rgba(74,222,128,.4); color:#166534;
                    padding:.6rem 1rem; border-radius:9px; font-size:.875rem; margin-bottom:1rem; }
        .kv { display:flex; justify-content:space-between; padding:.5rem 0; border-bottom:1px solid var(--line); font-size:.875rem; }
        .kv:last-child { border-bottom:none; }
        .kv-k { opacity:.55; }
        .kv-v { font-weight:600; }
    </style>

    <div class="page-head">
        <div>
            <div class="page-title">Toutes les factures</div>
            <div class="page-sub">Année {{ $year?->label ?? '—' }}</div>
        </div>
        <div style="display:flex;gap:.6rem;">
            <a href="{{ route('finances.index') }}" class="btn" wire:navigate>← Tableau de bord</a>
        </div>
    </div>

    @if (session('ok'))
        <div class="flash-ok">{{ session('ok') }}</div>
    @endif

    {{-- Filtres --}}
    <div class="inv-filters">
        <input type="text" wire:model.live.debounce.300ms="search"
               placeholder="Rechercher : nom, matricule, n° facture...">
        <select wire:model.live="levelId">
            <option value="">Tous les niveaux</option>
            @foreach ($levels as $lvl)
                <option value="{{ $lvl->id }}">{{ $lvl->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="classId">
            <option value="">Toutes les classes</option>
            @foreach ($classes as $cl)
                <option value="{{ $cl->id }}">{{ $cl->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="status">
            <option value="">Tous les statuts</option>
            <option value="pending">En attente</option>
            <option value="partial">Partiel</option>
            <option value="paid">Payé</option>
            <option value="overdue">En retard</option>
            <option value="cancelled">Annulé</option>
        </select>
    </div>

    {{-- Tableau --}}
    <div class="fin-card">
        <div class="fin-card-body" style="padding-top:.5rem;padding-bottom:.5rem;">
            <table class="fin-table">
                <thead>
                    <tr>
                        <th>N° facture</th><th>Élève</th><th>Classe</th><th>Année</th><th>Libellé</th>
                        <th class="num">Dû</th><th class="num">Payé</th><th class="num">Reste</th>
                        <th>Statut</th><th>Échéance</th><th class="num">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $inv)
                        @php $reste = (int) $inv->amount_due - (int) $inv->amount_paid; @endphp
                        <tr>
                            <td class="mono-num">{{ $inv->invoice_number }}</td>
                            <td>
                                <div style="font-weight:600;">{{ $inv->first_name }} {{ $inv->last_name }}</div>
                                <div class="lbl">{{ $inv->matricule }}</div>
                            </td>
                            <td style="opacity:.65;">{{ $inv->class_name ?? '—' }}</td>
                            <td class="lbl">{{ $inv->year_label ?? '—' }}</td>
                            <td>{{ $inv->label }}</td>
                            <td class="num mono">{{ number_format((int)$inv->amount_due, 0, ',', ' ') }}</td>
                            <td class="num mono" style="color:#166534;">{{ number_format((int)$inv->amount_paid, 0, ',', ' ') }}</td>
                            <td class="num mono" style="color:{{ $reste > 0 ? 'var(--accent-red)' : 'inherit' }};">
                                {{ number_format($reste, 0, ',', ' ') }}
                            </td>
                            <td><span class="st st-{{ $inv->status }}">{{ $inv->status }}</span></td>
                            <td class="lbl">{{ $inv->due_at ? \Carbon\Carbon::parse($inv->due_at)->format('d/m/Y') : '—' }}</td>
                            <td class="num">
                                <div class="act-btns">
                                    <button wire:click="showInvoice({{ $inv->id }})" class="btn-icon2 ic-view" title="Voir">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    <button wire:click="startEdit({{ $inv->id }})" class="btn-icon2 ic-edit" title="Modifier">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $inv->id }})" class="btn-icon2 ic-del" title="Supprimer">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="fin-empty">Aucune facture ne correspond à ces critères.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:1rem;">{{ $invoices->links() }}</div>

    {{-- Modal vue détail --}}
    @if ($viewingId && $viewing)
        <div class="ov">
            <div class="ov-panel">
                <div class="ov-title">Facture {{ $viewing->invoice_number }}</div>
                <div class="kv"><span class="kv-k">Élève</span><span class="kv-v">{{ $viewing->studentSchoolYear?->student?->first_name }} {{ $viewing->studentSchoolYear?->student?->last_name }}</span></div>
                <div class="kv"><span class="kv-k">Classe</span><span class="kv-v">{{ $viewing->studentSchoolYear?->schoolClass?->name ?? '—' }}</span></div>
                <div class="kv"><span class="kv-k">Libellé</span><span class="kv-v">{{ $viewing->label }}</span></div>
                <div class="kv"><span class="kv-k">Montant dû</span><span class="kv-v">{{ number_format((int)$viewing->amount_due, 0, ',', ' ') }} DJF</span></div>
                <div class="kv"><span class="kv-k">Montant payé</span><span class="kv-v" style="color:#166534;">{{ number_format((int)$viewing->amount_paid, 0, ',', ' ') }} DJF</span></div>
                <div class="kv"><span class="kv-k">Reste dû</span><span class="kv-v" style="color:var(--accent-red);">{{ number_format((int)$viewing->amount_due - (int)$viewing->amount_paid, 0, ',', ' ') }} DJF</span></div>
                <div class="kv"><span class="kv-k">Statut</span><span class="kv-v"><span class="st st-{{ $viewing->status }}">{{ $viewing->status }}</span></span></div>
                <div class="kv"><span class="kv-k">Émise le</span><span class="kv-v">{{ $viewing->issued_at ? $viewing->issued_at->format('d/m/Y') : '—' }}</span></div>
                <div class="kv"><span class="kv-k">Échéance</span><span class="kv-v">{{ $viewing->due_at ? $viewing->due_at->format('d/m/Y') : '—' }}</span></div>
                <div class="ov-actions" style="margin-top:1.25rem;">
                    <button wire:click="$set('viewingId', null)" class="btn">Fermer</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal édition --}}
    @if ($editingId)
        <div class="ov">
            <div class="ov-panel">
                <div class="ov-title">Modifier la facture</div>
                <div class="ed-grid">
                    <div class="ff full">
                        <label>Libellé</label>
                        <input type="text" wire:model="editLabel">
                        @error('editLabel') <span style="color:var(--accent-red);font-size:.75rem;">{{ $message }}</span> @enderror
                    </div>
                    <div class="ff">
                        <label>N° de facture</label>
                        <input type="text" wire:model="editInvoiceNumber">
                        @error('editInvoiceNumber') <span style="color:var(--accent-red);font-size:.75rem;">{{ $message }}</span> @enderror
                    </div>
                    <div class="ff">
                        <label>Statut</label>
                        <select wire:model="editStatus">
                            <option value="pending">En attente</option>
                            <option value="partial">Partiel</option>
                            <option value="paid">Payé</option>
                            <option value="overdue">En retard</option>
                            <option value="cancelled">Annulé</option>
                        </select>
                        @error('editStatus') <span style="color:var(--accent-red);font-size:.75rem;">{{ $message }}</span> @enderror
                    </div>
                    <div class="ff">
                        <label>Montant dû (DJF)</label>
                        <input type="number" wire:model="editAmountDue" min="0">
                        @error('editAmountDue') <span style="color:var(--accent-red);font-size:.75rem;">{{ $message }}</span> @enderror
                    </div>
                    <div class="ff">
                        <label>Montant payé (DJF)</label>
                        <input type="number" wire:model="editAmountPaid" min="0">
                        @error('editAmountPaid') <span style="color:var(--accent-red);font-size:.75rem;">{{ $message }}</span> @enderror
                    </div>
                    <div class="ff">
                        <label>Émise le</label>
                        <input type="date" wire:model="editIssuedAt">
                        @error('editIssuedAt') <span style="color:var(--accent-red);font-size:.75rem;">{{ $message }}</span> @enderror
                    </div>
                    <div class="ff">
                        <label>Échéance</label>
                        <input type="date" wire:model="editDueAt">
                        @error('editDueAt') <span style="color:var(--accent-red);font-size:.75rem;">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="ov-actions">
                    <button wire:click="$set('editingId', null)" class="btn">Annuler</button>
                    <button wire:click="saveEdit" class="btn btn-green">Enregistrer</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal suppression / blocage --}}
    @if ($confirmDeleteId || $deleteBlockedMsg)
        <div class="ov">
            <div class="ov-panel" style="max-width:420px;">
                @if ($deleteBlockedMsg)
                    <div class="ov-title">Suppression impossible</div>
                    <p style="font-size:.875rem;opacity:.7;line-height:1.5;margin-bottom:1.25rem;">{{ $deleteBlockedMsg }}</p>
                    <div class="ov-actions">
                        <button wire:click="$set('deleteBlockedMsg', null)" class="btn">Compris</button>
                    </div>
                @else
                    <div class="ov-title">Supprimer cette facture ?</div>
                    <p style="font-size:.875rem;opacity:.7;line-height:1.5;margin-bottom:1.25rem;">
                        Cette facture n'a reçu aucun paiement. La suppression est définitive.
                    </p>
                    <div class="ov-actions">
                        <button wire:click="$set('confirmDeleteId', null)" class="btn">Annuler</button>
                        <button wire:click="deleteInvoice" class="btn" style="background:var(--accent-red);color:#fff;">Oui, supprimer</button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>