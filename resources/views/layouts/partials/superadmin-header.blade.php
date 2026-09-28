{{-- ============================================================
     resources/views/layouts/partials/superadmin-header.blade.php
     ============================================================ --}}

@php
    $sa = auth('superadmin')->user();
    $initials = $sa ? strtoupper(substr($sa->name, 0, 1)) . strtoupper(substr(strstr($sa->name, ' '), 1, 1) ?: substr($sa->name, 1, 1)) : 'SA';
@endphp

<header class="top-bar" id="app-header">

    <div class="header-left">
        <div class="header-school-name">Espace Superadmin</div>
        <div class="header-clock">
            <span class="header-date" id="hdr-date"></span>
            <span class="header-time" id="hdr-time"></span>
        </div>
        <button type="button" class="sidebar-toggle" @click="$dispatch('toggle-sidebar')">
            ☰
        </button>
    </div>

    <div class="header-right">

        <button type="button"
                id="theme-toggle"
                class="theme-toggle-btn"
                onclick="toggleTheme()"
                title="Changer le thème">
            <svg id="icon-light" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
            </svg>
            <svg id="icon-dark" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="display:none;">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
        </button>

        <div class="user-menu" x-data="{ open: false }" @click.outside="open = false">
            <button type="button"
                    class="user-menu-trigger"
                    @click="open = !open"
                    :aria-expanded="open">
                <div class="user-menu-avatar">{{ $initials }}</div>
                <div class="user-menu-info">
                    <span class="user-menu-name">{{ $sa?->name }}</span>
                    <span class="user-menu-role">Superadmin</span>
                </div>
                <svg class="user-menu-chevron"
                     :class="{ 'rotated': open }"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="user-menu-dropdown"
                 x-show="open"
                 x-transition:enter="dropdown-enter"
                 x-transition:enter-start="dropdown-enter-start"
                 x-transition:enter-end="dropdown-enter-end"
                 x-transition:leave="dropdown-leave"
                 x-transition:leave-start="dropdown-leave-start"
                 x-transition:leave-end="dropdown-leave-end"
                 style="display:none;">

                <div class="dropdown-header">
                    <div class="dropdown-avatar">{{ $initials }}</div>
                    <div>
                        <div class="dropdown-name">{{ $sa?->name }}</div>
                        <div class="dropdown-email">{{ $sa?->email }}</div>
                        <div class="dropdown-role-badge">Superadmin</div>
                    </div>
                </div>

                <div class="dropdown-divider"></div>

                <form method="POST" action="{{ route('superadmin.logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item dropdown-item-danger">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Se déconnecter
                    </button>
                </form>
            </div>
        </div>

    </div>
</header>

<script>
(function () {
    const JOURS = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
    const MOIS  = ['janvier','février','mars','avril','mai','juin','juillet',
                   'août','septembre','octobre','novembre','décembre'];

    function pad(n) { return String(n).padStart(2, '0'); }

    function tick() {
        const now  = new Date();
        const date = JOURS[now.getDay()] + ' ' + now.getDate() + ' '
                   + MOIS[now.getMonth()] + ' ' + now.getFullYear();
        const time = pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
        const d = document.getElementById('hdr-date');
        const t = document.getElementById('hdr-time');
        if (d) d.textContent = date;
        if (t) t.textContent = time;
    }

    tick();
    setInterval(tick, 1000);
})();

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('dugsi-theme', theme);

    const iconLight = document.getElementById('icon-light');
    const iconDark  = document.getElementById('icon-dark');

    if (theme === 'dark') {
        if (iconLight) iconLight.style.display = 'none';
        if (iconDark)  iconDark.style.display  = '';
    } else {
        if (iconLight) iconLight.style.display = '';
        if (iconDark)  iconDark.style.display  = 'none';
    }
}

function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme') || 'light';
    applyTheme(current === 'dark' ? 'light' : 'dark');
}

(function () {
    const saved = localStorage.getItem('dugsi-theme') || 'light';
    applyTheme(saved);
})();
</script>
