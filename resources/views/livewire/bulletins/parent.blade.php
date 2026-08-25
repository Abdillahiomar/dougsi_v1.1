<?php

use App\Models\Bulletin;
use App\Models\Guardian;
use App\Models\StudentSchoolYear;
use App\Services\AcademicYearService;
use Livewire\Volt\Component;

new class extends Component {

    public function with(): array
    {
        $user     = auth()->user();
        $year     = AcademicYearService::current();
        $guardian = Guardian::where('user_id', $user->id)->first();

        if (! $guardian) {
            return ['children' => collect(), 'year' => $year];
        }

        // Les inscriptions (student_school_year) des enfants du parent, année courante
        $ssys = StudentSchoolYear::whereHas('student.guardians',
                fn ($q) => $q->where('guardians.id', $guardian->id)
            )
            ->when($year, fn ($q) => $q->where('academic_year_id', $year->id))
            ->with(['student', 'schoolClass'])
            ->get();

        // Pour chaque enfant, ses bulletins générés
        $children = $ssys->map(function ($ssy) {
            $bulletins = Bulletin::where('student_school_year_id', $ssy->id)
                ->orderByDesc('generated_at')
                ->get();

            return [
                'ssy'        => $ssy,
                'student'    => $ssy->student,
                'class'      => $ssy->schoolClass?->name,
                'bulletins'  => $bulletins,
            ];
        });

        return compact('children', 'year');
    }
}; ?>

<div>
    @include('layouts.partials.finance-styles')

    <style>
        .bp-head { margin-bottom:1.5rem; }
        .bp-title { font-family:'Fraunces',serif; font-size:1.5rem; font-weight:700; color:var(--ink); }
        .bp-sub { font-size:.875rem; color:var(--dsh-muted,#8A8578); margin-top:.15rem; }

        .bp-child { background:var(--paper-raised,#fff); border:1px solid var(--line,#E5E2DA);
                    border-radius:16px; padding:1.5rem; margin-bottom:1.25rem; }
        .bp-child-head { display:flex; align-items:center; gap:.85rem; margin-bottom:1.1rem;
                         padding-bottom:1rem; border-bottom:1px solid var(--line,#E5E2DA); }
        .bp-avatar { width:44px; height:44px; border-radius:12px; background:var(--sidebar,#2A3F7E); color:#fff;
                     display:flex; align-items:center; justify-content:center; font-family:'Fraunces',serif;
                     font-weight:700; font-size:1rem; flex-shrink:0; }
        .bp-child-name { font-weight:700; font-size:1.05rem; color:var(--ink); }
        .bp-child-class { font-size:.8rem; color:var(--dsh-muted,#8A8578); font-family:'JetBrains Mono',monospace; }

        .bp-bulletin-row { display:flex; align-items:center; justify-content:space-between;
                           padding:.85rem 0; border-bottom:1px solid var(--line,#E5E2DA); }
        .bp-bulletin-row:last-child { border-bottom:none; }
        .bp-bul-info { display:flex; align-items:center; gap:1.5rem; }
        .bp-bul-period { font-weight:600; font-size:.95rem; color:var(--ink); min-width:110px; }
        .bp-bul-meta { font-size:.8rem; color:var(--dsh-muted,#8A8578); }
        .bp-bul-avg { font-family:'JetBrains Mono',monospace; font-weight:600; font-size:.9rem; }
        .bp-bul-actions { display:flex; gap:.5rem; }
        .bp-btn { display:inline-flex; align-items:center; gap:5px; padding:.45rem .85rem; border-radius:8px;
                  font-size:.8rem; font-weight:600; text-decoration:none; border:1px solid var(--line,#E5E2DA);
                  background:var(--paper,#F5F3EE); color:var(--ink); transition:background .12s; }
        .bp-btn:hover { background:var(--line,#E5E2DA); }
        .bp-btn svg { width:14px; height:14px; }
        .bp-btn-pdf { background:rgba(224,92,58,.1); color:var(--accent-red,#E05C3A); border-color:rgba(224,92,58,.25); }
        .bp-btn-pdf:hover { background:rgba(224,92,58,.18); }

        .bp-no-bulletin { font-size:.85rem; color:var(--dsh-muted,#8A8578); font-style:italic; padding:.5rem 0; }
        .bp-empty { padding:3rem; text-align:center; color:var(--dsh-muted,#8A8578);
                    background:var(--paper-raised,#fff); border:1px dashed var(--line,#E5E2DA); border-radius:14px; }
    </style>

    <div class="bp-head">
        <div class="bp-title">Bulletins</div>
        <div class="bp-sub">Année {{ $year?->label ?? '—' }} · Consultez et téléchargez les bulletins de vos enfants</div>
    </div>

    @forelse ($children as $child)
        <div class="bp-child">
            <div class="bp-child-head">
                <div class="bp-avatar">{{ \Illuminate\Support\Str::of($child['student']->fullName())->explode(' ')->map(fn($p) => \Illuminate\Support\Str::substr($p, 0, 1))->take(2)->join('') }}</div>
                <div>
                    <div class="bp-child-name">{{ $child['student']->fullName() }}</div>
                    <div class="bp-child-class">{{ $child['class'] ?? 'Classe non définie' }}</div>
                </div>
            </div>

            @forelse ($child['bulletins'] as $bulletin)
                <div class="bp-bulletin-row">
                    <div class="bp-bul-info">
                        <span class="bp-bul-period">{{ $bulletin->period }}</span>
                        <span class="bp-bul-avg">
                            @if (! is_null($bulletin->average))
                                {{ number_format((float) $bulletin->average, 2, ',', ' ') }}/20
                            @else
                                <span style="opacity:.5;">—</span>
                            @endif
                        </span>
                        @if ($bulletin->rank)
                            <span class="bp-bul-meta">Rang : {{ $bulletin->rank }}</span>
                        @endif
                        <span class="bp-bul-meta">
                            {{ $bulletin->generated_at ? \Carbon\Carbon::parse($bulletin->generated_at)->format('d/m/Y') : '' }}
                        </span>
                    </div>
                    <div class="bp-bul-actions">
                        <a href="{{ route('bulletins.show', [$child['student']->id, $bulletin->id]) }}" class="bp-btn" wire:navigate>
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Voir
                        </a>
                        <a href="{{ route('bulletins.pdf', [$child['student']->id, $bulletin->id]) }}" class="bp-btn bp-btn-pdf" target="_blank">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            PDF
                        </a>
                    </div>
                </div>
            @empty
                <div class="bp-no-bulletin">Aucun bulletin disponible pour le moment.</div>
            @endforelse
        </div>
    @empty
        <div class="bp-empty">
            Aucun enfant rattaché à votre compte pour l'année en cours.
        </div>
    @endforelse
</div>