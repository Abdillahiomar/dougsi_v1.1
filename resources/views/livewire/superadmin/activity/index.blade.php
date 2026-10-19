<?php
use App\Models\AuditLog;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\{Computed, Layout};

new #[Layout('layouts.superadmin')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $guardFilter = '';
    public string $eventFilter = '';

    public function mount(): void
    {
        abort_unless(auth('superadmin')->check(), 403);
    }

    #[Computed]
    public function logs()
    {
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
    }
};
?>



<div class="p-6">
    @include('layouts.partials.finance-styles')
    <div class="page-head">
        <div>
            <div class="page-title">Journal d'activité</div>
            <div class="page-sub">Connexions et déconnexions de tous les utilisateurs (conservées 1 mois)</div>
        </div>
    </div>

    <div class="filters">
        <div class="filter-field" style="flex:1;min-width:240px;">
            <span class="lbl">Recherche</span>
            <input wire:model.live.debounce.300ms="search"
                   placeholder="Nom ou email..."
                   class="fin-input">
        </div>
        <div class="filter-field">
            <span class="lbl">Espace</span>
            <select wire:model.live="guardFilter" class="fin-select">
                <option value="">Tous les espaces</option>
                <option value="web">École (web)</option>
                <option value="superadmin">Superadmin</option>
            </select>
        </div>
        <div class="filter-field">
            <span class="lbl">Action</span>
            <select wire:model.live="eventFilter" class="fin-select">
                <option value="">Toutes les actions</option>
                <option value="login">Connexion</option>
                <option value="logout">Déconnexion</option>
            </select>
        </div>
    </div>

    <div class="fin-card">
        <div class="fin-card-body">
            <table class="fin-table">
                <thead>
                    <tr>
                        <th>Date/heure</th>
                        <th>Utilisateur</th>
                        <th>École</th>
                        <th>Espace</th>
                        <th>Action</th>
                        <th>Adresse IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->logs as $log)
                        <tr>
                            <td class="mono" style="white-space:nowrap;">
                                {{ $log->created_at?->format('d/m/Y H:i:s') }}
                            </td>
                            <td>
                                <div style="font-weight:600;">{{ $log->name ?? '—' }}</div>
                                <div style="font-size:.75rem;opacity:.55;">{{ $log->email }}</div>
                            </td>
                            <td>{{ $log->school?->name ?? '—' }}</td>
                            <td>
                                <span class="st {{ $log->guard === 'superadmin' ? 'st-trial' : 'st-voided' }}">
                                    {{ $log->guard === 'superadmin' ? 'Superadmin' : 'École' }}
                                </span>
                            </td>
                            <td>
                                <span class="st {{ $log->event === 'login' ? 'st-active' : 'st-suspended' }}">
                                    {{ $log->event === 'login' ? 'Connexion' : 'Déconnexion' }}
                                </span>
                            </td>
                            <td class="mono">{{ $log->ip_address ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="fin-empty">Aucune activité enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $this->logs->links() }}</div>
</div>
