<?php

use App\Models\Invoice;
use App\Mail\InvoiceAvailableMail;
use App\Mail\PaymentReceiptMail;
use App\Services\SubscriptionPaymentService;
use Illuminate\Support\Facades\Mail;
use Livewire\Volt\Component;
use Livewire\Attributes\{Computed, Layout};
use Livewire\WithPagination;

new #[Layout('layouts.superadmin')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    public ?int $payingInvoiceId = null;
    public $payAmount = null;
    public string $payMethod = '';
    public string $payReference = '';
    public $payDate = null;

    public ?int $editingInvoiceId = null;
    public $editAmount = null;
    public $editDueAt = null;

    public function mount(): void
    {
        abort_unless(auth('superadmin')->check(), 403);
    }

    #[Computed]
    public function invoices()
    {
        return Invoice::query()
            ->with(['school:id,name', 'subscription:id,plan_id'])
            ->when($this->search, function ($q) {
                $q->where('invoice_number', 'ilike', '%' . $this->search . '%')
                  ->orWhereHas('school', fn ($s) =>
                      $s->where('name', 'ilike', '%' . $this->search . '%'));
            })
            ->when($this->statusFilter, fn ($q) =>
                $q->where('status', $this->statusFilter))
            ->orderByDesc('issued_at')
            ->paginate(20);
    }

    public function startPay(int $invoiceId): void
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $this->payingInvoiceId = $invoice->id;
        $this->payAmount       = $invoice->amount;
        $this->payMethod       = '';
        $this->payReference    = '';
        $this->payDate         = now()->format('Y-m-d');
    }

    public function cancelPay(): void
    {
        $this->reset(['payingInvoiceId', 'payAmount', 'payMethod', 'payReference', 'payDate']);
    }

    public function confirmPay(): void
    {
        abort_unless(auth('superadmin')->check(), 403);

        $this->validate([
            'payAmount'    => 'required|numeric|min:0',
            'payMethod'    => 'nullable|string|max:50',
            'payReference' => 'nullable|string|max:100',
            'payDate'      => 'required|date',
        ]);

        $invoice = Invoice::findOrFail($this->payingInvoiceId);

        app(SubscriptionPaymentService::class)->recordPayment($invoice, [
            'amount'    => $this->payAmount,
            'method'    => $this->payMethod,
            'reference' => $this->payReference,
            'paid_at'   => $this->payDate,
        ]);

        session()->flash('status', "Paiement enregistré pour la facture {$invoice->invoice_number}.");
        $this->cancelPay();
    }

    // ── Modifier une facture ──
    public function startEdit(int $invoiceId): void
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $this->editingInvoiceId = $invoice->id;
        $this->editAmount       = $invoice->amount;
        $this->editDueAt        = $invoice->due_at?->format('Y-m-d');
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingInvoiceId', 'editAmount', 'editDueAt']);
    }

    public function saveEdit(): void
    {
        abort_unless(auth('superadmin')->check(), 403);

        $this->validate([
            'editAmount' => 'required|numeric|min:0',
            'editDueAt'  => 'nullable|date',
        ]);

        $invoice = Invoice::findOrFail($this->editingInvoiceId);
        $invoice->update([
            'amount' => $this->editAmount,
            'due_at' => $this->editDueAt,
        ]);

        session()->flash('status', "Facture {$invoice->invoice_number} mise à jour.");
        $this->cancelEdit();
    }

    // ── Emails ──
    public function sendInvoiceAvailable(int $invoiceId): void
    {
        abort_unless(auth('superadmin')->check(), 403);

        $invoice = Invoice::with('school', 'subscription')->findOrFail($invoiceId);

        if (! $invoice->school?->email) {
            session()->flash('error', "Cette école n'a pas d'adresse email renseignée.");
            return;
        }

        Mail::to($invoice->school->email)->send(new InvoiceAvailableMail($invoice));

        session()->flash('status', "Email envoyé à {$invoice->school->email} pour la facture {$invoice->invoice_number}.");
    }

    public function sendReceipt(int $invoiceId): void
    {
        abort_unless(auth('superadmin')->check(), 403);

        $invoice = Invoice::with('school', 'payment')->findOrFail($invoiceId);

        if (! $invoice->payment) {
            session()->flash('error', "Aucun paiement enregistré pour cette facture.");
            return;
        }

        if (! $invoice->school?->email) {
            session()->flash('error', "Cette école n'a pas d'adresse email renseignée.");
            return;
        }

        Mail::to($invoice->school->email)->send(new PaymentReceiptMail($invoice, $invoice->payment));

        session()->flash('status', "Reçu envoyé à {$invoice->school->email}.");
    }
} ?>



<div class="p-6">
    @include('layouts.partials.finance-styles')
    <div class="page-head">
        <div>
            <div class="page-title">Factures</div>
            <div class="page-sub">Toutes les factures émises aux écoles</div>
        </div>
    </div>

    @if (session('status'))
        <div class="fin-alert ok">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="fin-alert" style="background:rgba(224,92,58,.08);border-color:rgba(224,92,58,.3);color:#C04020;">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="filters">
        <div class="filter-field" style="flex:1;min-width:240px;">
            <span class="lbl">Recherche</span>
            <input wire:model.live.debounce.300ms="search"
                   placeholder="N° facture ou école..."
                   class="fin-input">
        </div>
        <div class="filter-field">
            <span class="lbl">Statut</span>
            <select wire:model.live="statusFilter" class="fin-select">
                <option value="">Tous les statuts</option>
                <option value="unpaid">Impayée</option>
                <option value="overdue">En retard</option>
                <option value="paid">Payée</option>
                <option value="cancelled">Annulée</option>
            </select>
        </div>
    </div>

    @if ($editingInvoiceId)
        <form wire:submit="saveEdit" class="fin-card" style="padding:1rem;margin-bottom:1rem;">
            <div style="display:grid;grid-template-columns:1fr;gap:.75rem;" class="sm-grid-2">
                <div class="filter-field">
                    <span class="lbl">Montant (FDJ)</span>
                    <input type="number" wire:model="editAmount" class="fin-input">
                    @error('editAmount') <span class="fin-error">{{ $message }}</span> @enderror
                </div>
                <div class="filter-field">
                    <span class="lbl">Échéance</span>
                    <input type="date" wire:model="editDueAt" class="fin-input">
                </div>
            </div>
            <div style="display:flex;gap:.5rem;margin-top:.75rem;">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <button type="button" wire:click="cancelEdit" class="btn">Annuler</button>
            </div>
        </form>
    @endif

    @if ($payingInvoiceId)
        <form wire:submit="confirmPay" class="fin-card" style="padding:1rem;margin-bottom:1rem;">
            <div style="display:grid;grid-template-columns:1fr;gap:.75rem;" class="sm-grid-2">
                <div class="filter-field">
                    <span class="lbl">Montant reçu (FDJ)</span>
                    <input type="number" wire:model="payAmount" class="fin-input">
                    @error('payAmount') <span class="fin-error">{{ $message }}</span> @enderror
                </div>
                <div class="filter-field">
                    <span class="lbl">Méthode</span>
                    <select wire:model="payMethod" class="fin-select">
                        <option value="">— Choisir —</option>
                        <option value="d-money">D-Money</option>
                        <option value="virement">Virement</option>
                        <option value="especes">Espèces</option>
                    </select>
                </div>
                <div class="filter-field">
                    <span class="lbl">Référence (optionnel)</span>
                    <input wire:model="payReference" class="fin-input">
                </div>
                <div class="filter-field">
                    <span class="lbl">Date de paiement</span>
                    <input type="date" wire:model="payDate" class="fin-input">
                </div>
            </div>
            <div style="display:flex;gap:.5rem;margin-top:.75rem;">
                <button type="submit" class="btn btn-primary">Enregistrer le paiement</button>
                <button type="button" wire:click="cancelPay" class="btn">Annuler</button>
            </div>
        </form>
    @endif

    <div class="fin-card">
        <div class="fin-card-body">
            <table class="fin-table">
                <thead>
                    <tr>
                        <th>N° Facture</th>
                        <th>École</th>
                        <th class="num">Montant</th>
                        <th>Émise le</th>
                        <th>Échéance</th>
                        <th>Statut</th>
                        <th class="num">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->invoices as $invoice)
                        <tr @class(['row-voided' => $invoice->status === 'cancelled'])>
                            <td class="mono">{{ $invoice->invoice_number }}</td>
                            <td style="font-weight:600;">{{ $invoice->school?->name ?? '—' }}</td>
                            <td class="num mono">{{ number_format($invoice->amount, 0, ',', ' ') }} FDJ</td>
                            <td>{{ $invoice->issued_at?->format('d/m/Y') }}</td>
                            <td>{{ $invoice->due_at?->format('d/m/Y') }}</td>
                            <td>
                                <span @class([
                                    'st',
                                    'st-paid'      => $invoice->status === 'paid',
                                    'st-overdue'   => $invoice->status === 'overdue',
                                    'st-trial'     => $invoice->status === 'unpaid',
                                    'st-cancelled' => $invoice->status === 'cancelled',
                                ])>
                                    @php
                                        $labels = ['paid' => 'Payée', 'unpaid' => 'Impayée', 'overdue' => 'En retard', 'cancelled' => 'Annulée'];
                                    @endphp
                                    {{ $labels[$invoice->status] ?? ucfirst($invoice->status) }}
                                </span>
                            </td>
                            <td class="num" style="white-space:nowrap;">
                                <a href="{{ route('superadmin.invoices.pdf', $invoice) }}" target="_blank" class="btn btn-icon" title="Imprimer / PDF">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/></svg>
                                </a>

                                @if (! in_array($invoice->status, ['cancelled']))
                                    <button wire:click="startEdit({{ $invoice->id }})" class="btn btn-icon" title="Modifier">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                    </button>
                                @endif

                                @if (in_array($invoice->status, ['unpaid', 'overdue']))
                                    <button wire:click="sendInvoiceAvailable({{ $invoice->id }})"
                                            wire:confirm="Envoyer un email à {{ $invoice->school?->name }} pour l'informer que la facture {{ $invoice->invoice_number }} est disponible ?"
                                            class="btn btn-icon" title="Informer l'école par email">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                    </button>
                                    <button wire:click="startPay({{ $invoice->id }})" class="btn btn-green btn-icon" title="Marquer payée">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    </button>
                                @elseif ($invoice->status === 'paid')
                                    <button wire:click="sendReceipt({{ $invoice->id }})"
                                            class="btn btn-icon" title="Envoyer le reçu de paiement">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185zM9.75 9h.008v.008H9.75V9zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM14.25 12h.008v.008h-.008V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="fin-empty">Aucune facture trouvée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $this->invoices->links() }}</div>
</div>

<style>
    .fin-error { display:block; font-size:.75rem; color:var(--accent-red); margin-top:.25rem; }
    @media (min-width: 640px) {
        .sm-grid-2 { grid-template-columns: repeat(2, 1fr) !important; }
    }
</style>