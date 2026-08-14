<?php
use function Livewire\Volt\{state, mount, layout};
use App\Models\School;

layout('layouts.superadmin');

state(['school' => null]);

mount(function (School $school) {
    $this->school = $school->loadCount('users');
});

?>

<div class="p-6 max-w-3xl">


    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('superadmin.schools.index') }}" class="text-slate-500 hover:text-slate-800">← Retour</a>
        <h1 class="text-2xl font-bold">{{ $school->name }}</h1>
        <span @class([
            'px-2 py-1 rounded text-xs',
            'bg-green-100 text-green-700' => $school->is_active,
            'bg-red-100 text-red-700' => ! $school->is_active,
        ])>
            {{ $school->is_active ? 'Active' : 'Inactive' }}
        </span>

        <form method="POST" action="{{ route('superadmin.schools.impersonate', $school) }}" class="mt-4">
    @csrf
    <button type="submit"
            class="bg-amber-600 hover:bg-amber-500 text-white rounded px-4 py-2">
        🔑 Se connecter en tant que cette école
    </button>
</form>
    </div>

    <div class="bg-white rounded-lg border p-6 space-y-3">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <div class="text-sm text-slate-500">Email</div>
                <div>{{ $school->email ?? '—' }}</div>
            </div>
            <div>
                <div class="text-sm text-slate-500">Téléphone</div>
                <div>{{ $school->phone ?? '—' }}</div>
            </div>
            <div>
                <div class="text-sm text-slate-500">Utilisateurs</div>
                <div>{{ $school->users_count }}</div>
            </div>
            <div>
                <div class="text-sm text-slate-500">Créée le</div>
                <div>{{ $school->created_at?->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>
</div>