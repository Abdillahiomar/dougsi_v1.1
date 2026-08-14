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
    $this->school->update(['is_active' => ! $this->school->is_active]);
    $this->school->refresh();
};

?>

<div class="p-6 max-w-4xl">
    {{-- En-tête --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('superadmin.schools.index') }}"
           class="text-slate-500 hover:text-slate-800">← Retour</a>
        <h1 class="text-2xl font-bold">{{ $school->name }}</h1>
        <span @class([
            'px-2 py-1 rounded text-xs font-medium',
            'bg-green-100 text-green-700' => $school->is_active,
            'bg-red-100 text-red-700' => ! $school->is_active,
        ])>
            {{ $school->is_active ? 'Active' : 'Inactive' }}
        </span>
    </div>

    {{-- Cartes de stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg border p-4">
            <div class="text-sm text-slate-500">Élèves</div>
            <div class="text-3xl font-bold text-slate-800">{{ $this->stats['students'] }}</div>
        </div>
        <div class="bg-white rounded-lg border p-4">
            <div class="text-sm text-slate-500">Classes</div>
            <div class="text-3xl font-bold text-slate-800">{{ $this->stats['classes'] }}</div>
        </div>
        <div class="bg-white rounded-lg border p-4">
            <div class="text-sm text-slate-500">Personnel</div>
            <div class="text-3xl font-bold text-slate-800">{{ $this->stats['staff'] }}</div>
        </div>
        <div class="bg-white rounded-lg border p-4">
            <div class="text-sm text-slate-500">Utilisateurs</div>
            <div class="text-3xl font-bold text-slate-800">{{ $this->stats['users'] }}</div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        {{-- Infos école --}}
        <div class="bg-white rounded-lg border p-6">
            <h2 class="font-semibold text-slate-700 mb-4">Informations</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Email</dt>
                    <dd>{{ $school->email ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Téléphone</dt>
                    <dd>{{ $school->phone ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Créée le</dt>
                    <dd>{{ $school->created_at?->format('d/m/Y') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Admin principal --}}
        <div class="bg-white rounded-lg border p-6">
            <h2 class="font-semibold text-slate-700 mb-4">Administrateur</h2>
            @if ($this->admin)
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Nom</dt>
                        <dd>{{ $this->admin->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Email</dt>
                        <dd>{{ $this->admin->email }}</dd>
                    </div>
                </dl>
            @else
                <p class="text-sm text-slate-400">Aucun administrateur défini.</p>
            @endif
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-3 mt-6">
        <button wire:click="toggleActive"
                @class([
                    'rounded px-4 py-2 text-white',
                    'bg-red-600 hover:bg-red-500' => $school->is_active,
                    'bg-green-600 hover:bg-green-500' => ! $school->is_active,
                ])>
            {{ $school->is_active ? 'Désactiver l\'école' : 'Activer l\'école' }}
        </button>

        @if ($this->admin)
            <form method="POST" action="{{ route('superadmin.schools.impersonate', $school) }}">
                @csrf
                <button type="submit"
                        class="bg-amber-600 hover:bg-amber-500 text-white rounded px-4 py-2">
                    🔑 Se connecter en tant que cette école
                </button>
            </form>
        @endif
    </div>
</div>