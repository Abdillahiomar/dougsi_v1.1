<?php
use function Livewire\Volt\{state, computed, layout, usesPagination, mount};
use App\Models\Subscription;
use App\Services\InvoiceGenerator;

layout('layouts.superadmin');
usesPagination();

state([
    'search'       => '',
    'statusFilter' => '',
]);

mount(function () {
    abort_unless(auth('superadmin')->check(), 403);
});

$generateInvoice = function ($subscriptionId) {
    abort_unless(auth('superadmin')->check(), 403);

    $sub = \App\Models\Subscription::withoutGlobalScopes()->findOrFail($subscriptionId);

    $invoice = app(InvoiceGenerator::class)->generateForSubscription($sub);

    if ($invoice) {
        session()->flash('status', "Facture {$invoice->invoice_number} générée ({$invoice->amount} FDJ).");
    } else {
        session()->flash('status', "Une facture existe déjà pour la période courante de {$sub->school?->name}.");
    }
};

$subscriptions = computed(function () {
    return Subscription::query()
        ->with(['school:id,name', 'plan:id,name'])
        ->when($this->search, function ($q) {
            $q->whereHas('school', fn ($s) =>
                $s->where('name', 'ilike', '%' . $this->search . '%'));
        })
        ->when($this->statusFilter, fn ($q) =>
            $q->where('status', $this->statusFilter))
        ->orderByDesc('created_at')
        ->paginate(20);
});

?>

@include('layouts.partials.finance-styles')

<div class="p-6">
    <div class="page-head">
        <div>
            <div class="page-title">Abonnements</div>
            <div class="page-sub">Les abonnements négociés par école</div>
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
                   placeholder="Nom de l'école..."
                   class="fin-input">
        </div>
        <div class="filter-field">
            <span class="lbl">Statut</span>
            <select wire:model.live="statusFilter" class="fin-select">
                <option value="">Tous les statuts</option>
                <option value="active">Actif</option>
                <option value="trial">Essai</option>
                <option value="suspended">Suspendu</option>
                <option value="expired">Expiré</option>
            </select>
        </div>
    </div>

    <div class="fin-card">
        <div class="fin-card-body">
            <table class="fin-table">
                <thead>
                    <tr>
                        <th>École</th>
                        <th>Plan</th>
                        <th class="num">Mensuel</th>
                        <th class="num">Remise</th>
                        <th>Périodicité</th>
                        <th class="num">Montant / cycle</th>
                        <th>Statut</th>
                        <th>Échéance</th>
                        <th class="num">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->subscriptions as $sub)
                        <tr>
                            <td style="font-weight:600;">{{ $sub->school?->name ?? '—' }}</td>
                            <td>{{ $sub->plan?->name ?? '—' }}</td>
                            <td class="num mono">{{ number_format($sub->effectiveMonthlyAmount(), 0, ',', ' ') }} FDJ</td>
                            <td class="num mono">{{ $sub->discount_percent > 0 ? $sub->discount_percent . ' %' : '—' }}</td>
                            <td>{{ $sub->cycleLabel() }}</td>
                            <td class="num mono" style="font-weight:700;">{{ number_format($sub->cycleAmount(), 0, ',', ' ') }} FDJ</td>
                            <td>
                                <span @class([
                                    'st',
                                    'st-active'    => $sub->status === 'active',
                                    'st-trial'     => $sub->status === 'trial',
                                    'st-suspended' => $sub->status === 'suspended',
                                    'st-expired'   => $sub->status === 'expired',
                                ])>
                                    {{ ucfirst($sub->status) }}
                                </span>
                            </td>
                            <td>{{ $sub->ends_at?->format('d/m/Y') ?? '—' }}</td>
                            <td class="num" style="white-space:nowrap;">
                                <a href="{{ route('superadmin.subscriptions.edit', $sub->id) }}" class="btn btn-icon" title="Modifier">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                </a>
                                <button wire:click="generateInvoice({{ $sub->id }})"
                                        wire:confirm="Générer la facture du cycle courant pour {{ $sub->school?->name }} ?"
                                        class="btn btn-icon" title="Générer facture">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </button>
                                <a href="{{ route('superadmin.invoices.index') }}" class="btn btn-icon" title="Factures">🧾</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="fin-empty">Aucun abonnement trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $this->subscriptions->links() }}</div>
</div>
