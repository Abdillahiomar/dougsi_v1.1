<?php
// resources/views/livewire/superadmin/dashboard.blade.php
use function Livewire\Volt\{computed};
use App\Models\School;
use App\Models\User;
use App\Models\Subscription;
use function Livewire\Volt\{layout};
layout('layouts.superadmin');

$stats = computed(fn () => [
    'schools'       => School::count(),
    'active'        => School::where('status', 'active')->count(),
    'total_users'   => User::whereNotNull('school_id')->count(),
    'subscriptions' => Subscription::where('status', 'active')->count(),
]);

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
