<?php

use App\Models\School;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.superadmin')] class extends Component
{
    public string $activeTab = 'catalog';

    // ── Catalogue des permissions ───────────────────────────────────
    public string $newPermissionName = '';
    public ?string $deleteError      = null;

    // ── Rôles par école ──────────────────────────────────────────────
    public string $selectedSchoolId = '';

    public function mount(): void
    {
        abort_unless(auth('superadmin')->check(), 403);
    }

    public function addPermission(): void
    {
        $this->validate([
            'newPermissionName' => [
                'required', 'string', 'max:100',
                'regex:/^[a-z0-9_]+(\.[a-z0-9_]+)+$/',
                'unique:permissions,name',
            ],
        ], [
            'newPermissionName.regex' => "Format attendu : module.action (minuscules, ex: accounting.manage).",
        ]);

        Permission::create([
            'name'       => $this->newPermissionName,
            'guard_name' => 'web',
        ]);

        $this->newPermissionName = '';
    }

    public function deletePermission(int $id): void
    {
        $this->deleteError = null;

        $permission = Permission::withCount('roles')->find($id);
        if (! $permission) return;

        if ($permission->roles_count > 0) {
            $this->deleteError = "« {$permission->name} » est utilisée par {$permission->roles_count} rôle(s) — impossible à supprimer tant qu'elle est attribuée.";
            return;
        }

        $permission->delete();
    }

    public function with(): array
    {
        $permissions = Permission::withCount('roles')
            ->orderBy('name')
            ->get();

        $schools = School::orderBy('name')->get();

        // Rôles "orphelins" : restes d'avant la mise en place du multi-tenant,
        // non rattachés à une école (school_id NULL). Bug connu de résolution
        // de rôle par nom — affiché ici pour visibilité, pas de correction
        // automatique dans cet écran.
        $orphanRoles = Role::whereNull('school_id')
            ->withCount('permissions')
            ->orderBy('name')
            ->get();

        $schoolRoles = collect();
        if ($this->selectedSchoolId) {
            $schoolRoles = Role::where('school_id', $this->selectedSchoolId)
                ->with('permissions')
                ->orderBy('name')
                ->get();
        }

        return compact('permissions', 'schools', 'orphanRoles', 'schoolRoles');
    }
}; ?>

<div class="p-6">
    @include('layouts.partials.finance-styles')

    <div class="page-head">
        <div>
            <div class="page-title">Permissions & Rôles</div>
            <div class="page-sub">
                Seul le superadmin peut créer de nouveaux types de permissions. Chaque école décide ensuite,
                dans ses propres paramètres (Utilisateurs & Rôles), lesquelles attribuer à ses rôles.
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div style="display:flex;gap:.5rem;margin-bottom:1.5rem;">
        <button wire:click="$set('activeTab','catalog')" class="btn {{ $activeTab === 'catalog' ? 'btn-primary' : '' }}">
            Catalogue des permissions
        </button>
        <button wire:click="$set('activeTab','schools')" class="btn {{ $activeTab === 'schools' ? 'btn-primary' : '' }}">
            Rôles par école
        </button>
    </div>

    {{-- ══ CATALOGUE ══ --}}
    @if ($activeTab === 'catalog')

        <div class="fin-card">
            <div class="fin-card-header"><span class="fin-card-title">Ajouter une permission</span></div>
            <div class="fin-card-body">
                <div style="display:flex;gap:.65rem;align-items:flex-end;flex-wrap:wrap;">
                    <div class="filter-field" style="flex:1;min-width:240px;">
                        <span class="lbl">Nom de la permission</span>
                        <input wire:model="newPermissionName" type="text" class="fin-input" placeholder="Ex: accounting.manage">
                        @error('newPermissionName') <span style="color:var(--accent-red);font-size:.75rem;">{{ $message }}</span> @enderror
                    </div>
                    <button wire:click="addPermission" class="btn btn-primary">+ Ajouter</button>
                </div>
            </div>
        </div>

        @if ($deleteError)
            <div class="fin-alert err">{{ $deleteError }}</div>
        @endif

        <div class="fin-card">
            <div class="fin-card-header">
                <span class="fin-card-title">Catalogue global</span>
                <span class="fin-card-sub">{{ $permissions->count() }} permission(s)</span>
            </div>
            <div class="fin-card-body">
                <table class="fin-table">
                    <thead>
                        <tr><th>Permission</th><th>Attribuée à</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($permissions as $perm)
                            <tr>
                                <td class="mono">{{ $perm->name }}</td>
                                <td>{{ $perm->roles_count }} rôle(s)</td>
                                <td>
                                    @if ($perm->roles_count === 0)
                                        <button wire:click="deletePermission({{ $perm->id }})"
                                                wire:confirm="Supprimer la permission « {{ $perm->name }} » ?"
                                                class="btn btn-danger btn-icon">Suppr.</button>
                                    @else
                                        <span class="lbl">En cours d'utilisation</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ══ RÔLES PAR ÉCOLE ══ --}}
    @if ($activeTab === 'schools')

        @if ($orphanRoles->isNotEmpty())
            <div class="fin-alert warn">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                <div>
                    <strong>{{ $orphanRoles->count() }} rôle(s) orphelin(s)</strong> non rattaché(s) à une école (restes d'avant la mise en place du multi-tenant) :
                    {{ $orphanRoles->pluck('name')->implode(', ') }}.
                    Tant que ce n'est pas corrigé, l'attribution d'un rôle par simple nom peut s'y accrocher par erreur.
                </div>
            </div>
        @endif

        <div class="filters">
            <div class="filter-field" style="min-width:260px;">
                <span class="lbl">École</span>
                <select wire:model.live="selectedSchoolId" class="fin-select">
                    <option value="">— Choisir une école —</option>
                    @foreach ($schools as $school)
                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if (! $selectedSchoolId)
            <div class="fin-card"><div class="fin-empty">Choisis une école pour voir ses rôles et leurs permissions.</div></div>
        @elseif ($schoolRoles->isEmpty())
            <div class="fin-card"><div class="fin-empty">Aucun rôle trouvé pour cette école.</div></div>
        @else
            @foreach ($schoolRoles as $role)
                <div class="fin-card">
                    <div class="fin-card-header">
                        <span class="fin-card-title" style="text-transform:capitalize;">{{ $role->name }}</span>
                        <span class="fin-card-sub">{{ $role->permissions->count() }} permission(s)</span>
                    </div>
                    <div class="fin-card-body">
                        @if ($role->permissions->isEmpty())
                            <span class="lbl">Aucune permission attribuée.</span>
                        @else
                            <div style="display:flex;flex-wrap:wrap;gap:.4rem;">
                                @foreach ($role->permissions->sortBy('name') as $perm)
                                    <span class="st st-active">{{ $perm->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    @endif

</div>
