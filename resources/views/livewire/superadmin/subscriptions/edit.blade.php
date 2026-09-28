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
        abort_unless(auth('superadmin')->check(), 403);

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

@include('layouts.partials.finance-styles')

<div class="p-6" style="max-width:640px;">
    <div class="page-head">
        <div style="display:flex;align-items:center;gap:.75rem;">
            <a href="{{ route('superadmin.subscriptions.index') }}" class="btn btn-icon" title="Retour">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </a>
            <div>
                <div class="page-title">Modifier l'abonnement</div>
                <div class="page-sub">École : {{ $subscription->school?->name }}</div>
            </div>
        </div>
    </div>

    <form wire:submit="save">
        <div class="fin-card">
            <div class="fin-card-body" style="display:flex;flex-direction:column;gap:1rem;">
                <div class="filter-field">
                    <span class="lbl">Plan de référence (optionnel)</span>
                    <select wire:model.live="plan_id" class="fin-select">
                        <option value="">— Aucun (montant 100% négocié) —</option>
                        @foreach ($this->plans as $plan)
                            <option value="{{ $plan->id }}">
                                {{ $plan->name }} ({{ number_format($plan->price, 0, ',', ' ') }} FDJ/mois)
                            </option>
                        @endforeach
                    </select>
                    <p style="font-size:.75rem;opacity:.55;margin-top:.35rem;">Sert de point de départ. Le montant ci-dessous prime s'il est renseigné.</p>
                </div>

                <div class="filter-field">
                    <span class="lbl">Montant mensuel négocié (FDJ)</span>
                    <input type="number" wire:model.live="custom_monthly_amount"
                           placeholder="Laisser vide pour utiliser le prix du plan"
                           class="fin-input">
                </div>

                <div style="display:grid;grid-template-columns:1fr;gap:1rem;" class="sm-grid-2">
                    <div class="filter-field">
                        <span class="lbl">Périodicité de paiement</span>
                        <select wire:model.live="billing_cycle" class="fin-select">
                            <option value="monthly">Mensuel</option>
                            <option value="quarterly">Trimestriel (3 mois)</option>
                            <option value="semiannual">Semestriel (6 mois)</option>
                            <option value="annual">Annuel (12 mois)</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <span class="lbl">Remise (%)</span>
                        <input type="number" step="0.01" wire:model.live="discount_percent" class="fin-input">
                    </div>
                </div>

                <div class="kpi dark">
                    <div class="lbl">Montant à facturer par cycle</div>
                    <div class="kpi-val">{{ number_format($this->cyclePreview, 0, ',', ' ') }}<span class="kpi-unit">FDJ</span></div>
                    <div class="kpi-foot">
                        {{ number_format($this->monthly, 0, ',', ' ') }} FDJ × {{ $this->months }} mois
                        @if ((float) $discount_percent > 0) − {{ $discount_percent }}% de remise @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="fin-card">
            <div class="fin-card-body" style="display:flex;flex-direction:column;gap:1rem;">
                <div class="filter-field">
                    <span class="lbl">Statut</span>
                    <select wire:model="status" class="fin-select">
                        <option value="active">Actif</option>
                        <option value="trial">Essai</option>
                        <option value="suspended">Suspendu</option>
                        <option value="expired">Expiré</option>
                    </select>
                </div>

                <div style="display:grid;grid-template-columns:1fr;gap:1rem;" class="sm-grid-2">
                    <div class="filter-field">
                        <span class="lbl">Date de début</span>
                        <input type="date" wire:model="starts_at" class="fin-input">
                    </div>
                    <div class="filter-field">
                        <span class="lbl">Date de fin</span>
                        <input type="date" wire:model="ends_at" class="fin-input">
                    </div>
                </div>

                <label style="display:flex;align-items:center;gap:.5rem;font-size:.875rem;">
                    <input type="checkbox" wire:model="auto_renew">
                    Renouvellement automatique
                </label>
            </div>
        </div>

        <div style="display:flex;gap:.75rem;">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="{{ route('superadmin.subscriptions.index') }}" class="btn">Annuler</a>
        </div>
    </form>
</div>

<style>
    @media (min-width: 640px) {
        .sm-grid-2 { grid-template-columns: repeat(2, 1fr) !important; }
    }
</style>