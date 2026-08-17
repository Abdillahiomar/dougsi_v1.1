<?php
use function Livewire\Volt\{state, layout, rules};
use Illuminate\Support\Facades\{DB, Hash};
use App\Models\{School, User};
use Spatie\Permission\Models\Role;

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
    $data = $this->validate();

    DB::transaction(function () use ($data) {
        // 1. Créer l'école
        $school = School::create([
            'name'      => $data['school_name'],
            'email'     => $data['school_email'],
            'slug'      => Str::slug($data['school_name']),  // ← ligne à ajouter
            'phone'     => $data['school_phone'],
            'is_active' => true,
        ]);

        // 2. Créer le premier admin, rattaché à cette école
        $admin = User::create([
            'name'      => $data['admin_name'],
            'email'     => $data['admin_email'],
            'password'  => Hash::make($data['admin_password']),
            'school_id' => $school->id,
        ]);

        // 3. Lui donner le rôle admin
        //    → Version rôle GLOBAL :
        $admin->assignRole('admin');

        //    → Si tu es en mode "teams" Spatie, remplace par :
        //    setPermissionsTeamId($school->id);
        //    $admin->assignRole('admin');
    });

    session()->flash('status', "École « {$data['school_name']} » créée avec son administrateur.");

    return redirect()->route('superadmin.schools.index');
};

?>

<div class="p-6 max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('superadmin.schools.index') }}" class="text-slate-500 hover:text-slate-800">← Retour</a>
        <h1 class="text-2xl font-bold">Nouvelle école</h1>
    </div>

    <form wire:submit="save" class="space-y-8">
        {{-- Bloc école --}}
        <div class="bg-white rounded-lg border p-6 space-y-4">
            <h2 class="font-semibold text-slate-700">Informations de l'école</h2>

            <div>
                <label class="block text-sm mb-1">Nom de l'école</label>
                <input wire:model="school_name" class="w-full border rounded px-3 py-2">
                @error('school_name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Email</label>
                    <input type="email" wire:model="school_email" class="w-full border rounded px-3 py-2">
                    @error('school_email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">Téléphone</label>
                    <input wire:model="school_phone" placeholder="+253 ..." class="w-full border rounded px-3 py-2">
                    @error('school_phone') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Bloc admin --}}
        <div class="bg-white rounded-lg border p-6 space-y-4">
            <h2 class="font-semibold text-slate-700">Premier administrateur</h2>
            <p class="text-sm text-slate-500">Ce compte pourra se connecter et gérer l'école.</p>

            <div>
                <label class="block text-sm mb-1">Nom complet</label>
                <input wire:model="admin_name" class="w-full border rounded px-3 py-2">
                @error('admin_name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Email de connexion</label>
                    <input type="email" wire:model="admin_email" class="w-full border rounded px-3 py-2">
                    @error('admin_email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm mb-1">Mot de passe</label>
                    <input type="password" wire:model="admin_password" class="w-full border rounded px-3 py-2">
                    @error('admin_password') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="bg-sky-600 hover:bg-sky-500 text-white rounded px-5 py-2.5 font-medium"
                    wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">Créer l'école</span>
                <span wire:loading wire:target="save">Création…</span>
            </button>
            <a href="{{ route('superadmin.schools.index') }}"
               class="px-5 py-2.5 text-slate-600 hover:text-slate-900">Annuler</a>
        </div>
    </form>
</div>