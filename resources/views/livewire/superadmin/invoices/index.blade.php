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
        $invoice = Invoice::withoutGlobalScopes()->findOrFail($invoiceId);
        $invoice->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);
        session()->flash('status', "Facture {$invoice->invoice_number} marquée comme payée.");
    }
} ?>

<div class="p-6">
    @if (session('status'))
        <div class="mb-4 rounded bg-green-100 text-green-800 px-4 py-2">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6">
        <h1 class="text-2xl font-bold">Factures</h1>
        <p class="text-sm text-slate-500">Toutes les factures émises aux écoles.</p>
    </div>

    <div class="flex flex-wrap gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search"
               placeholder="Rechercher (n° facture ou école)..."
               class="border rounded px-3 py-2 flex-1 min-w-[240px]">

        <select wire:model.live="statusFilter" class="border rounded px-3 py-2">
            <option value="">Tous les statuts</option>
            <option value="unpaid">Impayée</option>
            <option value="paid">Payée</option>
            <option value="cancelled">Annulée</option>
        </select>
    </div>

    <div class="bg-white rounded-lg border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left">
                <tr class="border-b">
                    <th class="px-4 py-3">N° Facture</th>
                    <th class="px-4 py-3">École</th>
                    <th class="px-4 py-3 text-right">Montant</th>
                    <th class="px-4 py-3">Émise le</th>
                    <th class="px-4 py-3">Échéance</th>
                    <th class="px-4 py-3 text-center">Statut</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->invoices as $invoice)
                    <tr class="border-b last:border-0 hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $invoice->invoice_number }}</td>
                        <td class="px-4 py-3 font-medium">{{ $invoice->school?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            {{ number_format($invoice->amount, 0, ',', ' ') }} FDJ
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $invoice->issued_at?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $invoice->due_at?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span @class([
                                'px-2 py-1 rounded text-xs font-medium',
                                'bg-green-100 text-green-700' => $invoice->status === 'paid',
                                'bg-red-100 text-red-700'     => $invoice->status === 'unpaid',
                                'bg-slate-100 text-slate-600' => $invoice->status === 'cancelled',
                            ])>
                                @php
                                    $labels = ['paid' => 'Payée', 'unpaid' => 'Impayée', 'cancelled' => 'Annulée'];
                                @endphp
                                {{ $labels[$invoice->status] ?? ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if ($invoice->status === 'unpaid')
                                <button wire:click="markAsPaid({{ $invoice->id }})"
                                        wire:confirm="Marquer la facture {{ $invoice->invoice_number }} comme payée ?"
                                        class="text-emerald-600 hover:text-emerald-800">
                                    Marquer payée
                                </button>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                            Aucune facture trouvée.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $this->invoices->links() }}</div>
</div>