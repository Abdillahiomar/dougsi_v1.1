<?php

use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new class extends Component {
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        // Si l'utilisateur n'a pas à changer son mot de passe, on le renvoie à l'accueil
        if (! auth()->user()->must_change_password) {
            $this->redirect('/', navigate: true);
        }
    }

    public function updatePassword(): void
    {
        $this->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
            'password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $user = auth()->user();

        // Empêcher de réutiliser le mot de passe par défaut
        if (Hash::check('password', Hash::make($this->password)) || $this->password === 'password') {
            $this->addError('password', 'Choisissez un mot de passe différent de celui par défaut.');
            return;
        }

        $user->update([
            'password'             => Hash::make($this->password),
            'must_change_password' => false,
        ]);

        session()->flash('ok', 'Mot de passe mis à jour. Bienvenue !');
        $this->redirect('/', navigate: true);
    }
}; ?>

<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;background:var(--paper,#F5F3EE);">
    <div style="max-width:420px;width:100%;">
        <div style="text-align:center;margin-bottom:1.75rem;">
            <div style="width:52px;height:52px;margin:0 auto 1rem;border-radius:14px;background:rgba(42,63,126,.1);display:flex;align-items:center;justify-content:center;">
                <svg width="26" height="26" fill="none" stroke="#2A3F7E" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 style="font-family:'Fraunces',serif;font-size:1.4rem;font-weight:600;color:var(--ink,#1A1A1A);margin-bottom:.35rem;">
                Sécurisez votre compte
            </h1>
            <p style="font-size:.875rem;color:var(--ink,#1A1A1A);opacity:.55;line-height:1.5;">
                Pour votre première connexion, veuillez choisir un mot de passe personnel.
            </p>
        </div>

        <div style="background:var(--paper-raised,#FFFFFF);border:1px solid var(--line,#E5E2DA);border-radius:14px;padding:1.75rem;box-shadow:0 4px 20px rgba(0,0,0,.05);">
            <div style="display:flex;flex-direction:column;gap:.35rem;margin-bottom:1rem;">
                <label style="font-family:'JetBrains Mono',monospace;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--ink,#1A1A1A);opacity:.5;">
                    Nouveau mot de passe
                </label>
                <input wire:model="password" type="password" autofocus
                       placeholder="8 caractères minimum"
                       style="padding:.6rem .8rem;border-radius:8px;border:1px solid var(--line,#E5E2DA);background:var(--paper,#F5F3EE);font-size:.9rem;font-family:'Inter',sans-serif;color:var(--ink,#1A1A1A);outline:none;width:100%;">
                @error('password') <span style="font-size:.75rem;color:var(--accent-red,#E05C3A);">{{ $message }}</span> @enderror
            </div>

            <div style="display:flex;flex-direction:column;gap:.35rem;margin-bottom:1.5rem;">
                <label style="font-family:'JetBrains Mono',monospace;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--ink,#1A1A1A);opacity:.5;">
                    Confirmer le mot de passe
                </label>
                <input wire:model="password_confirmation" type="password"
                       placeholder="Répéter le mot de passe"
                       wire:keydown.enter="updatePassword"
                       style="padding:.6rem .8rem;border-radius:8px;border:1px solid var(--line,#E5E2DA);background:var(--paper,#F5F3EE);font-size:.9rem;font-family:'Inter',sans-serif;color:var(--ink,#1A1A1A);outline:none;width:100%;">
            </div>

            <button wire:click="updatePassword"
                    wire:loading.attr="disabled"
                    style="width:100%;padding:.7rem;border-radius:8px;background:var(--sidebar,#2A3F7E);color:#FFFFFF;font-size:.9rem;font-weight:600;font-family:'Inter',sans-serif;border:none;cursor:pointer;">
                <span wire:loading.remove wire:target="updatePassword">Enregistrer et continuer</span>
                <span wire:loading wire:target="updatePassword">Enregistrement...</span>
            </button>
        </div>

        <div style="text-align:center;margin-top:1.25rem;">
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault();document.getElementById('logout-form').submit();"
               style="font-size:.8125rem;color:var(--ink,#1A1A1A);opacity:.5;text-decoration:none;">
                Se déconnecter
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
        </div>
    </div>
</div>