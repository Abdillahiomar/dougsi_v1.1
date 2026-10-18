<?php

use App\Models\Invoice;
use App\Services\SubscriptionPaymentService;
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
                            <td class="num">
                                @if (in_array($invoice->status, ['unpaid', 'overdue']))
                                    <button wire:click="startPay({{ $invoice->id }})" class="btn btn-green btn-icon" title="Marquer payée">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    </button>
                                @else
                                    <span style="opacity:.4;">—</span>
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