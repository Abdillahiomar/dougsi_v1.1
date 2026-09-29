<?php
use function Livewire\Volt\{state, layout};
use Illuminate\Support\Facades\Auth;

layout('layouts.superadmin-guest');

state(['email' => '', 'password' => '', 'remember' => false]);

$login = function () {
    $this->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::guard('superadmin')->attempt(
        ['email' => $this->email, 'password' => $this->password],
        $this->remember
    )) {
        session()->regenerate();
        return redirect()->route('superadmin.dashboard');
    }

    $this->addError('email', 'Ces identifiants ne correspondent à aucun compte superadmin.');
};

?>

<div class="auth-form-wrap" x-data="{ showPw: false }">

    <div class="form-title">Espace Superadmin</div>
    <div class="form-subtitle">Accès réservé à l'administration de la plateforme Dugsi.</div>

    <form wire:submit="login">
        <div class="field">
            <label for="sa-email" class="field-label">Adresse e-mail</label>
            <input id="sa-email" type="email" wire:model="email" autofocus
                   class="field-input"
                   placeholder="admin@dugsi.dj"
                   autocomplete="username">
            @error('email')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label for="sa-password" class="field-label">Mot de passe</label>
            <div class="pw-wrap">
                <input id="sa-password" :type="showPw ? 'text' : 'password'" wire:model="password"
                       class="field-input"
                       placeholder="••••••••"
                       autocomplete="current-password"
                       style="padding-right:2.75rem;">
                <button type="button" class="pw-toggle" @click="showPw = !showPw">
                    <svg x-show="!showPw" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="showPw" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="display:none;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
            @error('password')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="auth-row">
            <label class="checkbox-wrap">
                <input type="checkbox" wire:model="remember" class="checkbox-input">
                <span class="checkbox-label">Rester connecté</span>
            </label>
        </div>

        <button type="submit" class="btn-submit" wire:loading.attr="disabled" wire:target="login">
            <span wire:loading.remove wire:target="login" style="display:flex;align-items:center;gap:.5rem;">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                Se connecter
            </span>
            <span wire:loading wire:target="login">Connexion…</span>
        </button>
    </form>
</div>