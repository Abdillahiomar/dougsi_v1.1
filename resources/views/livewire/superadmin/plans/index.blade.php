<?php

use App\Models\Plan;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\Attributes\{Computed, Layout};

new #[Layout('layouts.superadmin')] class extends Component
{
    public bool $showForm = false;
    public ?int $editingPlanId = null;

    public string $name = '';
    public string $description = '';
    public $price = '';
    public string $billing_cycle = 'monthly';
    public $max_students = '';
    public $max_staff = '';
    public string $features = '';
    public bool $is_active = true;

    public function mount(): void
    {
        abort_unless(auth('superadmin')->check(), 403);
    }

    #[Computed]
    public function plans()
    {
        return Plan::withCount('subscriptions')->orderBy('price')->get();
    }

    protected function rules(): array
    {
        return [
            'name'          => 'required|string|max:200',
            'description'   => 'nullable|string|max:1000',
            'price'         => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
            'max_students'  => 'nullable|integer|min:0',
            'max_staff'     => 'nullable|integer|min:0',
        ];
    }

    public function startCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function startEdit(int $planId): void
    {
        $plan = Plan::findOrFail($planId);

        $this->editingPlanId  = $plan->id;
        $this->name           = $plan->name;
        $this->description    = $plan->description ?? '';
        $this->price          = $plan->price;
        $this->billing_cycle  = $plan->billing_cycle;
        $this->max_students   = $plan->max_students;
        $this->max_staff      = $plan->max_staff;
        $this->features       = implode("\n", $plan->features ?? []);
        $this->is_active      = (bool) $plan->is_active;
        $this->showForm       = true;
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function save(): void
    {
        $this->validate();

        $features = collect(explode("\n", $this->features))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        $data = [
            'name'          => $this->name,
            'description'   => $this->description ?: null,
            'price'         => $this->price,
            'billing_cycle' => $this->billing_cycle,
            'max_students'  => $this->max_students ?: null,
            'max_staff'     => $this->max_staff ?: null,
            'features'      => $features,
            'is_active'     => $this->is_active,
        ];

        if ($this->editingPlanId) {
            Plan::findOrFail($this->editingPlanId)->update($data);
            session()->flash('status', 'Plan mis à jour.');
        } else {
            $data['slug'] = $this->uniqueSlug($this->name);
            Plan::create($data);
            session()->flash('status', 'Plan créé.');
        }

        $this->resetForm();
        $this->showForm = false;
        unset($this->plans);
    }

    public function toggleActive(int $planId): void
    {
        $plan = Plan::findOrFail($planId);
        $plan->update(['is_active' => ! $plan->is_active]);
        unset($this->plans);
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Plan::where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingPlanId', 'name', 'description', 'price', 'billing_cycle',
            'max_students', 'max_staff', 'features', 'is_active',
        ]);
        $this->billing_cycle = 'monthly';
        $this->is_active = true;
    }
};
?>



<div class="p-6">
    @include('layouts.partials.finance-styles')
    <div class="page-head">
        <div>
            <div class="page-title">Plans tarifaires</div>
            <div class="page-sub">Les offres proposées aux écoles</div>
        </div>
        @unless ($showForm)
            <button wire:click="startCreate" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Nouveau plan
            </button>
        @endunless
    </div>

    @if (session('status'))
        <div class="fin-alert ok">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('status') }}
        </div>
    @endif

    @if ($showForm)
        <div class="fin-card">
            <div class="fin-card-header">
                <span class="fin-card-title">{{ $editingPlanId ? 'Modifier le plan' : 'Nouveau plan' }}</span>
                <button wire:click="cancel" class="btn btn-icon" style="margin-left:auto;">✕</button>
            </div>
            <div class="fin-card-body" style="display:flex;flex-direction:column;gap:1rem;">
                <div style="display:grid;grid-template-columns:1fr;gap:1rem;" class="sm-grid-2">
                    <div class="filter-field">
                        <span class="lbl">Nom du plan</span>
                        <input wire:model="name" class="fin-input" placeholder="Ex: Pro">
                        @error('name') <span class="fin-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="filter-field">
                        <span class="lbl">Prix (FDJ)</span>
                        <input type="number" wire:model="price" class="fin-input" placeholder="Ex: 15000">
                        @error('price') <span class="fin-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="filter-field">
                    <span class="lbl">Description</span>
                    <input wire:model="description" class="fin-input" placeholder="Phrase courte décrivant le plan">
                </div>

                <div style="display:grid;grid-template-columns:1fr;gap:1rem;" class="sm-grid-2">
                    <div class="filter-field">
                        <span class="lbl">Périodicité</span>
                        <select wire:model="billing_cycle" class="fin-select">
                            <option value="monthly">Mensuel</option>
                            <option value="yearly">Annuel</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <span class="lbl">Statut</span>
                        <select wire:model="is_active" class="fin-select">
                            <option value="1">Actif</option>
                            <option value="0">Inactif (masqué pour les nouveaux abonnements)</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr;gap:1rem;" class="sm-grid-2">
                    <div class="filter-field">
                        <span class="lbl">Élèves max (optionnel)</span>
                        <input type="number" wire:model="max_students" class="fin-input" placeholder="Illimité si vide">
                    </div>
                    <div class="filter-field">
                        <span class="lbl">Personnel max (optionnel)</span>
                        <input type="number" wire:model="max_staff" class="fin-input" placeholder="Illimité si vide">
                    </div>
                </div>

                <div class="filter-field">
                    <span class="lbl">Fonctionnalités incluses (une par ligne)</span>
                    <textarea wire:model="features" rows="4" class="fin-input" placeholder="Bulletins automatiques&#10;Paiements D-Money&#10;..."></textarea>
                </div>

                <div style="display:flex;gap:.75rem;">
                    <button wire:click="save" class="btn btn-primary">Enregistrer</button>
                    <button wire:click="cancel" class="btn">Annuler</button>
                </div>
            </div>
        </div>
    @endif

    <div class="fin-card">
        <div class="fin-card-body">
            <table class="fin-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th class="num">Prix</th>
                        <th>Périodicité</th>
                        <th>Limites</th>
                        <th class="num">Abonnements</th>
                        <th>Statut</th>
                        <th class="num">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->plans as $plan)
                        <tr>
                            <td style="font-weight:600;">
                                {{ $plan->name }}
                                @if ($plan->description)
                                    <div style="font-size:.75rem;opacity:.55;font-weight:400;margin-top:2px;">{{ $plan->description }}</div>
                                @endif
                            </td>
                            <td class="num mono">{{ number_format($plan->price, 0, ',', ' ') }} FDJ</td>
                            <td>{{ $plan->billing_cycle === 'yearly' ? 'Annuel' : 'Mensuel' }}</td>
                            <td class="mono" style="font-size:.8125rem;">
                                {{ $plan->max_students ?? '∞' }} élèves · {{ $plan->max_staff ?? '∞' }} staff
                            </td>
                            <td class="num mono">{{ $plan->subscriptions_count }}</td>
                            <td>
                                <span class="st {{ $plan->is_active ? 'st-active' : 'st-suspended' }}">
                                    {{ $plan->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="num" style="white-space:nowrap;">
                                <button wire:click="startEdit({{ $plan->id }})" class="btn btn-icon" title="Modifier">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                </button>
                                <button wire:click="toggleActive({{ $plan->id }})" class="btn btn-icon" title="{{ $plan->is_active ? 'Désactiver' : 'Activer' }}">
                                    @if ($plan->is_active)
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    @else
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="fin-empty">Aucun plan créé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .fin-error { display:block; font-size:.75rem; color:var(--accent-red); margin-top:.25rem; }
    @media (min-width: 640px) {
        .sm-grid-2 { grid-template-columns: repeat(2, 1fr) !important; }
    }
</style>
