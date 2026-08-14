<?php
// resources/views/livewire/superadmin/schools/index.blade.php
use function Livewire\Volt\{state, computed};
use App\Models\School;

$search = state('');

$schools = computed(fn () =>
    School::withCount('users')
        ->when($this->search, fn ($q) =>
            $q->where('name', 'ilike', "%{$this->search}%"))
        ->orderBy('name')
        ->paginate(15)
);

$toggleActive = function (School $school) {
    $school->update(['is_active' => ! $school->is_active]);
};

?>

<div class="p-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Écoles</h1>
        <input wire:model.live.debounce.300ms="search"
               placeholder="Rechercher..."
               class="border rounded px-3 py-2">
    </div>

    <table class="w-full">
        <thead>
            <tr class="border-b text-left">
                <th class="py-2">Nom</th>
                <th>Utilisateurs</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($this->schools as $school)
                <tr class="border-b">
                    <td class="py-2">{{ $school->name }}</td>
                    <td>{{ $school->users_count }}</td>
                    <td>
                        <span @class([
                            'px-2 py-1 rounded text-xs',
                            'bg-green-100 text-green-700' => $school->is_active,
                            'bg-red-100 text-red-700' => ! $school->is_active,
                        ])>
                            {{ $school->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="space-x-2">
                        <a href="{{ route('superadmin.schools.show', $school) }}"
                           class="text-blue-600">Voir</a>
                        <button wire:click="toggleActive({{ $school->id }})"
                                class="text-orange-600">
                            {{ $school->is_active ? 'Désactiver' : 'Activer' }}
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">{{ $this->schools->links() }}</div>
</div>