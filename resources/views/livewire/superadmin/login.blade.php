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

<div class="w-full max-w-md" x-data="{ show: false }">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-500/15 ring-1 ring-emerald-400/30 mb-4">
            <svg class="w-7 h-7 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.44 60.44 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
            </svg>
        </div>
        <h1 class="text-2xl font-semibold text-white tracking-tight">Espace Superadmin</h1>
        <p class="text-sm text-slate-400 mt-1">Dugsi — administration de la plateforme</p>
    </div>

    <div class="bg-white/5 backdrop-blur-xl ring-1 ring-white/10 rounded-2xl p-8 shadow-2xl">
        <form wire:submit="login" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-200 mb-1.5">Adresse e-mail</label>
                <input type="email" wire:model="email" autofocus
                       placeholder="admin@dugsi.dj"
                       class="w-full rounded-lg bg-white/5 border border-white/10 px-3.5 py-2.5 text-white placeholder-slate-500 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/30 focus:outline-none transition">
                @error('email')
                    <p class="text-red-400 text-sm mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-200 mb-1.5">Mot de passe</label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'" wire:model="password"
                           placeholder="••••••••"
                           class="w-full rounded-lg bg-white/5 border border-white/10 px-3.5 py-2.5 pr-11 text-white placeholder-slate-500 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-400/30 focus:outline-none transition">
                    <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-white transition">
                        <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg x-show="show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="text-red-400 text-sm mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-300 select-none">
                <input type="checkbox" wire:model="remember"
                       class="rounded border-white/20 bg-white/5 text-emerald-500 focus:ring-emerald-400/30">
                Rester connecté
            </label>

            <button type="submit"
                    class="w-full rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white font-medium py-2.5 transition focus:ring-2 focus:ring-emerald-400/50 focus:outline-none disabled:opacity-60 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled" wire:target="login">
                <span wire:loading.remove wire:target="login">Se connecter</span>
                <span wire:loading wire:target="login" class="inline-flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"/>
                    </svg>
                    Connexion…
                </span>
            </button>
        </form>
    </div>

    <p class="text-center text-xs text-slate-500 mt-6">
        Accès réservé à l'administration Dugsi
    </p>
</div>