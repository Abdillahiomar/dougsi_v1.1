<?php
use function Livewire\Volt\{state, rules, layout, mount, computed};
use App\Models\AuditLog;
use App\Models\SuperAdmin;
use Illuminate\Validation\Rules\Password;

layout('layouts.superadmin');

state([
    'current_password'      => '',
    'password'               => '',
    'password_confirmation'  => '',
]);

rules([
    'current_password' => ['required', 'string', 'current_password:superadmin'],
    'password'          => ['required', 'string', Password::default(), 'confirmed'],
]);

mount(function () {
    abort_unless(auth('superadmin')->check(), 403);
});

$lastLogin = computed(function () {
    $base = AuditLog::query()
        ->where('user_type', SuperAdmin::class)
        ->where('user_id', auth('superadmin')->id())
        ->where('guard', 'superadmin')
        ->where('event', 'login')
        ->orderByDesc('created_at');

    return (clone $base)->skip(1)->first() ?? $base->first();
});

$updatePassword = function () {
    $validated = $this->validate();

    auth('superadmin')->user()->update(['password' => $validated['password']]);

    $this->reset('current_password', 'password', 'password_confirmation');

    session()->flash('status', 'Mot de passe mis à jour.');
};

?>

@include('layouts.partials.finance-styles')

<div class="p-6" style="max-width:640px;">
    <div class="page-head">
        <div>
            <div class="page-title">Sécurité</div>
            <div class="page-sub">Ton compte superadmin et ton mot de passe</div>
        </div>
    </div>

    @if (session('status'))
        <div class="fin-alert ok">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('status') }}
        </div>
    @endif

    {{-- Identité --}}
    <div class="fin-card">
        <div class="fin-card-header"><span class="fin-card-title">Mon compte</span></div>
        <div class="fin-card-body">
            <dl style="display:grid;grid-template-columns:1fr;gap:.85rem;font-size:.875rem;" class="sm-grid-2">
                <div>
                    <dt class="lbl">Nom</dt>
                    <dd style="margin-top:.2rem;font-weight:600;">{{ auth('superadmin')->user()->name }}</dd>
                </div>
                <div>
                    <dt class="lbl">Email</dt>
                    <dd style="margin-top:.2rem;">{{ auth('superadmin')->user()->email }}</dd>
                </div>
                <div style="grid-column:1/-1;">
                    <dt class="lbl">Dernière connexion</dt>
                    <dd style="margin-top:.2rem;">
                        @if ($this->lastLogin)
                            {{ $this->lastLogin->created_at?->format('d/m/Y H:i') }}
                            <span class="mono" style="opacity:.55;font-size:.75rem;">· {{ $this->lastLogin->ip_address ?? '—' }}</span>
                        @else
                            Première connexion
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Mot de passe --}}
    <div class="fin-card">
        <div class="fin-card-header">
            <span class="fin-card-title">Modifier le mot de passe</span>
        </div>
        <div class="fin-card-body">
            <form wire:submit="updatePassword" style="display:flex;flex-direction:column;gap:1rem;">
                <div class="filter-field">
                    <span class="lbl">Mot de passe actuel</span>
                    <input wire:model="current_password" type="password" required autocomplete="current-password" class="fin-input">
                    @error('current_password') <span class="fin-error">{{ $message }}</span> @enderror
                </div>
                <div class="filter-field">
                    <span class="lbl">Nouveau mot de passe</span>
                    <input wire:model="password" type="password" required autocomplete="new-password" class="fin-input">
                    @error('password') <span class="fin-error">{{ $message }}</span> @enderror
                </div>
                <div class="filter-field">
                    <span class="lbl">Confirmer le mot de passe</span>
                    <input wire:model="password_confirmation" type="password" required autocomplete="new-password" class="fin-input">
                </div>

                <div>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .fin-error { display:block; font-size:.75rem; color:var(--accent-red); margin-top:.25rem; }
    @media (min-width: 640px) {
        .sm-grid-2 { grid-template-columns: repeat(2, 1fr) !important; }
    }
</style>
