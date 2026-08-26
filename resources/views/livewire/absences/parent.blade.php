<?php

use App\Models\Attendance;
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

        // Inscriptions des enfants du parent, année courante
        $ssys = StudentSchoolYear::whereHas('student.guardians',
                fn ($q) => $q->where('guardians.id', $guardian->id)
            )
            ->when($year, fn ($q) => $q->where('academic_year_id', $year->id))
            ->with(['student', 'schoolClass'])
            ->get();

        $children = $ssys->map(function ($ssy) {
            // Toutes les absences / retards / excusés de l'année, hors présences
            $records = Attendance::where('student_school_year_id', $ssy->id)
                ->whereIn('status', ['absent', 'late', 'excused'])
                ->with('subject')
                ->orderByDesc('date')
                ->get();

            return [
                'ssy'       => $ssy,
                'student'   => $ssy->student,
                'class'     => $ssy->schoolClass?->name,
                'records'   => $records,
                'nb_absent' => $records->where('status', 'absent')->count(),
                'nb_late'   => $records->where('status', 'late')->count(),
                'nb_excused'=> $records->where('status', 'excused')->count(),
            ];
        });

        return compact('children', 'year');
    }
}; ?>

<div>
    @include('layouts.partials.finance-styles')

    <style>
        .ap-head { margin-bottom:1.5rem; }
        .ap-title { font-family:'Fraunces',serif; font-size:1.5rem; font-weight:700; color:var(--ink); }
        .ap-sub { font-size:.875rem; color:var(--dsh-muted,#8A8578); margin-top:.15rem; }

        .ap-child { background:var(--paper-raised,#fff); border:1px solid var(--line,#E5E2DA);
                    border-radius:16px; padding:1.5rem; margin-bottom:1.25rem; }
        .ap-child-head { display:flex; align-items:center; gap:.85rem; margin-bottom:1.15rem; }
        .ap-avatar { width:44px; height:44px; border-radius:12px; background:var(--sidebar,#2A3F7E); color:#fff;
                     display:flex; align-items:center; justify-content:center; font-family:'Fraunces',serif;
                     font-weight:700; font-size:1rem; flex-shrink:0; }
        .ap-child-name { font-weight:700; font-size:1.05rem; color:var(--ink); }
        .ap-child-class { font-size:.8rem; color:var(--dsh-muted,#8A8578); font-family:'JetBrains Mono',monospace; }

        .ap-counters { display:flex; gap:.75rem; margin-bottom:1.1rem; flex-wrap:wrap; }
        .ap-counter { flex:1; min-width:100px; text-align:center; padding:.75rem; border-radius:10px;
                      background:var(--paper,#F5F3EE); border:1px solid var(--line,#E5E2DA); }
        .ap-counter-num { font-family:'Fraunces',serif; font-size:1.5rem; font-weight:700; line-height:1; }
        .ap-counter-lbl { font-size:.7rem; color:var(--dsh-muted,#8A8578); text-transform:uppercase;
                          letter-spacing:.05em; font-family:'JetBrains Mono',monospace; margin-top:.35rem; }

        .ap-table { width:100%; border-collapse:collapse; }
        .ap-table th { text-align:left; font-size:.7rem; text-transform:uppercase; letter-spacing:.06em;
                       color:var(--dsh-muted,#8A8578); font-family:'JetBrains Mono',monospace;
                       padding:.5rem .6rem; border-bottom:1px solid var(--line,#E5E2DA); }
        .ap-table td { padding:.65rem .6rem; font-size:.875rem; border-bottom:1px solid var(--line,#E5E2DA); }
        .ap-table tr:last-child td { border-bottom:none; }

        .ap-badge { display:inline-block; padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600;
                    font-family:'JetBrains Mono',monospace; }
        .ap-badge-absent  { background:rgba(224,92,58,.12); color:var(--accent-red,#E05C3A); }
        .ap-badge-late    { background:rgba(232,168,56,.15); color:#8A6010; }
        .ap-badge-excused { background:rgba(74,222,128,.15); color:#166534; }

        .ap-just-ok { display:inline-flex; align-items:center; gap:4px; color:#166534; font-size:.8rem; text-decoration:none; }
        .ap-just-ok:hover { text-decoration:underline; }
        .ap-just-no { color:var(--dsh-muted,#8A8578); font-size:.8rem; font-style:italic; }

        .ap-clean { padding:1.5rem; text-align:center; color:#166534; font-size:.9rem;
                    background:rgba(74,222,128,.06); border-radius:10px; }
        .ap-empty { padding:3rem; text-align:center; color:var(--dsh-muted,#8A8578);
                    background:var(--paper-raised,#fff); border:1px dashed var(--line,#E5E2DA); border-radius:14px; }
    </style>

    <div class="ap-head">
        <div class="ap-title">Absences</div>
        <div class="ap-sub">Année {{ $year?->label ?? '—' }} · Suivi des absences et retards de vos enfants</div>
    </div>

    @forelse ($children as $child)
        <div class="ap-child">
            <div class="ap-child-head">
                <div class="ap-avatar">{{ \Illuminate\Support\Str::of($child['student']->fullName())->explode(' ')->map(fn($p) => \Illuminate\Support\Str::substr($p, 0, 1))->take(2)->join('') }}</div>
                <div>
                    <div class="ap-child-name">{{ $child['student']->fullName() }}</div>
                    <div class="ap-child-class">{{ $child['class'] ?? 'Classe non définie' }}</div>
                </div>
            </div>

            <div class="ap-counters">
                <div class="ap-counter">
                    <div class="ap-counter-num" style="color:var(--accent-red,#E05C3A);">{{ $child['nb_absent'] }}</div>
                    <div class="ap-counter-lbl">Absences</div>
                </div>
                <div class="ap-counter">
                    <div class="ap-counter-num" style="color:#8A6010;">{{ $child['nb_late'] }}</div>
                    <div class="ap-counter-lbl">Retards</div>
                </div>
                <div class="ap-counter">
                    <div class="ap-counter-num" style="color:#166534;">{{ $child['nb_excused'] }}</div>
                    <div class="ap-counter-lbl">Excusées</div>
                </div>
            </div>

            @if ($child['records']->isEmpty())
                <div class="ap-clean">Aucune absence ni retard enregistré. Parfait !</div>
            @else
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>Date</th><th>Séance</th><th>Matière</th><th>Statut</th><th>Justification</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($child['records'] as $rec)
                            <tr>
                                <td>{{ $rec->date?->format('d/m/Y') }}</td>
                                <td style="color:var(--dsh-muted,#8A8578);">{{ $rec->sessionLabel() }}</td>
                                <td>{{ $rec->subject?->name ?? '—' }}</td>
                                <td>
                                    @switch($rec->status)
                                        @case('absent')  <span class="ap-badge ap-badge-absent">Absent</span> @break
                                        @case('late')    <span class="ap-badge ap-badge-late">Retard</span> @break
                                        @case('excused') <span class="ap-badge ap-badge-excused">Excusée</span> @break
                                    @endswitch
                                </td>
                                <td>
                                    @if ($rec->justificationUrl())
                                        <a href="{{ $rec->justificationUrl() }}" target="_blank" class="ap-just-ok">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Justifiée
                                        </a>
                                    @elseif ($rec->justification)
                                        <span class="ap-just-ok" style="color:#166534;">{{ $rec->justification }}</span>
                                    @else
                                        <span class="ap-just-no">Non justifiée</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @empty
        <div class="ap-empty">
            Aucun enfant rattaché à votre compte pour l'année en cours.
        </div>
    @endforelse
</div>