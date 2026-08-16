<?php
use function Livewire\Volt\{state, computed, layout, usesPagination};
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

layout('layouts.superadmin');
usesPagination();

state([
    'search'          => '',
    'schoolFilter'    => '',
    'resetUserId'     => null,
    'generatedPassword' => null,
]);

// Liste des écoles pour le filtre déroulant
$schools = computed(fn () =>
    \App\Models\School::orderBy('name')->get(['id', 'name'])
);

$users = computed(function () {
    return User::query()
        ->with(['school:id,name', 'roles:id,name'])
        ->when($this->search, function ($q) {
            $q->where(function ($sub) {
                $sub->where('name', 'ilike', '%' . $this->search . '%')
                    ->orWhere('email', 'ilike', '%' . $this->search . '%');
            });
        })
        ->when($this->schoolFilter, fn ($q) =>
            $q->where('school_id', $this->schoolFilter))
        ->orderBy('name')
        ->paginate(20);
});

// Réinitialiser le mot de passe : génère un mot de passe temporaire
$resetPassword = function ($userId) {
    $user = User::findOrFail($userId);

    $newPassword = Str::password(10); // mot de passe aléatoire
    $user->update(['password' => Hash::make($newPassword)]);

    $this->resetUserId = $userId;
    $this->generatedPassword = $newPassword;
};

$closeReset = function () {
    $this->resetUserId = null;
    $this->generatedPassword = null;
};

?>

<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Utilisateurs</h1>
        <p class="text-sm text-slate-500">Tous les utilisateurs, toutes écoles confondues.</p>
    </div>

    {{-- Filtres --}}
    <div class="flex flex-wrap gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search"
               placeholder="Rechercher par nom ou email..."
               class="border rounded px-3 py-2 flex-1 min-w-[240px]">

        <select wire:model.live="schoolFilter" class="border rounded px-3 py-2">
            <option value="">Toutes les écoles</option>
            @foreach ($this->schools as $school)
                <option value="{{ $school->id }}">{{ $school->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Bandeau mot de passe généré --}}
    @if ($generatedPassword)
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-green-800 font-medium">Mot de passe réinitialisé</p>
                    <p class="text-sm text-green-700 mt-1">
                        Nouveau mot de passe temporaire :
                        <code class="bg-white px-2 py-1 rounded font-mono border">{{ $generatedPassword }}</code>
                    </p>
                    <p class="text-xs text-green-600 mt-1">
                        Communiquez-le à l'utilisateur. Il ne sera plus affiché après fermeture.
                    </p>
                </div>
                <button wire:click="closeReset" class="text-green-700 hover:text-green-900">✕</button>
            </div>
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-lg border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left">
                <tr class="border-b">
                    <th class="px-4 py-3">Nom</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">École</th>
                    <th class="px-4 py-3">Rôle</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->users as $user)
                    <tr class="border-b last:border-0 hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->school?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @foreach ($user->roles as $role)
                                <span class="inline-block bg-slate-100 text-slate-700 rounded px-2 py-0.5 text-xs">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="resetPassword({{ $user->id }})"
                                    wire:confirm="Réinitialiser le mot de passe de {{ $user->name }} ?"
                                    class="text-orange-600 hover:text-orange-800 text-sm">
                                Réinitialiser mot de passe
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                            Aucun utilisateur trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $this->users->links() }}</div>
</div>