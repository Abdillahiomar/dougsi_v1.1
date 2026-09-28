{{-- resources/views/layouts/superadmin.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Superadmin — Dugsi</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,600;0,9..144,700;1,9..144,400&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600;700&display=swap"
          rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>

<div class="app-shell"
     x-data="{ sidebarOpen: false }"
     :class="{ 'sidebar-open': sidebarOpen }"
     @toggle-sidebar.window="sidebarOpen = !sidebarOpen">

    @include('layouts.partials.superadmin-sidebar')
    @include('layouts.partials.superadmin-header')

    <div class="sidebar-overlay"
         x-show="sidebarOpen"
         @click="sidebarOpen = false"
         x-transition.opacity
         style="display:none;"></div>

    <main class="app-main" id="app-main">
        {{ $slot }}
    </main>

    <footer class="app-footer">
        <span class="footer-copy">DOUGSI &copy; {{ date('Y') }} — Espace Superadmin</span>
    </footer>

</div>

@livewireScripts
</body>
</html>
