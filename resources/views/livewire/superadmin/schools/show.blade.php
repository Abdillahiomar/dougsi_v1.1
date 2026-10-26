<?php
use App\Models\{School, User, Student, SchoolClass, Staff, Subscription, Plan, Invoice};
use App\Services\RoleTemplateService;
use App\Services\InvoiceGenerator;
use App\Services\SubscriptionPaymentService;
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

    // ── Abonnement ──
    public bool $showAddSubscription = false;
    public $subPlanId = null;
    public $subCustomAmount = null;
    public $subDiscountPercent = 0;
    public string $subBillingCycle = 'monthly';
    public string $subStatus = 'active';
    public $subStartsAt = null;
    public bool $subAutoRenew = true;

    // ── Paiement d'une facture ──
    public ?int $payingInvoiceId = null;
    public $payAmount = null;
    public string $payMethod = '';
    public string $payReference = '';
    public $payDate = null;

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

    // ── Abonnement ──
    #[Computed]
    public function subscription(): ?Subscription
    {
        return $this->school->subscriptions()->latest()->first();
    }

    #[Computed]
    public function plans()
    {
        return Plan::orderBy('price')->get();
    }

    #[Computed]
    public function invoices()
    {
        return $this->subscription
            ? $this->subscription->invoices()->orderByDesc('issued_at')->get()
            : collect();
    }

    public function toggleAddSubscription(): void
    {
        $this->showAddSubscription = ! $this->showAddSubscription;

        if ($this->showAddSubscription) {
            $this->subStartsAt = now()->format('Y-m-d');
        }
    }

    public function createSubscription(): void
    {
        abort_unless(auth('superadmin')->check(), 403);

        $this->validate([
            'subPlanId'          => 'nullable|exists:plans,id',
            'subCustomAmount'    => 'nullable|numeric|min:0',
            'subDiscountPercent' => 'required|numeric|min:0|max:100',
            'subBillingCycle'    => 'required|in:monthly,quarterly,semiannual,annual',
            'subStatus'          => 'required|in:active,trial,suspended,expired',
            'subStartsAt'        => 'required|date',
        ]);

        $startsAt = \Carbon\Carbon::parse($this->subStartsAt);

        $subscription = Subscription::create([
            'school_id'             => $this->school->id,
            'plan_id'               => $this->subPlanId ?: null,
            'custom_monthly_amount' => $this->subCustomAmount ?: null,
            'discount_percent'      => $this->subDiscountPercent,
            'billing_cycle'         => $this->subBillingCycle,
            'status'                => $this->subStatus,
            'starts_at'             => $startsAt,
            'ends_at'               => $startsAt->copy()->addYear(),
            'auto_renew'            => $this->subAutoRenew,
        ]);

        // Génère d'un coup toutes les factures de l'année contractuelle
        // (12 pour un cycle mensuel, 4 pour trimestriel, etc.)
        app(InvoiceGenerator::class)->generateYearSchedule($subscription);

        $this->showAddSubscription = false;
        session()->flash('status', 'Abonnement créé, factures de l\'année générées.');
        unset($this->subscription);
        unset($this->invoices);
    }

    public function generateInvoice(): void
    {
        abort_unless(auth('superadmin')->check(), 403);

        if (! $this->subscription) {
            return;
        }

        $invoice = app(InvoiceGenerator::class)->generateForSubscription($this->subscription);

        session()->flash('status', $invoice
            ? "Facture {$invoice->invoice_number} générée."
            : "Une facture existe déjà pour la période courante.");

        unset($this->invoices);
    }

    /**
     * Renouvelle manuellement l'abonnement d'un an : repousse la date de fin,
     * repasse le statut à "active", génère les factures de la nouvelle année.
     * Utile pour les abonnements sans renouvellement automatique, ou pour
     * renouveler en avance.
     */
    public function renewSubscription(): void
    {
        abort_unless(auth('superadmin')->check(), 403);

        if (! $this->subscription) {
            return;
        }

        $sub = $this->subscription;

        $newStart = ($sub->ends_at && $sub->ends_at->isFuture())
            ? $sub->ends_at->copy()->addDay()
            : now()->startOfDay();

        app(InvoiceGenerator::class)->generateYearSchedule($sub, $newStart);

        $sub->update([
            'status'  => 'active',
            'ends_at' => $newStart->copy()->addYear(),
        ]);

        session()->flash('status', "Abonnement renouvelé jusqu'au {$sub->fresh()->ends_at->format('d/m/Y')}.");
        unset($this->subscription);
        unset($this->invoices);
    }

    // ── Paiement d'une facture ──
    public function startPay(int $invoiceId): void
    {
        $invoice = Invoice::findOrFail($invoiceId);

        $this->payingInvoiceId = $invoice->id;
        $this->payAmount       = $invoice->amount;
        $this->payMethod       = '';
        $this->payReference    = '';
        $this->payDate         = now()->format('Y-m-d');
    }

    public function cancelPay(): void
    {
        $this->reset(['payingInvoiceId', 'payAmount', 'payMethod', 'payReference', 'payDate']);
    }

    public function confirmPay(): void
    {
        abort_unless(auth('superadmin')->check(), 403);

        $this->validate([
            'payAmount'    => 'required|numeric|min:0',
            'payMethod'    => 'nullable|string|max:50',
            'payReference' => 'nullable|string|max:100',
            'payDate'      => 'required|date',
        ]);

        $invoice = Invoice::findOrFail($this->payingInvoiceId);

        app(SubscriptionPaymentService::class)->recordPayment($invoice, [
            'amount'    => $this->payAmount,
            'method'    => $this->payMethod,
            'reference' => $this->payReference,
            'paid_at'   => $this->payDate,
        ]);

        session()->flash('status', "Paiement enregistré pour la facture {$invoice->invoice_number}.");
        $this->cancelPay();
        unset($this->invoices);
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

    {{-- Abonnement & Facturation --}}
    <div class="fin-card" style="margin-top:1.25rem;">
        <div class="fin-card-header">
            <span class="fin-card-title">Abonnement & Facturation</span>
            @if ($this->subscription)
                <div style="margin-left:auto;display:flex;gap:.5rem;">
                    <button wire:click="renewSubscription"
                            wire:confirm="Renouveler cet abonnement d'un an et générer les factures de la nouvelle année ?"
                            class="btn btn-icon" title="Renouveler d'un an">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    </button>
                    <a href="{{ route('superadmin.subscriptions.edit', $this->subscription) }}" class="btn btn-icon" title="Modifier l'abonnement">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                    </a>
                </div>
            @endif
        </div>
        <div class="fin-card-body">
            @if (! $this->subscription && ! $showAddSubscription)
                <p class="fin-empty" style="padding:.5rem 0 1rem;">Aucun abonnement pour cette école.</p>
                <button wire:click="toggleAddSubscription" class="btn btn-primary">+ Créer un abonnement</button>
            @elseif ($showAddSubscription)
                <form wire:submit="createSubscription" style="display:flex;flex-direction:column;gap:1rem;">
                    <div style="display:grid;grid-template-columns:1fr;gap:1rem;" class="sm-grid-2">
                        <div class="filter-field">
                            <span class="lbl">Plan de référence (optionnel)</span>
                            <select wire:model="subPlanId" class="fin-select">
                                <option value="">— Aucun (montant 100% négocié) —</option>
                                @foreach ($this->plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }} ({{ number_format($plan->price, 0, ',', ' ') }} FDJ/mois)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-field">
                            <span class="lbl">Montant mensuel négocié (FDJ)</span>
                            <input type="number" wire:model="subCustomAmount" class="fin-input" placeholder="Laisser vide pour utiliser le prix du plan">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr;gap:1rem;" class="sm-grid-2">
                        <div class="filter-field">
                            <span class="lbl">Périodicité</span>
                            <select wire:model="subBillingCycle" class="fin-select">
                                <option value="monthly">Mensuel</option>
                                <option value="quarterly">Trimestriel (3 mois)</option>
                                <option value="semiannual">Semestriel (6 mois)</option>
                                <option value="annual">Annuel (12 mois)</option>
                            </select>
                        </div>
                        <div class="filter-field">
                            <span class="lbl">Remise (%)</span>
                            <input type="number" step="0.01" wire:model="subDiscountPercent" class="fin-input">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr;gap:1rem;" class="sm-grid-2">
                        <div class="filter-field">
                            <span class="lbl">Statut</span>
                            <select wire:model="subStatus" class="fin-select">
                                <option value="active">Actif</option>
                                <option value="trial">Essai</option>
                                <option value="suspended">Suspendu</option>
                                <option value="expired">Expiré</option>
                            </select>
                        </div>
                        <div class="filter-field">
                            <span class="lbl">Date de début</span>
                            <input type="date" wire:model="subStartsAt" class="fin-input">
                            @error('subStartsAt') <span class="fin-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <label style="display:flex;align-items:center;gap:.5rem;font-size:.875rem;">
                        <input type="checkbox" wire:model="subAutoRenew">
                        Renouvellement automatique
                    </label>

                    <p style="font-size:.8125rem;opacity:.65;">
                        La date de fin (1 an après le début) et toutes les factures de l'année seront générées automatiquement à la création.
                    </p>

                    <div style="display:flex;gap:.5rem;">
                        <button type="submit" class="btn btn-primary">Créer l'abonnement</button>
                        <button type="button" wire:click="toggleAddSubscription" class="btn">Annuler</button>
                    </div>
                </form>
            @else
                <dl style="display:grid;grid-template-columns:1fr;gap:.85rem;font-size:.875rem;margin-bottom:1.25rem;" class="sm-grid-2">
                    <div>
                        <dt class="lbl">Plan</dt>
                        <dd style="margin-top:.2rem;font-weight:600;">{{ $this->subscription->plan?->name ?? 'Négocié (sans plan)' }}</dd>
                    </div>
                    <div>
                        <dt class="lbl">Montant par cycle</dt>
                        <dd style="margin-top:.2rem;" class="mono">{{ number_format($this->subscription->cycleAmount(), 0, ',', ' ') }} FDJ ({{ $this->subscription->cycleLabel() }})</dd>
                    </div>
                    <div>
                        <dt class="lbl">Statut</dt>
                        <dd style="margin-top:.3rem;">
                            <span @class([
                                'st',
                                'st-active'    => $this->subscription->status === 'active',
                                'st-trial'     => $this->subscription->status === 'trial',
                                'st-suspended' => in_array($this->subscription->status, ['suspended', 'expired']),
                            ])>
                                {{ match($this->subscription->status) { 'active'=>'Actif','trial'=>'Essai','suspended'=>'Suspendu','expired'=>'Expiré',default=>ucfirst($this->subscription->status) } }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="lbl">Période</dt>
                        <dd style="margin-top:.2rem;">
                            {{ $this->subscription->starts_at?->format('d/m/Y') }}
                            → {{ $this->subscription->ends_at?->format('d/m/Y') ?? 'en cours' }}
                        </dd>
                    </div>
                </dl>

                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;">
                    <span class="fin-card-sub" style="font-weight:600;">Factures</span>
                    <button wire:click="generateInvoice" class="btn btn-icon" title="Générer la facture de la période courante">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Générer facture
                    </button>
                </div>

                @if ($payingInvoiceId)
                    <form wire:submit="confirmPay" class="fin-card" style="background:var(--paper);padding:1rem;margin-bottom:1rem;display:flex;flex-direction:column;gap:.75rem;">
                        <div style="display:grid;grid-template-columns:1fr;gap:.75rem;" class="sm-grid-2">
                            <div class="filter-field">
                                <span class="lbl">Montant reçu (FDJ)</span>
                                <input type="number" wire:model="payAmount" class="fin-input">
                                @error('payAmount') <span class="fin-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="filter-field">
                                <span class="lbl">Méthode</span>
                                <select wire:model="payMethod" class="fin-select">
                                    <option value="">— Choisir —</option>
                                    <option value="d-money">D-Money</option>
                                    <option value="virement">Virement</option>
                                    <option value="especes">Espèces</option>
                                </select>
                            </div>
                            <div class="filter-field">
                                <span class="lbl">Référence (optionnel)</span>
                                <input wire:model="payReference" class="fin-input">
                            </div>
                            <div class="filter-field">
                                <span class="lbl">Date de paiement</span>
                                <input type="date" wire:model="payDate" class="fin-input">
                            </div>
                        </div>
                        <div style="display:flex;gap:.5rem;">
                            <button type="submit" class="btn btn-primary">Enregistrer le paiement</button>
                            <button type="button" wire:click="cancelPay" class="btn">Annuler</button>
                        </div>
                    </form>
                @endif

                <table class="fin-table">
                    <thead>
                        <tr>
                            <th>N° Facture</th>
                            <th class="num">Montant</th>
                            <th>Émise le</th>
                            <th>Échéance</th>
                            <th>Statut</th>
                            <th class="num">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->invoices as $invoice)
                            <tr>
                                <td class="mono">{{ $invoice->invoice_number }}</td>
                                <td class="num mono">{{ number_format($invoice->amount, 0, ',', ' ') }} FDJ</td>
                                <td>{{ $invoice->issued_at?->format('d/m/Y') }}</td>
                                <td>{{ $invoice->due_at?->format('d/m/Y') }}</td>
                                <td>
                                    <span @class([
                                        'st',
                                        'st-paid'      => $invoice->status === 'paid',
                                        'st-overdue'   => $invoice->status === 'overdue',
                                        'st-trial'     => $invoice->status === 'unpaid',
                                        'st-cancelled' => $invoice->status === 'cancelled',
                                    ])>
                                        {{ match($invoice->status) { 'paid'=>'Payée','unpaid'=>'Impayée','overdue'=>'En retard','cancelled'=>'Annulée',default=>ucfirst($invoice->status) } }}
                                    </span>
                                </td>
                                <td class="num">
                                    @if (in_array($invoice->status, ['unpaid', 'overdue']))
                                        <button wire:click="startPay({{ $invoice->id }})" class="btn btn-green btn-icon" title="Marquer payée">
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        </button>
                                    @else
                                        <span style="opacity:.4;">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="fin-empty">Aucune facture pour cet abonnement.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
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
