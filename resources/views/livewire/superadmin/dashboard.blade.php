<?php
// resources/views/livewire/superadmin/dashboard.blade.php
use App\Models\School;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Invoice;
use Livewire\Volt\Component;
use Livewire\Attributes\{Computed, Layout};

new #[Layout('layouts.superadmin')] class extends Component
{
    public int $year;
    public string $month = ''; // '' = toute l'année

    public function mount(): void
    {
        $this->year = (int) now()->year;
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'schools'       => School::count(),
            'active'        => School::where('status', 'active')->count(),
            'total_users'   => User::whereNotNull('school_id')->count(),
            'subscriptions' => Subscription::where('status', 'active')->count(),
        ];
    }

    // Années disponibles pour le sélecteur (basées sur les factures existantes)
    #[Computed]
    public function availableYears()
    {
        $years = Invoice::withoutGlobalScopes()
            ->selectRaw('DISTINCT EXTRACT(YEAR FROM issued_at)::int as y')
            ->orderByDesc('y')
            ->pluck('y');

        return $years->isEmpty() ? collect([(int) now()->year]) : $years;
    }

    // KPI's de facturation Dugsi → écoles, filtrés par année (+ mois optionnel)
    #[Computed]
    public function billing(): array
    {
        $query = Invoice::withoutGlobalScopes()->whereYear('issued_at', $this->year);

        if ($this->month !== '') {
            $query->whereMonth('issued_at', (int) $this->month);
        }

        $invoices = $query->get();

        $totalInvoices   = $invoices->count();
        $forecastRevenue = (float) $invoices->sum('amount');
        $actualRevenue   = (float) $invoices->where('status', 'paid')->sum('amount');
        $outstanding     = (float) $invoices->whereIn('status', ['unpaid', 'overdue'])->sum('amount');
        $overdueCount    = $invoices->where('status', 'overdue')->count();
        $recoveryRate    = $forecastRevenue > 0 ? round(($actualRevenue / $forecastRevenue) * 100, 1) : 0.0;

        return [
            'totalInvoices'   => $totalInvoices,
            'forecastRevenue' => $forecastRevenue,
            'actualRevenue'   => $actualRevenue,
            'outstanding'     => $outstanding,
            'overdueCount'    => $overdueCount,
            'recoveryRate'    => $recoveryRate,
        ];
    }
};
?>

@include('layouts.partials.finance-styles')

<div class="p-6">
    <div class="page-head">
        <div>
            <div class="page-title">Tableau de bord</div>
            <div class="page-sub">Vue d'ensemble de la plateforme Dugsi</div>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi dark">
            <div class="lbl">Écoles</div>
            <div class="kpi-val">{{ $this->stats['schools'] }}</div>
            <div class="kpi-foot">sur la plateforme</div>
        </div>
        <div class="kpi good">
            <div class="lbl">Écoles actives</div>
            <div class="kpi-val">{{ $this->stats['active'] }}</div>
            <div class="kpi-foot">statut « active »</div>
        </div>
        <div class="kpi">
            <div class="lbl">Utilisateurs</div>
            <div class="kpi-val">{{ $this->stats['total_users'] }}</div>
            <div class="kpi-foot">toutes écoles confondues</div>
        </div>
        <div class="kpi">
            <div class="lbl">Abonnements actifs</div>
            <div class="kpi-val">{{ $this->stats['subscriptions'] }}</div>
            <div class="kpi-foot">en cours</div>
        </div>
    </div>

    <div class="fin-card">
        <div class="fin-card-header">
            <span class="fin-card-title">Facturation</span>
            <div style="margin-left:auto;display:flex;gap:.5rem;">
                <select wire:model.live="year" class="fin-select" style="width:auto;">
                    @foreach ($this->availableYears as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
                <select wire:model.live="month" class="fin-select" style="width:auto;">
                    <option value="">Toute l'année</option>
                    @foreach ([1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'] as $num => $label)
                        <option value="{{ $num }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="fin-card-body">
            <div class="kpi-grid">
                <div class="kpi">
                    <div class="lbl">Factures totales</div>
                    <div class="kpi-val">{{ $this->billing['totalInvoices'] }}</div>
                    <div class="kpi-foot">émises sur la période</div>
                </div>
                <div class="kpi">
                    <div class="lbl">CA prévisionnel</div>
                    <div class="kpi-val">{{ number_format($this->billing['forecastRevenue'], 0, ',', ' ') }}<span class="kpi-unit">FDJ</span></div>
                    <div class="kpi-foot">total facturé sur la période</div>
                </div>
                <div class="kpi good">
                    <div class="lbl">CA réel</div>
                    <div class="kpi-val">{{ number_format($this->billing['actualRevenue'], 0, ',', ' ') }}<span class="kpi-unit">FDJ</span></div>
                    <div class="kpi-foot">effectivement encaissé</div>
                </div>
                <div class="kpi {{ $this->billing['outstanding'] > 0 ? 'warn' : '' }}">
                    <div class="lbl">À recouvrir</div>
                    <div class="kpi-val">{{ number_format($this->billing['outstanding'], 0, ',', ' ') }}<span class="kpi-unit">FDJ</span></div>
                    <div class="kpi-foot">{{ $this->billing['overdueCount'] }} facture(s) en retard</div>
                </div>
                <div class="kpi {{ $this->billing['recoveryRate'] >= 80 ? 'good' : '' }}">
                    <div class="lbl">Taux de recouvrement</div>
                    <div class="kpi-val">{{ $this->billing['recoveryRate'] }}<span class="kpi-unit">%</span></div>
                    <div class="kpi-foot">CA réel / CA prévisionnel</div>
                </div>
            </div>
        </div>
    </div>

    <div class="fin-card">
        <div class="fin-card-header">
            <span class="fin-card-title">Accès rapide</span>
        </div>
        <div class="fin-card-body" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.85rem;">
            <a href="{{ route('superadmin.schools.create') }}" class="btn btn-primary" style="justify-content:center;">
                + Nouvelle école
            </a>
            <a href="{{ route('superadmin.schools.index') }}" class="btn" style="justify-content:center;">Gérer les écoles</a>
            <a href="{{ route('superadmin.users.index') }}" class="btn" style="justify-content:center;">Gérer les utilisateurs</a>
            <a href="{{ route('superadmin.subscriptions.index') }}" class="btn" style="justify-content:center;">Abonnements</a>
            <a href="{{ route('superadmin.invoices.index') }}" class="btn" style="justify-content:center;">Factures</a>
            <a href="{{ route('superadmin.activity.index') }}" class="btn" style="justify-content:center;">Journal d'activité</a>
        </div>
    </div>
</div>
