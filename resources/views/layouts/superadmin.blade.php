{{-- resources/views/layouts/superadmin.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Superadmin — Dugsi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100" x-data="{ sidebarOpen: false }">
    <div class="flex min-h-screen">
        {{-- Overlay mobile --}}
        <div x-show="sidebarOpen"
             x-transition.opacity
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-black/50 z-30 md:hidden"
             style="display: none;"></div>

        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-40 w-64 bg-gray-900 text-gray-100 flex flex-col transform transition-transform duration-200 ease-in-out md:static md:translate-x-0">
            {{-- En-tête --}}
            <div class="px-6 py-5 border-b border-gray-800">
                <div class="text-lg font-bold">Dugsi</div>
                <div class="text-xs text-gray-400">Espace Superadmin</div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 px-3 py-4 space-y-1">
                @php
                    $link = 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition';
                    $active = 'bg-gray-800 text-white';
                    $idle = 'text-gray-300 hover:bg-gray-800 hover:text-white';
                @endphp

                <a href="{{ route('superadmin.dashboard') }}"
                   @click="sidebarOpen = false"
                   class="{{ $link }} {{ request()->routeIs('superadmin.dashboard') ? $active : $idle }}">
                    <span>📊</span> Tableau de bord
                </a>

                <a href="{{ route('superadmin.schools.index') }}"
                   @click="sidebarOpen = false"
                   class="{{ $link }} {{ request()->routeIs('superadmin.schools.*') ? $active : $idle }}">
                    <span>🏫</span> Écoles
                </a>

                {{-- Décommente au fur et à mesure que tu crées les pages --}}

                <a href="{{ route('superadmin.users.index') }}"
                   @click="sidebarOpen = false"
                   class="{{ $link }} {{ request()->routeIs('superadmin.users.*') ? $active : $idle }}">
                    <span>👥</span> Utilisateurs
                </a>

                <a href="{{ route('superadmin.subscriptions.index') }}"
                   @click="sidebarOpen = false"
                   class="{{ $link }} {{ request()->routeIs('superadmin.subscriptions.*') ? $active : $idle }}">
                    <span>💳</span> Abonnements
                </a>
                <a href="{{ route('superadmin.activity.index') }}"
                   @click="sidebarOpen = false"
                   class="{{ $link }} {{ request()->routeIs('superadmin.activity.*') ? $active : $idle }}">
                    <span>📋</span> Journal d'activité
                </a>
                {{--
                <a href="{{ route('superadmin.settings') }}"
                   class="{{ $link }} {{ request()->routeIs('superadmin.settings') ? $active : $idle }}">
                    <span>⚙️</span> Paramètres
                </a>
                --}}
            </nav>

            {{-- Pied : utilisateur + déconnexion --}}
            <div class="px-3 py-4 border-t border-gray-800">
                <div class="px-3 pb-2 text-sm">
                    <div class="font-medium">{{ auth('superadmin')->user()->name }}</div>
                    <div class="text-xs text-gray-400">{{ auth('superadmin')->user()->email }}</div>
                </div>
                <form method="POST" action="{{ route('superadmin.logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full text-left px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-gray-800 hover:text-white transition">
                        Déconnexion
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            {{-- Barre mobile avec bouton menu --}}
            <header class="md:hidden flex items-center gap-3 bg-gray-900 text-white px-4 py-3 shrink-0">
                <button type="button"
                        @click="sidebarOpen = true"
                        aria-label="Ouvrir le menu"
                        class="p-1 -ml-1 text-2xl leading-none">☰</button>
                <span class="font-bold">Dugsi — Superadmin</span>
            </header>

            <main class="flex-1 overflow-auto">{{ $slot }}</main>
        </div>
    </div>
</body>
</html>