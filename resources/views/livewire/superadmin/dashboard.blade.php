<?php
// resources/views/livewire/superadmin/dashboard.blade.php
use function Livewire\Volt\{computed};
use App\Models\School;
use App\Models\User;
use function Livewire\Volt\{layout};
layout('layouts.superadmin');

$stats = computed(fn () => [
    'schools'       => School::count(),
    'active'        => School::where('is_active', true)->count(),
    'total_users'   => User::count(),
]);

?>

<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Tableau de bord Superadmin</h1>

    <div class="grid grid-cols-3 gap-4">
        <div class="rounded-lg border p-4">
            <div class="text-sm text-gray-500">Écoles</div>
            <div class="text-3xl font-bold">{{ $this->stats['schools'] }}</div>
        </div>
        <div class="rounded-lg border p-4">
            <div class="text-sm text-gray-500">Écoles actives</div>
            <div class="text-3xl font-bold">{{ $this->stats['active'] }}</div>
        </div>
        <div class="rounded-lg border p-4">
            <div class="text-sm text-gray-500">Utilisateurs</div>
            <div class="text-3xl font-bold">{{ $this->stats['total_users'] }}</div>
        </div>
    </div>
</div>