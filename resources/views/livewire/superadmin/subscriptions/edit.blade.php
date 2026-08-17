<?php

use App\Models\{Subscription, Plan};
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;

new #[Layout('layouts.superadmin')] class extends Component
{
    public ?Subscription $subscription = null;
    public $plan_id = null;
    public $custom_monthly_amount = null;
    public $discount_percent = 0;
    public string $billing_cycle = 'monthly';
    public string $status = 'active';
    public $starts_at = null;
    public $ends_at = null;
    public bool $auto_renew = false;

    public function mount(Subscription $subscription): void
    {
        $this->subscription          = $subscription;
        $this->plan_id               = $subscription->plan_id;
        $this->custom_monthly_amount = $subscription->custom_monthly_amount;
        $this->discount_percent      = $subscription->discount_percent ?? 0;
        $this->billing_cycle         = $subscription->billing_cycle ?? 'monthly';
        $this->status                = $subscription->status;
        $this->starts_at             = $subscription->starts_at?->format('Y-m-d');
        $this->ends_at               = $subscription->ends_at?->format('Y-m-d');
        $this->auto_renew            = (bool) $subscription->auto_renew;
    }

    #[Computed]
    public function plans()
    {
        return Plan::orderBy('price')->get();
    }

    #[Computed]
    public function monthly(): float
    {
        if ($this->custom_monthly_amount) {
            return (float) $this->custom_monthly_amount;
        }
        return (float) ($this->plans->firstWhere('id', $this->plan_id)?->price ?? 0);
    }

    #[Computed]
    public function months(): int
    {
        return match ($this->billing_cycle) {
            'quarterly'  => 3,
            'semiannual' => 6,
            'annual'     => 12,
            default      => 1,
        };
    }

    #[Computed]
    public function cyclePreview(): float
    {
        $base = $this->monthly * $this->months;
        $discount = $base * (((float) $this->discount_percent) / 100);
        return round($base - $discount, 2);
    }

    public function save()
    {
        $this->validate([
            'plan_id'               => 'nullable|exists:plans,id',
            'custom_monthly_amount' => 'nullable|numeric|min:0',
            'discount_percent'      => 'required|numeric|min:0|max:100',
            'billing_cycle'         => 'required|in:monthly,quarterly,semiannual,annual',
            'status'                => 'required|in:active,trial,suspended,expired',
            'starts_at'             => 'nullable|date',
            'ends_at'               => 'nullable|date|after_or_equal:starts_at',
        ]);

        $this->subscription->update([
            'plan_id'               => $this->plan_id,
            'custom_monthly_amount' => $this->custom_monthly_amount ?: null,
            'discount_percent'      => $this->discount_percent,
            'billing_cycle'         => $this->billing_cycle,
            'status'                => $this->status,
            'starts_at'             => $this->starts_at,
            'ends_at'               => $this->ends_at,
            'auto_renew'            => $this->auto_renew,
        ]);

        session()->flash('status', 'Abonnement mis à jour.');
        return redirect()->route('superadmin.subscriptions.index');
    }

    
} ?>

<div class="p-6 max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('superadmin.subscriptions.index') }}"
           class="text-slate-500 hover:text-slate-800">← Retour</a>
        <h1 class="text-2xl font-bold">Modifier l'abonnement</h1>
    </div>

    <div class="mb-4 text-sm text-slate-500">
        École : <strong class="text-slate-800">{{ $subscription->school?->name }}</strong>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="bg-white rounded-lg border p-6 space-y-4">
            <div>
                <label class="block text-sm mb-1">Plan de référence (optionnel)</label>
                <select wire:model.live="plan_id" class="w-full border rounded px-3 py-2">
                    <option value="">— Aucun (montant 100% négocié) —</option>
                    @foreach ($this->plans as $plan)
                        <option value="{{ $plan->id }}">
                            {{ $plan->name }} ({{ number_format($plan->price, 0, ',', ' ') }} FDJ/mois)
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-400 mt-1">Sert de point de départ. Le montant ci-dessous prime s'il est renseigné.</p>
            </div>

            <div>
                <label class="block text-sm mb-1">Montant mensuel négocié (FDJ)</label>
                <input type="number" wire:model.live="custom_monthly_amount"
                       placeholder="Laisser vide pour utiliser le prix du plan"
                       class="w-full border rounded px-3 py-2">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Périodicité de paiement</label>
                    <select wire:model.live="billing_cycle" class="w-full border rounded px-3 py-2">
                        <option value="monthly">Mensuel</option>
                        <option value="quarterly">Trimestriel (3 mois)</option>
                        <option value="semiannual">Semestriel (6 mois)</option>
                        <option value="annual">Annuel (12 mois)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm mb-1">Remise (%)</label>
                    <input type="number" step="0.01" wire:model.live="discount_percent"
                           class="w-full border rounded px-3 py-2">
                </div>
            </div>

            <div class="rounded-lg bg-indigo-50 border border-indigo-100 p-4">
                <div class="text-sm text-indigo-600">Montant à facturer par cycle</div>
                <div class="text-2xl font-bold text-indigo-800">
                    {{ number_format($this->cyclePreview, 0, ',', ' ') }} FDJ
                </div>
                <div class="text-xs text-indigo-500 mt-1">
                    {{ number_format($this->monthly, 0, ',', ' ') }} FDJ × {{ $this->months }} mois
                    @if ((float) $discount_percent > 0) − {{ $discount_percent }}% de remise @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border p-6 space-y-4">
            <div>
                <label class="block text-sm mb-1">Statut</label>
                <select wire:model="status" class="w-full border rounded px-3 py-2">
                    <option value="active">Actif</option>
                    <option value="trial">Essai</option>
                    <option value="suspended">Suspendu</option>
                    <option value="expired">Expiré</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Date de début</label>
                    <input type="date" wire:model="starts_at" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1">Date de fin</label>
                    <input type="date" wire:model="ends_at" class="w-full border rounded px-3 py-2">
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" wire:model="auto_renew" class="rounded">
                Renouvellement automatique
            </label>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-sky-600 hover:bg-sky-500 text-white rounded px-5 py-2.5 font-medium">
                Enregistrer
            </button>
            <a href="{{ route('superadmin.subscriptions.index') }}"
               class="px-5 py-2.5 text-slate-600 hover:text-slate-900">Annuler</a>
        </div>
    </form>
</div>