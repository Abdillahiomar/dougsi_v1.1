<?php
// resources/views/livewire/superadmin/schools/index.blade.php
use function Livewire\Volt\{state, computed, usesPagination};
use App\Models\School;
use function Livewire\Volt\{layout};
layout('layouts.superadmin');
usesPagination();

state(['search' => '']);

$schools = computed(function () {
    return School::withCount('users')
        ->when($this->search, fn ($q) =>
            $q->where('name', 'ilike', '%' . $this->search . '%'))
        ->orderBy('name')
        ->paginate(15);
});

$toggleActive = function ($id) {
    abort_unless(auth('superadmin')->check(), 403);

    $school = School::findOrFail($id);
    $newStatus = $school->status === 'active' ? 'suspended' : 'active';
    $school->update(['status' => $newStatus]);
};

?>

@include('layouts.partials.finance-styles')

<div class="p-6">
    @if (session('status'))
        <div class="fin-alert ok">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('status') }}
        </div>
    @endif

    <div class="page-head">
        <div>
            <div class="page-title">Écoles</div>
            <div class="page-sub">{{ $this->schools->total() }} école(s) sur la plateforme</div>
        </div>
        <a href="{{ route('superadmin.schools.create') }}" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Nouvelle école
        </a>
    </div>

    <div class="filters">
        <div class="filter-field">
            <span class="lbl">Recherche</span>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Nom de l'école..."
                   class="fin-input">
        </div>
    </div>

    <div class="fin-card">
        <div class="fin-card-body">
            <table class="fin-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th class="num">Utilisateurs</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->schools as $school)
                        <tr>
                            <td style="font-weight:600;">{{ $school->name }}</td>
                            <td class="num mono">{{ $school->users_count }}</td>
                            <td>
                                <span class="st {{ $school->status === 'active' ? 'st-active' : 'st-suspended' }}">
                                    {{ $school->status === 'active' ? 'Active' : 'Suspendue' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('superadmin.schools.show', $school) }}" class="btn btn-icon" title="Voir">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0Z"/></svg>
                                </a>
                                <button wire:click="toggleActive({{ $school->id }})" class="btn btn-icon" title="{{ $school->status === 'active' ? 'Désactiver' : 'Activer' }}">
                                    @if ($school->status === 'active')
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    @else
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @endif
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="fin-empty">Aucune école trouvée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $this->schools->links() }}</div>
</div>
