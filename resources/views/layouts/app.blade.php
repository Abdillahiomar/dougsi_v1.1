{{-- ============================================================
     resources/views/components/layouts/app.blade.php
     Layout principal — sidebar + header + footer fixes
     ============================================================ --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Dugsi') }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,600;0,9..144,700;1,9..144,400&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600;700&display=swap"
          rel="stylesheet">

    {{-- Styles compilés --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>

    {{-- Livewire --}}
    @livewireStyles

    {{-- Favicon dynamique selon l'école --}}
    @auth
        @php
            $school    = auth()->user()->school;
            $logoFile  = $school?->logo_path ? public_path('storage/schools/logos/'.basename($school->logo_path)) : null;
            $logoUrl   = ($logoFile && file_exists($logoFile))
                ? asset('storage/schools/logos/'.basename($school->logo_path)).'?v='.filemtime($logoFile)
                : null;
        @endphp

        @if ($logoUrl)
            <link rel="icon" type="image/png" href="{{ $logoUrl }}">
        @else
            <link rel="icon" href="/favicon.ico">
        @endif
    @else
        <link rel="icon" href="/favicon.ico">
    @endauth
</head>
{{-- pt-10 pousse le contenu vers le bas uniquement quand la bannière est visible --}}
<body class="{{ session()->has('impersonator_id') ? 'pt-10' : '' }}">

{{-- ============================================================
     Bannière d'impersonation — fixée tout en haut, au-dessus de tout.
     Placée HORS de .app-shell pour ne pas être masquée par la sidebar
     ou le header en position fixed.
     ============================================================ --}}
@if (session()->has('impersonator_id'))
    <div class="fixed top-0 inset-x-0 z-[9999] bg-amber-500 text-white px-4 py-2 flex items-center justify-between gap-4 text-sm shadow-lg">
        <span>
            ⚠️ Vous êtes connecté en tant que
            <strong>{{ auth()->user()?->name }}</strong>
            @if (auth()->user()?->school?->name)
                ({{ auth()->user()->school->name }})
            @endif
            — mode support superadmin.
        </span>
        <form method="POST" action="{{ route('superadmin.stop-impersonating') }}">
            @csrf
            <button type="submit"
                    class="underline font-medium hover:text-amber-100 whitespace-nowrap">
                ← Revenir à mon compte superadmin
            </button>
        </form>
    </div>
@endif

<div class="app-shell">

    {{-- ── Sidebar fixe ── --}}
    @include('layouts.partials.sidebar')

    {{-- ── Header fixe ── --}}
    @include('layouts.partials.header')

    {{-- ── Contenu scrollable ── --}}
    <main class="app-main" id="app-main">
        {{ $slot }}
    </main>

    {{-- ── Footer fixe ── --}}
    @include('layouts.partials.footer')

</div>

{{-- Livewire + Alpine --}}
@livewireScripts
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>

@stack('scripts')
</body>
</html>