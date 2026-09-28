<?php

use App\Models\Invoice;
use Livewire\Volt\Component;
use Livewire\Attributes\{Computed, Layout};
use Livewire\WithPagination;

new #[Layout('layouts.superadmin')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

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

    public function markAsPaid($invoiceId)
    {
        abort_unless(auth('superadmin')->check(), 403);

        $invoice = Invoice::withoutGlobalScopes()->findOrFail($invoiceId);
        $invoice->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);
        session()->flash('status', "Facture {$invoice->invoice_number} marquée comme payée.");
    }
} ?>

@include('layouts.partials.finance-styles')

<div class="p-6">
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
                <option value="paid">Payée</option>
                <option value="cancelled">Annulée</option>
            </select>
        </div>
    </div>

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
                                    'st-overdue'   => $invoice->status === 'unpaid',
                                    'st-cancelled' => $invoice->status === 'cancelled',
                                ])>
                                    @php
                                        $labels = ['paid' => 'Payée', 'unpaid' => 'Impayée', 'cancelled' => 'Annulée'];
                                    @endphp
                                    {{ $labels[$invoice->status] ?? ucfirst($invoice->status) }}
                                </span>
                            </td>
                            <td class="num">
                                @if ($invoice->status === 'unpaid')
                                    <button wire:click="markAsPaid({{ $invoice->id }})"
                                            wire:confirm="Marquer la facture {{ $invoice->invoice_number }} comme payée ?"
                                            class="btn btn-green btn-icon" title="Marquer payée">
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