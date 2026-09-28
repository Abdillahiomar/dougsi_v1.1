<?php
use function Livewire\Volt\{state, layout, rules};
use Illuminate\Support\Facades\{DB, Hash};
use Illuminate\Support\Str;
use App\Models\{School, User};

layout('layouts.superadmin');

state([
    // École
    'school_name'  => '',
    'school_email' => '',
    'school_phone' => '',
    // Premier admin
    'admin_name'     => '',
    'admin_email'    => '',
    'admin_password' => '',
]);

rules([
    'school_name'    => 'required|string|max:255',
    'school_email'   => 'required|email|max:255',
    'school_phone'   => 'required|string|max:30',
    'admin_name'     => 'required|string|max:255',
    'admin_email'    => 'required|email|max:255|unique:users,email',
    'admin_password' => 'required|string|min:8',
]);

$save = function () {
    abort_unless(auth('superadmin')->check(), 403);

    $data = $this->validate();

    DB::transaction(function () use ($data) {
        // 1. Créer l'école
        $school = School::create([
            'name'   => $data['school_name'],
            'email'  => $data['school_email'],
            'slug'   => Str::slug($data['school_name']),
            'phone'  => $data['school_phone'],
            'status' => 'active',
        ]);

        // 2. Créer les 6 rôles standards de cette école
        \App\Services\RoleTemplateService::createForSchool($school->id);

        // 3. Créer le premier admin
        $admin = User::create([
            'name'      => $data['admin_name'],
            'email'     => $data['admin_email'],
            'password'  => Hash::make($data['admin_password']),
            'school_id' => $school->id,
            'status'    => 'active',
        ]);

        // 4. Lui donner le rôle admin, dans le contexte de son école
        app(\Spatie\Permission\PermissionRegistrar::class)
            ->setPermissionsTeamId($school->id);
        $admin->assignRole('admin');

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    });

    session()->flash('status', "École « {$data['school_name']} » créée avec son administrateur.");

    return redirect()->route('superadmin.schools.index');
};

?>

@include('layouts.partials.finance-styles')

<div class="p-6" style="max-width:640px;">
    <div class="page-head">
        <div>
            <div class="page-title">Nouvelle école</div>
            <div class="page-sub">Créer une école et son premier administrateur</div>
        </div>
        <a href="{{ route('superadmin.schools.index') }}" class="btn">← Retour</a>
    </div>

    <form wire:submit="save">
        {{-- Bloc école --}}
        <div class="fin-card">
            <div class="fin-card-header">
                <span class="fin-card-title">Informations de l'école</span>
            </div>
            <div class="fin-card-body" style="display:flex;flex-direction:column;gap:1rem;">
                <div class="filter-field">
                    <span class="lbl">Nom de l'école</span>
                    <input wire:model="school_name" class="fin-input">
                    @error('school_name') <span class="fin-error">{{ $message }}</span> @enderror
                </div>

                <div style="display:grid;grid-template-columns:1fr;gap:1rem;" class="sm-grid-2">
                    <div class="filter-field">
                        <span class="lbl">Email</span>
                        <input type="email" wire:model="school_email" class="fin-input">
                        @error('school_email') <span class="fin-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="filter-field">
                        <span class="lbl">Téléphone</span>
                        <input wire:model="school_phone" placeholder="+253 ..." class="fin-input">
                        @error('school_phone') <span class="fin-error">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Bloc admin --}}
        <div class="fin-card">
            <div class="fin-card-header">
                <span class="fin-card-title">Premier administrateur</span>
                <span class="fin-card-sub">pourra se connecter et gérer l'école</span>
            </div>
            <div class="fin-card-body" style="display:flex;flex-direction:column;gap:1rem;">
                <div class="filter-field">
                    <span class="lbl">Nom complet</span>
                    <input wire:model="admin_name" class="fin-input">
                    @error('admin_name') <span class="fin-error">{{ $message }}</span> @enderror
                </div>

                <div style="display:grid;grid-template-columns:1fr;gap:1rem;" class="sm-grid-2">
                    <div class="filter-field">
                        <span class="lbl">Email de connexion</span>
                        <input type="email" wire:model="admin_email" class="fin-input">
                        @error('admin_email') <span class="fin-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="filter-field">
                        <span class="lbl">Mot de passe</span>
                        <input type="password" wire:model="admin_password" class="fin-input">
                        @error('admin_password') <span class="fin-error">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:.75rem;">
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">Créer l'école</span>
                <span wire:loading wire:target="save">Création…</span>
            </button>
            <a href="{{ route('superadmin.schools.index') }}" class="btn">Annuler</a>
        </div>
    </form>
</div>

<style>
    .fin-error { display:block; font-size:.75rem; color:var(--accent-red); margin-top:.25rem; }
    @media (min-width: 640px) {
        .sm-grid-2 { grid-template-columns: repeat(2, 1fr) !important; }
    }
</style>
