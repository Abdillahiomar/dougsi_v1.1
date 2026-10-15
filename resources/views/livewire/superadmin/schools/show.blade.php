<?php
use App\Models\{School, User, Student, SchoolClass, Staff};
use App\Services\RoleTemplateService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Livewire\Volt\Component;
use Livewire\Attributes\{Computed, Layout};

new #[Layout('layouts.superadmin')] class extends Component
{
    public School $school;
    public bool $showAddAdmin = false;
    public string $newAdminName = '';
    public string $newAdminEmail = '';
    public ?string $generatedAdminPassword = null;

    public function mount(School $school): void
    {
        $this->school = $school;
    }

    // Compteurs — filtrés explicitement par school_id (le superadmin n'a pas de scope tenant)
    #[Computed]
    public function stats(): array
    {
        $id = $this->school->id;

        return [
            'users'    => User::where('school_id', $id)->count(),
            'students' => Student::where('school_id', $id)->count(),
            'classes'  => SchoolClass::where('school_id', $id)->count(),
            'staff'    => Staff::where('school_id', $id)->count(),
        ];
    }

    // L'admin principal de l'école
    #[Computed]
    public function admin(): ?User
    {
        return User::where('school_id', $this->school->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'admin'))
            ->first();
    }

    public function toggleActive(): void
    {
        $this->school->update(['status' => $this->school->status === 'active' ? 'suspended' : 'active']);
        $this->school->refresh();
    }

    public function toggleAddAdmin(): void
    {
        $this->showAddAdmin = ! $this->showAddAdmin;
        $this->reset(['newAdminName', 'newAdminEmail']);
    }

    public function createAdmin(): void
    {
        abort_unless(auth('superadmin')->check(), 403);

        $this->validate([
            'newAdminName'  => 'required|string|max:200',
            'newAdminEmail' => 'required|email|max:200|unique:users,email',
        ]);

        $password = Str::password(10);

        DB::transaction(function () use ($password) {
            $admin = User::create([
                'name'      => $this->newAdminName,
                'email'     => $this->newAdminEmail,
                'password'  => Hash::make($password),
                'school_id' => $this->school->id,
                'status'    => 'active',
            ]);

            // S'assure que les rôles standards existent pour cette école (réparation
            // idempotente — utile si l'école a été créée avant la mise en place des
            // rôles, ou si son rôle "admin" a été supprimé par erreur).
            RoleTemplateService::createForSchool($this->school->id);

            app(PermissionRegistrar::class)->setPermissionsTeamId($this->school->id);
            $admin->assignRole('admin');
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });

        $this->generatedAdminPassword = $password;
        $this->reset(['newAdminName', 'newAdminEmail', 'showAddAdmin']);

        unset($this->admin);
    }
};
?>



<div class="p-6" style="max-width:960px;">
    @include('layouts.partials.finance-styles')
    <div class="page-head">
        <div style="display:flex;align-items:center;gap:.75rem;">
            <a href="{{ route('superadmin.schools.index') }}" class="btn btn-icon" title="Retour">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </a>
            <div>
                <div class="page-title">{{ $school->name }}</div>
                <div class="page-sub">
                    <span class="st {{ $school->status === 'active' ? 'st-active' : 'st-suspended' }}">
                        {{ $school->status === 'active' ? 'Active' : 'Suspendue' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi">
            <div class="lbl">Élèves</div>
            <div class="kpi-val">{{ $this->stats['students'] }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Classes</div>
            <div class="kpi-val">{{ $this->stats['classes'] }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Personnel</div>
            <div class="kpi-val">{{ $this->stats['staff'] }}</div>
        </div>
        <div class="kpi">
            <div class="lbl">Utilisateurs</div>
            <div class="kpi-val">{{ $this->stats['users'] }}</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr;gap:1.25rem;" class="sm-grid-2">
        {{-- Infos école --}}
        <div class="fin-card">
            <div class="fin-card-header"><span class="fin-card-title">Informations</span></div>
            <div class="fin-card-body">
                <dl style="display:flex;flex-direction:column;gap:.75rem;font-size:.875rem;">
                    <div style="display:flex;justify-content:space-between;">
                        <dt class="lbl" style="text-transform:none;letter-spacing:normal;font-size:.8125rem;opacity:.6;">Email</dt>
                        <dd>{{ $school->email ?? '—' }}</dd>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <dt class="lbl" style="text-transform:none;letter-spacing:normal;font-size:.8125rem;opacity:.6;">Téléphone</dt>
                        <dd>{{ $school->phone ?? '—' }}</dd>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <dt class="lbl" style="text-transform:none;letter-spacing:normal;font-size:.8125rem;opacity:.6;">Créée le</dt>
                        <dd>{{ $school->created_at?->format('d/m/Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Admin principal --}}
        <div class="fin-card">
            <div class="fin-card-header"><span class="fin-card-title">Administrateur</span></div>
            <div class="fin-card-body">
                @if ($generatedAdminPassword)
                    <div class="fin-alert ok" style="align-items:flex-start;margin-bottom:1rem;">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:16px;height:16px;flex-shrink:0;margin-top:1px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p style="font-weight:600;">Administrateur créé.</p>
                            <p style="margin-top:.25rem;">
                                Mot de passe temporaire :
                                <code class="mono" style="background:var(--paper-raised);padding:2px 8px;border-radius:6px;border:1px solid var(--line);">{{ $generatedAdminPassword }}</code>
                            </p>
                            <p style="margin-top:.25rem;opacity:.8;font-size:.75rem;">
                                Communiquez-le à l'école. Il ne sera plus affiché après fermeture.
                            </p>
                        </div>
                    </div>
                @endif

                @if ($this->admin)
                    <dl style="display:flex;flex-direction:column;gap:.75rem;font-size:.875rem;">
                        <div style="display:flex;justify-content:space-between;">
                            <dt class="lbl" style="text-transform:none;letter-spacing:normal;font-size:.8125rem;opacity:.6;">Nom</dt>
                            <dd>{{ $this->admin->name }}</dd>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <dt class="lbl" style="text-transform:none;letter-spacing:normal;font-size:.8125rem;opacity:.6;">Email</dt>
                            <dd>{{ $this->admin->email }}</dd>
                        </div>
                    </dl>
                @elseif ($showAddAdmin)
                    <form wire:submit="createAdmin" style="display:flex;flex-direction:column;gap:.75rem;">
                        <div class="filter-field">
                            <span class="lbl">Nom complet</span>
                            <input wire:model="newAdminName" type="text" class="fin-input" placeholder="Ex: Amina Hassan">
                            @error('newAdminName') <span class="fin-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="filter-field">
                            <span class="lbl">Email de connexion</span>
                            <input wire:model="newAdminEmail" type="email" class="fin-input" placeholder="admin@ecole.dj">
                            @error('newAdminEmail') <span class="fin-error">{{ $message }}</span> @enderror
                        </div>
                        <p class="form-hint" style="font-size:.75rem;opacity:.6;">
                            Un mot de passe temporaire sera généré automatiquement et affiché une seule fois.
                        </p>
                        <div style="display:flex;gap:.5rem;">
                            <button type="submit" class="btn btn-primary">Créer l'administrateur</button>
                            <button type="button" wire:click="toggleAddAdmin" class="btn">Annuler</button>
                        </div>
                    </form>
                @else
                    <p class="fin-empty" style="padding:.5rem 0 1rem;">Aucun administrateur défini.</p>
                    <button wire:click="toggleAddAdmin" class="btn btn-primary">+ Créer un administrateur</button>
                @endif
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div style="display:flex;align-items:center;gap:.75rem;margin-top:1.25rem;">
        <button wire:click="toggleActive" class="btn {{ $school->status === 'active' ? 'btn-danger' : 'btn-green' }}">
            {{ $school->status === 'active' ? "Désactiver l'école" : "Activer l'école" }}
        </button>

        @if ($this->admin)
            <form method="POST" action="{{ route('superadmin.schools.impersonate', $school) }}">
                @csrf
                <button type="submit" class="btn" style="border-color:#E8A838;color:#8A6010;">
                    🔑 Se connecter en tant que cette école
                </button>
            </form>
        @endif
    </div>
</div>

<style>
    .fin-error { display:block; font-size:.75rem; color:var(--accent-red); margin-top:.25rem; }
    @media (min-width: 640px) {
        .sm-grid-2 { grid-template-columns: repeat(2, 1fr) !important; }
    }
</style>
