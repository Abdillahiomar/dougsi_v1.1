<?php
use function Livewire\Volt\{state, computed, layout, usesPagination};
use App\Models\AuditLog;

layout('layouts.superadmin');
usesPagination();

state([
    'search'       => '',
    'guardFilter'  => '',
    'eventFilter'  => '',
]);

$mount = function () {
    abort_unless(auth('superadmin')->check(), 403);
};

$logs = computed(function () {
    return AuditLog::query()
        ->with('school:id,name')
        ->when($this->search, function ($q) {
            $q->where(function ($sub) {
                $sub->where('name', 'ilike', '%' . $this->search . '%')
                    ->orWhere('email', 'ilike', '%' . $this->search . '%');
            });
        })
        ->when($this->guardFilter, fn ($q) => $q->where('guard', $this->guardFilter))
        ->when($this->eventFilter, fn ($q) => $q->where('event', $this->eventFilter))
        ->orderByDesc('created_at')
        ->paginate(30);
});

?>

<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Journal d'activité</h1>
        <p class="text-sm text-slate-500">Connexions et déconnexions de tous les utilisateurs (conservées 1 mois).</p>
    </div>

    <div class="flex flex-wrap gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search"
               placeholder="Rechercher par nom ou email..."
               class="border rounded px-3 py-2 flex-1 min-w-[240px]">

        <select wire:model.live="guardFilter" class="border rounded px-3 py-2">
            <option value="">Tous les espaces</option>
            <option value="web">École (web)</option>
            <option value="superadmin">Superadmin</option>
        </select>

        <select wire:model.live="eventFilter" class="border rounded px-3 py-2">
            <option value="">Toutes les actions</option>
            <option value="login">Connexion</option>
            <option value="logout">Déconnexion</option>
        </select>
    </div>

    <div class="bg-white rounded-lg border overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left">
                <tr class="border-b">
                    <th class="px-4 py-3">Date/heure</th>
                    <th class="px-4 py-3">Utilisateur</th>
                    <th class="px-4 py-3">École</th>
                    <th class="px-4 py-3 text-center">Espace</th>
                    <th class="px-4 py-3 text-center">Action</th>
                    <th class="px-4 py-3">Adresse IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->logs as $log)
                    <tr class="border-b last:border-0 hover:bg-slate-50">
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                            {{ $log->created_at?->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $log->name ?? '—' }}</div>
                            <div class="text-xs text-slate-500">{{ $log->email }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $log->school?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span @class([
                                'px-2 py-1 rounded text-xs font-medium',
                                'bg-indigo-100 text-indigo-700' => $log->guard === 'superadmin',
                                'bg-slate-100 text-slate-600'   => $log->guard === 'web',
                            ])>
                                {{ $log->guard === 'superadmin' ? 'Superadmin' : 'École' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span @class([
                                'px-2 py-1 rounded text-xs font-medium',
                                'bg-green-100 text-green-700' => $log->event === 'login',
                                'bg-red-100 text-red-700'     => $log->event === 'logout',
                            ])>
                                {{ $log->event === 'login' ? 'Connexion' : 'Déconnexion' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $log->ip_address ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                            Aucune activité enregistrée.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $this->logs->links() }}</div>
</div>
