<?php
use function Livewire\Volt\{state, computed, layout, usesPagination};
use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

layout('layouts.superadmin');
usesPagination();

state([
    'search'            => '',
    'schoolFilter'      => '',
    'resetUserId'       => null,
    'generatedPassword' => null,

    // ── Édition ──
    'editingUserId'  => null,
    'eName'          => '',
    'eEmail'         => '',
    'eStatus'        => 'active',
    'eRole'          => '',
    'eSchoolId'      => null,   // école du user en cours d'édition (pour le contexte team)
]);

// Écoles pour le filtre
$schools = computed(fn () =>
    School::orderBy('name')->get(['id', 'name'])
);

$users = computed(function () {
    return User::query()
        ->whereNotNull('school_id')            // exclut les superadmins (pas d'école)
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

// Rôles disponibles pour l'école du user en cours d'édition
$availableRoles = computed(function () {
    if (! $this->eSchoolId) return collect();

    return Role::where('school_id', $this->eSchoolId)
        ->orderBy('name')
        ->get(['id', 'name']);
});

// ── Réinitialiser le mot de passe ──
$resetPassword = function ($userId) {
    abort_unless(auth('superadmin')->check(), 403);

    $user = User::findOrFail($userId);
    $newPassword = Str::password(10);
    $user->update(['password' => Hash::make($newPassword)]);

    $this->resetUserId = $userId;
    $this->generatedPassword = $newPassword;
};

$closeReset = function () {
    $this->resetUserId = null;
    $this->generatedPassword = null;
};

// ── Ouvrir l'édition ──
$startEdit = function ($userId) {
    abort_unless(auth('superadmin')->check(), 403);

    $user = User::with('roles')->findOrFail($userId);

    // Définir le contexte team de l'école du user, pour lire ses rôles correctement
    app(PermissionRegistrar::class)->setPermissionsTeamId($user->school_id);
    $user->load('roles'); // recharge dans le bon contexte

    $this->editingUserId = $user->id;
    $this->eName     = $user->name;
    $this->eEmail    = $user->email;
    $this->eStatus   = $user->status ?? 'active';
    $this->eSchoolId = $user->school_id;
    $this->eRole     = $user->roles->first()?->name ?? '';
};

$cancelEdit = function () {
    $this->editingUserId = null;
    $this->reset(['eName', 'eEmail', 'eStatus', 'eRole', 'eSchoolId']);
};

// ── Sauvegarder l'édition ──
$saveEdit = function () {
    abort_unless(auth('superadmin')->check(), 403);

    $this->validate([
        'eName'   => 'required|string|max:200',
        'eEmail'  => 'required|email|unique:users,email,' . $this->editingUserId,
        'eStatus' => 'required|in:active,inactive,suspended',
        'eRole'   => 'required|string',
    ]);

    $user = User::findOrFail($this->editingUserId);

    // Mise à jour des champs simples
    $user->update([
        'name'   => $this->eName,
        'email'  => $this->eEmail,
        'status' => $this->eStatus,
    ]);

    // ── Rôle : dans le contexte team de l'école du user ──
    app(PermissionRegistrar::class)->setPermissionsTeamId($user->school_id);

    // Vérifier que le rôle existe bien dans cette école (anti-forge)
    $roleExists = Role::where('school_id', $user->school_id)
        ->where('name', $this->eRole)
        ->exists();

    if ($roleExists) {
        $user->syncRoles([$this->eRole]);
    }

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->editingUserId = null;
    $this->reset(['eName', 'eEmail', 'eStatus', 'eRole', 'eSchoolId']);
    session()->flash('status', 'Utilisateur mis à jour.');
};

?>

<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Utilisateurs</h1>
        <p class="text-sm text-slate-500">Tous les utilisateurs, toutes écoles confondues.</p>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded bg-green-100 text-green-800 px-4 py-2">{{ session('status') }}</div>
    @endif

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

    {{-- Formulaire d'édition --}}
    @if ($editingUserId)
        <div class="mb-4 rounded-lg bg-white border border-sky-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-lg">Modifier l'utilisateur</h2>
                <button wire:click="cancelEdit" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Nom complet</label>
                    <input wire:model="eName" type="text" class="border rounded px-3 py-2 w-full">
                    @error('eName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Email</label>
                    <input wire:model="eEmail" type="email" class="border rounded px-3 py-2 w-full">
                    @error('eEmail') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Statut</label>
                    <select wire:model="eStatus" class="border rounded px-3 py-2 w-full">
                        <option value="active">Actif</option>
                        <option value="inactive">Inactif</option>
                        <option value="suspended">Suspendu</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Rôle (dans son école)</label>
                    <select wire:model="eRole" class="border rounded px-3 py-2 w-full">
                        <option value="">— Choisir —</option>
                        @foreach ($this->availableRoles as $role)
                            <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                    @error('eRole') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-5">
                <button wire:click="cancelEdit" class="px-4 py-2 border rounded text-slate-600">Annuler</button>
                <button wire:click="saveEdit" class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded">
                    Enregistrer
                </button>
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
                    <th class="px-4 py-3">Statut</th>
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
                        <td class="px-4 py-3">
                            <span @class([
                                'rounded px-2 py-0.5 text-xs',
                                'bg-green-100 text-green-700' => ($user->status ?? 'active') === 'active',
                                'bg-slate-100 text-slate-500' => ($user->status ?? 'active') === 'inactive',
                                'bg-red-100 text-red-700'     => ($user->status ?? 'active') === 'suspended',
                            ])>
                                {{ match($user->status ?? 'active') { 'active'=>'Actif','inactive'=>'Inactif','suspended'=>'Suspendu',default=>'Actif' } }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <button wire:click="startEdit({{ $user->id }})"
                                    class="text-sky-600 hover:text-sky-800 text-sm">
                                Modifier
                            </button>
                            <button wire:click="resetPassword({{ $user->id }})"
                                    wire:confirm="Réinitialiser le mot de passe de {{ $user->name }} ?"
                                    class="text-orange-600 hover:text-orange-800 text-sm">
                                Réinit. mot de passe
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                            Aucun utilisateur trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $this->users->links() }}</div>
</div>