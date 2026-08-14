<?php
// resources/views/livewire/superadmin/login.blade.php
use function Livewire\Volt\{state, layout};
use Illuminate\Support\Facades\Auth;

//layout('layouts.guest'); // ou ton layout minimal

state(['email' => '', 'password' => '']);

$login = function () {
    $credentials = $this->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::guard('superadmin')->attempt($credentials)) {
        session()->regenerate();
        return redirect()->route('superadmin.dashboard');
    }

    $this->addError('email', 'Identifiants incorrects.');
};

?>

<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-md bg-white rounded-lg shadow p-8">
        <h1 class="text-2xl font-bold mb-6 text-center">Superadmin</h1>

        <form wire:submit="login" class="space-y-4">
            <div>
                <label class="block text-sm mb-1">Email</label>
                <input type="email" wire:model="email"
                       class="w-full border rounded px-3 py-2">
                @error('email')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm mb-1">Mot de passe</label>
                <input type="password" wire:model="password"
                       class="w-full border rounded px-3 py-2">
                @error('password')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit"
                    class="w-full bg-gray-900 text-white rounded py-2">
                Se connecter
            </button>
        </form>
    </div>
</div>