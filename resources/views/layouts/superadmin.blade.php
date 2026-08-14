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
        {{-- Sidebar superadmin --}}
        <aside class="w-64 bg-gray-900 text-white p-4">
            <div class="text-xl font-bold mb-6">Superadmin</div>
            <nav class="space-y-1">
                <a href="{{ route('superadmin.dashboard') }}"
                   class="block px-3 py-2 rounded hover:bg-gray-800">Tableau de bord</a>
                <a href="{{ route('superadmin.schools.index') }}"
                   class="block px-3 py-2 rounded hover:bg-gray-800">Écoles</a>
            </nav>
            <form method="POST" action="{{ route('superadmin.logout') }}" class="mt-6">
                @csrf
                <button type="submit"
                        class="w-full text-left px-3 py-2 rounded hover:bg-gray-800">
                    Déconnexion
                </button>
            </form>
        </aside>

        <main class="flex-1">{{ $slot }}</main>
    </div>
</body>
</html>