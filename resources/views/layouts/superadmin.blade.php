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
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <aside class="w-64 bg-gray-900 text-gray-100 flex flex-col">
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
                   class="{{ $link }} {{ request()->routeIs('superadmin.dashboard') ? $active : $idle }}">
                    <span>📊</span> Tableau de bord
                </a>

                <a href="{{ route('superadmin.schools.index') }}"
                   class="{{ $link }} {{ request()->routeIs('superadmin.schools.*') ? $active : $idle }}">
                    <span>🏫</span> Écoles
                </a>

                {{-- Décommente au fur et à mesure que tu crées les pages --}}
                
                <a href="{{ route('superadmin.users.index') }}"
                   class="{{ $link }} {{ request()->routeIs('superadmin.users.*') ? $active : $idle }}">
                    <span>👥</span> Utilisateurs
                </a>
                {{--
                <a href="{{ route('superadmin.subscriptions.index') }}"
                   class="{{ $link }} {{ request()->routeIs('superadmin.subscriptions.*') ? $active : $idle }}">
                    <span>💳</span> Abonnements
                </a>
                <a href="{{ route('superadmin.activity.index') }}"
                   class="{{ $link }} {{ request()->routeIs('superadmin.activity.*') ? $active : $idle }}">
                    <span>📋</span> Journal d'activité
                </a>
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

        <main class="flex-1 overflow-auto">{{ $slot }}</main>
    </div>
</body>
</html>