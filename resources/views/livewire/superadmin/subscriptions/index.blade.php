<?php
use function Livewire\Volt\{state, computed, layout, usesPagination};
use App\Models\Subscription;

layout('layouts.superadmin');
usesPagination();

state([
    'search'       => '',
    'statusFilter' => '',
]);

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

<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Abonnements</h1>
        <p class="text-sm text-slate-500">Les abonnements négociés par école.</p>
    </div>

    <div class="flex flex-wrap gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search"
               placeholder="Rechercher une école..."
               class="border rounded px-3 py-2 flex-1 min-w-[240px]">

        <select wire:model.live="statusFilter" class="border rounded px-3 py-2">
            <option value="">Tous les statuts</option>
            <option value="active">Actif</option>
            <option value="trial">Essai</option>
            <option value="suspended">Suspendu</option>
            <option value="expired">Expiré</option>
        </select>
    </div>

    <div class="bg-white rounded-lg border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left">
                <tr class="border-b">
                    <th class="px-4 py-3">École</th>
                    <th class="px-4 py-3">Plan</th>
                    <th class="px-4 py-3 text-right">Mensuel</th>
                    <th class="px-4 py-3 text-center">Remise</th>
                    <th class="px-4 py-3">Périodicité</th>
                    <th class="px-4 py-3 text-right">Montant / cycle</th>
                    <th class="px-4 py-3 text-center">Statut</th>
                    <th class="px-4 py-3">Échéance</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->subscriptions as $sub)
                    <tr class="border-b last:border-0 hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium">{{ $sub->school?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $sub->plan?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            {{ number_format($sub->effectiveMonthlyAmount(), 0, ',', ' ') }} FDJ
                        </td>
                        <td class="px-4 py-3 text-center">
                            {{ $sub->discount_percent > 0 ? $sub->discount_percent . ' %' : '—' }}
                        </td>
                        <td class="px-4 py-3">{{ $sub->cycleLabel() }}</td>
                        <td class="px-4 py-3 text-right font-medium">
                            {{ number_format($sub->cycleAmount(), 0, ',', ' ') }} FDJ
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span @class([
                                'px-2 py-1 rounded text-xs font-medium',
                                'bg-green-100 text-green-700'  => $sub->status === 'active',
                                'bg-blue-100 text-blue-700'    => $sub->status === 'trial',
                                'bg-amber-100 text-amber-700'  => $sub->status === 'suspended',
                                'bg-red-100 text-red-700'      => $sub->status === 'expired',
                            ])>
                                {{ ucfirst($sub->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $sub->ends_at?->format('d/m/Y') ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                            Aucun abonnement trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $this->subscriptions->links() }}</div>
</div>