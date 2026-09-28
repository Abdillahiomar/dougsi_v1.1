<?php
use function Livewire\Volt\{state, computed, layout, usesPagination, mount};
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

mount(function () {
    abort_unless(auth('superadmin')->check(), 403);
});

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

@include('layouts.partials.finance-styles')

<div class="p-6">
    <div class="page-head">
        <div>
            <div class="page-title">Utilisateurs</div>
            <div class="page-sub">Tous les utilisateurs, toutes écoles confondues</div>
        </div>
    </div>

    @if (session('status'))
        <div class="fin-alert ok">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('status') }}
        </div>
    @endif

    {{-- Filtres --}}
    <div class="filters">
        <div class="filter-field" style="flex:1;min-width:240px;">
            <span class="lbl">Recherche</span>
            <input wire:model.live.debounce.300ms="search"
                   placeholder="Nom ou email..."
                   class="fin-input">
        </div>
        <div class="filter-field">
            <span class="lbl">École</span>
            <select wire:model.live="schoolFilter" class="fin-select">
                <option value="">Toutes les écoles</option>
                @foreach ($this->schools as $school)
                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Bandeau mot de passe généré --}}
    @if ($generatedPassword)
        <div class="fin-alert ok" style="align-items:flex-start;justify-content:space-between;">
            <div style="display:flex;gap:.65rem;align-items:flex-start;">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:16px;height:16px;flex-shrink:0;margin-top:1px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <p style="font-weight:600;">Mot de passe réinitialisé</p>
                    <p style="margin-top:.25rem;">
                        Nouveau mot de passe temporaire :
                        <code class="mono" style="background:var(--paper-raised);padding:2px 8px;border-radius:6px;border:1px solid var(--line);">{{ $generatedPassword }}</code>
                    </p>
                    <p style="margin-top:.25rem;opacity:.8;font-size:.75rem;">
                        Communiquez-le à l'utilisateur. Il ne sera plus affiché après fermeture.
                    </p>
                </div>
            </div>
            <button wire:click="closeReset" class="btn btn-icon">✕</button>
        </div>
    @endif

    {{-- Formulaire d'édition --}}
    @if ($editingUserId)
        <div class="fin-card">
            <div class="fin-card-header">
                <span class="fin-card-title">Modifier l'utilisateur</span>
                <button wire:click="cancelEdit" class="btn btn-icon" style="margin-left:auto;">✕</button>
            </div>
            <div class="fin-card-body">
                <div style="display:grid;grid-template-columns:1fr;gap:1rem;" class="sm-grid-2">
                    <div class="filter-field">
                        <span class="lbl">Nom complet</span>
                        <input wire:model="eName" type="text" class="fin-input">
                        @error('eName') <span class="fin-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="filter-field">
                        <span class="lbl">Email</span>
                        <input wire:model="eEmail" type="email" class="fin-input">
                        @error('eEmail') <span class="fin-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="filter-field">
                        <span class="lbl">Statut</span>
                        <select wire:model="eStatus" class="fin-select">
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                            <option value="suspended">Suspendu</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <span class="lbl">Rôle (dans son école)</span>
                        <select wire:model="eRole" class="fin-select">
                            <option value="">— Choisir —</option>
                            @foreach ($this->availableRoles as $role)
                                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                        @error('eRole') <span class="fin-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:.75rem;margin-top:1.25rem;">
                    <button wire:click="cancelEdit" class="btn">Annuler</button>
                    <button wire:click="saveEdit" class="btn btn-primary">Enregistrer</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Table --}}
    <div class="fin-card">
        <div class="fin-card-body">
            <table class="fin-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>École</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th class="num">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->users as $user)
                        <tr>
                            <td style="font-weight:600;">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->school?->name ?? '—' }}</td>
                            <td>
                                @foreach ($user->roles as $role)
                                    <span class="st st-trial">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <span @class([
                                    'st',
                                    'st-active'   => ($user->status ?? 'active') === 'active',
                                    'st-voided'   => ($user->status ?? 'active') === 'inactive',
                                    'st-suspended'=> ($user->status ?? 'active') === 'suspended',
                                ])>
                                    {{ match($user->status ?? 'active') { 'active'=>'Actif','inactive'=>'Inactif','suspended'=>'Suspendu',default=>'Actif' } }}
                                </span>
                            </td>
                            <td class="num" style="white-space:nowrap;">
                                <button wire:click="startEdit({{ $user->id }})" class="btn btn-icon" title="Modifier">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                </button>
                                <button wire:click="resetPassword({{ $user->id }})"
                                        wire:confirm="Réinitialiser le mot de passe de {{ $user->name }} ?"
                                        class="btn btn-icon" title="Réinitialiser le mot de passe">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.412-.083-.849.005-1.15.306L9.5 17.25l-1.5 1.5m0 0l-2.25 2.25M8 18.75l-1.5-1.5m0 0l-1.5-1.5m1.5 1.5l1.5-1.5"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="fin-empty">Aucun utilisateur trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $this->users->links() }}</div>
</div>

<style>
    .fin-error { display:block; font-size:.75rem; color:var(--accent-red); margin-top:.25rem; }
    @media (min-width: 640px) {
        .sm-grid-2 { grid-template-columns: repeat(2, 1fr) !important; }
    }
</style>
