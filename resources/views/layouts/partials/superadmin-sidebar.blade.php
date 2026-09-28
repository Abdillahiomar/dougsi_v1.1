{{-- ============================================================
     resources/views/layouts/partials/superadmin-sidebar.blade.php
     Sidebar fixe de l'espace Superadmin — même design que l'app
     ============================================================ --}}
<aside class="sidebar" id="app-sidebar">
    <div class="sidebar-top">

        {{-- Brand --}}
        <div class="sidebar-brand">
            <div class="sidebar-logo-placeholder">D</div>
            <span class="sidebar-brand-name">Dugsi — Superadmin</span>
        </div>

        {{-- Navigation --}}
        <nav class="sidebar-nav">
            @php
                $navItems = [
                    [
                        'label' => 'Tableau de bord',
                        'route' => 'superadmin.dashboard',
                        'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
                    ],
                    [
                        'label' => 'Écoles',
                        'route' => 'superadmin.schools.index',
                        'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4v18M13 21V11l6 3v7M9 9h.01M9 12h.01M9 15h.01"/>',
                    ],
                    [
                        'label' => 'Utilisateurs',
                        'route' => 'superadmin.users.index',
                        'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
                    ],
                    [
                        'label' => 'Abonnements',
                        'route' => 'superadmin.subscriptions.index',
                        'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>',
                    ],
                    [
                        'label' => 'Factures',
                        'route' => 'superadmin.invoices.index',
                        'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
                    ],
                    [
                        'label' => "Journal d'activité",
                        'route' => 'superadmin.activity.index',
                        'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>',
                    ],
                ];
            @endphp

            <p class="nav-group-label">Plateforme</p>

            @foreach ($navItems as $item)
                @php
                    $href     = \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#';
                    $isActive = request()->routeIs(\Illuminate\Support\Str::beforeLast($item['route'], '.index') . '*');
                @endphp
                <a href="{{ $href }}"
                   @click="sidebarOpen = false"
                   class="nav-item {{ $isActive ? 'active' : '' }}">
                    <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        {!! $item['svg'] !!}
                    </svg>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Pied du sidebar --}}
    <div class="sidebar-footer-brand">
        <span style="font-family:'Fraunces',serif;font-size:.8125rem;font-weight:600;color:rgba(255,255,255,.35);letter-spacing:.04em;">
            DOUGSI
        </span>
        <span style="font-family:'JetBrains Mono',monospace;font-size:9px;color:rgba(255,255,255,.2);">
            Superadmin
        </span>
    </div>
</aside>
