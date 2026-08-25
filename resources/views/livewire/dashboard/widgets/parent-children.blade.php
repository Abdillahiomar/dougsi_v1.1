{{-- resources/views/livewire/dashboard/widgets/parent-children.blade.php --}}
{{-- Attend : $children = tableau retourné par StatisticsService::parentKpis() --}}

@php
    $totalBalance  = collect($children)->sum('balance');
    $totalAbsences = collect($children)->sum('absences');
    $totalPendingHw = collect($children)->sum('pending_hw');
@endphp

<div class="pc-wrap">

    {{-- Bande de synthèse foyer --}}
    <div class="pc-summary">
        <div class="pc-sum-card">
            <div class="pc-sum-icon" style="background:rgba(224,92,58,.12);color:var(--accent-red,#E05C3A);">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div class="pc-sum-body">
                <div class="pc-sum-label">Total à régler</div>
                <div class="pc-sum-value" style="color:{{ $totalBalance > 0 ? 'var(--accent-red,#E05C3A)' : 'inherit' }};">
                    {{ number_format((int) $totalBalance, 0, ',', ' ') }} <span class="pc-cur">DJF</span>
                </div>
            </div>
        </div>

        <div class="pc-sum-card">
            <div class="pc-sum-icon" style="background:rgba(232,168,56,.12);color:#B87914;">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div class="pc-sum-body">
                <div class="pc-sum-label">Absences (année)</div>
                <div class="pc-sum-value">{{ $totalAbsences }}</div>
            </div>
        </div>

        <div class="pc-sum-card">
            <div class="pc-sum-icon" style="background:rgba(42,63,126,.1);color:var(--sidebar,#2A3F7E);">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0118 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
            </div>
            <div class="pc-sum-body">
                <div class="pc-sum-label">Devoirs à rendre</div>
                <div class="pc-sum-value">{{ $totalPendingHw }}</div>
            </div>
        </div>
    </div>

    {{-- Titre de section --}}
    <div class="pc-section-title">Mes enfants</div>

    {{-- Grille des enfants --}}
    <div class="pc-grid">
        @forelse ($children as $child)
            <div class="pc-card">
                <div class="pc-card-head">
                    <div class="pc-avatar">{{ \Illuminate\Support\Str::of($child['name'])->explode(' ')->map(fn($p) => \Illuminate\Support\Str::substr($p, 0, 1))->take(2)->join('') }}</div>
                    <div class="pc-card-ident">
                        <div class="pc-card-name">{{ $child['name'] }}</div>
                        <div class="pc-card-class">{{ $child['class'] ?? 'Classe non définie' }}</div>
                    </div>
                </div>

                <div class="pc-metrics">
                    <div class="pc-metric">
                        <div class="pc-metric-label">Moyenne</div>
                        <div class="pc-metric-value">
                            @if (! is_null($child['avg']))
                                {{ number_format((float) $child['avg'], 2, ',', ' ') }}<span class="pc-metric-unit">/20</span>
                            @else
                                <span class="pc-metric-empty">—</span>
                            @endif
                        </div>
                        @if ($child['period'])
                            <div class="pc-metric-sub">{{ $child['period'] }}</div>
                        @endif
                    </div>

                    <div class="pc-metric">
                        <div class="pc-metric-label">Absences</div>
                        <div class="pc-metric-value" style="color:{{ $child['absences'] > 0 ? '#B87914' : 'inherit' }};">
                            {{ $child['absences'] }}
                        </div>
                    </div>

                    <div class="pc-metric">
                        <div class="pc-metric-label">Devoirs</div>
                        <div class="pc-metric-value">{{ $child['pending_hw'] }}</div>
                    </div>
                </div>

                <div class="pc-balance">
                    <span class="pc-balance-label">Solde restant</span>
                    <span class="pc-balance-value" style="color:{{ $child['balance'] > 0 ? 'var(--accent-red,#E05C3A)' : '#166534' }};">
                        {{ number_format((int) $child['balance'], 0, ',', ' ') }} DJF
                    </span>
                </div>
            </div>
        @empty
            <div class="pc-empty">
                Aucun enfant rattaché à votre compte pour l'année en cours.
            </div>
        @endforelse
    </div>
</div>

<style>
    .pc-wrap { display:flex; flex-direction:column; gap:1.75rem; }

    /* Bande de synthèse */
    .pc-summary { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; }
    .pc-sum-card { display:flex; align-items:center; gap:.9rem; background:var(--dsh-card,#fff);
                   border:1px solid var(--dsh-border,#E5E2DA); border-radius:14px; padding:1.1rem 1.25rem; }
    .pc-sum-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center;
                   justify-content:center; flex-shrink:0; }
    .pc-sum-icon svg { width:22px; height:22px; }
    .pc-sum-label { font-size:.75rem; color:var(--dsh-muted,#8A8578); font-family:'JetBrains Mono',monospace;
                    text-transform:uppercase; letter-spacing:.05em; }
    .pc-sum-value { font-family:'Fraunces',serif; font-size:1.5rem; font-weight:600; line-height:1.2; margin-top:2px; }
    .pc-cur { font-size:.8rem; font-weight:400; opacity:.6; }

    /* Section */
    .pc-section-title { font-family:'Fraunces',serif; font-size:1.15rem; font-weight:600; color:var(--dsh-ink,#1A1A1A); }

    /* Grille enfants */
    .pc-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:1.1rem; }
    .pc-card { background:var(--dsh-card,#fff); border:1px solid var(--dsh-border,#E5E2DA);
               border-radius:16px; padding:1.35rem; transition:box-shadow .18s, transform .18s; }
    .pc-card:hover { box-shadow:0 12px 34px rgba(0,0,0,.07); transform:translateY(-2px); }

    .pc-card-head { display:flex; align-items:center; gap:.8rem; margin-bottom:1.15rem; }
    .pc-avatar { width:46px; height:46px; border-radius:12px; background:var(--sidebar,#2A3F7E); color:#fff;
                 display:flex; align-items:center; justify-content:center; font-family:'Fraunces',serif;
                 font-weight:700; font-size:1.05rem; flex-shrink:0; }
    .pc-card-name { font-weight:700; font-size:1rem; color:var(--dsh-ink,#1A1A1A); line-height:1.2; }
    .pc-card-class { font-size:.8rem; color:var(--dsh-muted,#8A8578); margin-top:2px;
                     font-family:'JetBrains Mono',monospace; }

    .pc-metrics { display:grid; grid-template-columns:repeat(3,1fr); gap:.5rem; padding:1rem 0;
                  border-top:1px solid var(--dsh-border,#E5E2DA); border-bottom:1px solid var(--dsh-border,#E5E2DA); }
    .pc-metric { text-align:center; }
    .pc-metric-label { font-size:.65rem; color:var(--dsh-muted,#8A8578); text-transform:uppercase;
                       letter-spacing:.05em; font-family:'JetBrains Mono',monospace; }
    .pc-metric-value { font-family:'Fraunces',serif; font-size:1.25rem; font-weight:600; margin-top:3px; }
    .pc-metric-unit { font-size:.7rem; font-weight:400; opacity:.5; }
    .pc-metric-sub { font-size:.65rem; color:var(--dsh-muted,#8A8578); margin-top:1px; }
    .pc-metric-empty { color:var(--dsh-muted,#8A8578); opacity:.5; }

    .pc-balance { display:flex; justify-content:space-between; align-items:center; margin-top:1.1rem; }
    .pc-balance-label { font-size:.8rem; color:var(--dsh-muted,#8A8578); }
    .pc-balance-value { font-family:'JetBrains Mono',monospace; font-weight:600; font-size:.95rem; }

    .pc-empty { grid-column:1/-1; padding:2.5rem; text-align:center; font-size:.9rem;
                color:var(--dsh-muted,#8A8578); background:var(--dsh-card,#fff);
                border:1px dashed var(--dsh-border,#E5E2DA); border-radius:14px; }

    @media (max-width:760px) {
        .pc-summary { grid-template-columns:1fr; }
    }
</style>