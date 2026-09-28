<?php
use function Livewire\Volt\{state, mount, layout, computed};
use App\Models\{School, User, Student, SchoolClass, Staff};

layout('layouts.superadmin');

state(['school' => null]);

mount(function (School $school) {
    $this->school = $school;
});

// Compteurs — filtrés explicitement par school_id (le superadmin n'a pas de scope tenant)
$stats = computed(function () {
    $id = $this->school->id;

    return [
        'users'    => User::where('school_id', $id)->count(),
        'students' => Student::where('school_id', $id)->count(),
        'classes'  => SchoolClass::where('school_id', $id)->count(),
        'staff'    => Staff::where('school_id', $id)->count(),
    ];
});

// L'admin principal de l'école
$admin = computed(function () {
    return User::where('school_id', $this->school->id)
        ->whereHas('roles', fn ($q) => $q->where('name', 'admin'))
        ->first();
});

$toggleActive = function () {
    $this->school->update(['status' => $this->school->status === 'active' ? 'suspended' : 'active']);
    $this->school->refresh();
};

?>

@include('layouts.partials.finance-styles')

<div class="p-6" style="max-width:960px;">
    <div class="page-head">
        <div style="display:flex;align-items:center;gap:.75rem;">
            <a href="{{ route('superadmin.schools.index') }}" class="btn btn-icon" title="Retour">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </a>
            <div>
                <div class="page-title">{{ $school->name }}</div>
                <div class="page-sub">
                    <span class="st {{ $school->status === 'active' ? 'st-active' : 'st-suspended' }}">
                        {{ $school->status === 'active' ? 'Active' : 'Suspendue' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi">
            <div class="lbl">Élèves</div>
            <div class="kpi-val">{{ $this->stats['students'] }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Classes</div>
            <div class="kpi-val">{{ $this->stats['classes'] }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Personnel</div>
            <div class="kpi-val">{{ $this->stats['staff'] }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Utilisateurs</div>
            <div class="kpi-val">{{ $this->stats['users'] }}</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr;gap:1.25rem;" class="sm-grid-2">
        {{-- Infos école --}}
        <div class="fin-card">
            <div class="fin-card-header"><span class="fin-card-title">Informations</span></div>
            <div class="fin-card-body">
                <dl style="display:flex;flex-direction:column;gap:.75rem;font-size:.875rem;">
                    <div style="display:flex;justify-content:space-between;">
                        <dt class="lbl" style="text-transform:none;letter-spacing:normal;font-size:.8125rem;opacity:.6;">Email</dt>
                        <dd>{{ $school->email ?? '—' }}</dd>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <dt class="lbl" style="text-transform:none;letter-spacing:normal;font-size:.8125rem;opacity:.6;">Téléphone</dt>
                        <dd>{{ $school->phone ?? '—' }}</dd>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <dt class="lbl" style="text-transform:none;letter-spacing:normal;font-size:.8125rem;opacity:.6;">Créée le</dt>
                        <dd>{{ $school->created_at?->format('d/m/Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Admin principal --}}
        <div class="fin-card">
            <div class="fin-card-header"><span class="fin-card-title">Administrateur</span></div>
            <div class="fin-card-body">
                @if ($this->admin)
                    <dl style="display:flex;flex-direction:column;gap:.75rem;font-size:.875rem;">
                        <div style="display:flex;justify-content:space-between;">
                            <dt class="lbl" style="text-transform:none;letter-spacing:normal;font-size:.8125rem;opacity:.6;">Nom</dt>
                            <dd>{{ $this->admin->name }}</dd>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <dt class="lbl" style="text-transform:none;letter-spacing:normal;font-size:.8125rem;opacity:.6;">Email</dt>
                            <dd>{{ $this->admin->email }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="fin-empty" style="padding:.5rem 0;">Aucun administrateur défini.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div style="display:flex;align-items:center;gap:.75rem;margin-top:1.25rem;">
        <button wire:click="toggleActive" class="btn {{ $school->status === 'active' ? 'btn-danger' : 'btn-green' }}">
            {{ $school->status === 'active' ? "Désactiver l'école" : "Activer l'école" }}
        </button>

        @if ($this->admin)
            <form method="POST" action="{{ route('superadmin.schools.impersonate', $school) }}">
                @csrf
                <button type="submit" class="btn" style="border-color:#E8A838;color:#8A6010;">
                    🔑 Se connecter en tant que cette école
                </button>
            </form>
        @endif
    </div>
</div>

<style>
    @media (min-width: 640px) {
        .sm-grid-2 { grid-template-columns: repeat(2, 1fr) !important; }
    }
</style>
