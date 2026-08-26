<?php

use App\Models\Guardian;
use App\Models\StudentSchoolYear;
use App\Models\TimetableSlot;
use App\Services\AcademicYearService;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new class extends Component {

    #[Url] public string $childSsyId = '';

    public function with(): array
    {
        $user     = auth()->user();
        $year     = AcademicYearService::current();
        $guardian = Guardian::where('user_id', $user->id)->first();

        if (! $guardian) {
            return ['children' => collect(), 'slots' => collect(), 'selected' => null, 'year' => $year];
        }

        // Enfants du parent (année courante)
        $children = StudentSchoolYear::whereHas('student.guardians',
                fn ($q) => $q->where('guardians.id', $guardian->id)
            )
            ->when($year, fn ($q) => $q->where('academic_year_id', $year->id))
            ->with(['student', 'schoolClass'])
            ->get();

        // Enfant sélectionné (par défaut le premier)
        $selected = $this->childSsyId
            ? $children->firstWhere('id', (int) $this->childSsyId)
            : $children->first();

        // Créneaux de la classe de l'enfant sélectionné
        $slots = collect();
        if ($selected && $selected->school_class_id) {
            $slots = TimetableSlot::where('school_class_id', $selected->school_class_id)
                ->when($year, fn ($q) => $q->where('academic_year_id', $year->id))
                ->with(['subject', 'staff.user'])
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();
        }

        return compact('children', 'slots', 'selected', 'year');
    }
}; ?>

<div>
    @include('layouts.partials.finance-styles')

    <style>
        .et-head { margin-bottom:1.25rem; }
        .et-title { font-family:'Fraunces',serif; font-size:1.5rem; font-weight:700; color:var(--ink); }
        .et-sub { font-size:.875rem; color:var(--dsh-muted,#8A8578); margin-top:.15rem; }

        .et-picker { margin-bottom:1.5rem; max-width:340px; }
        .et-picker label { display:block; font-family:'JetBrains Mono',monospace; font-size:10px; font-weight:600;
                           text-transform:uppercase; letter-spacing:.08em; opacity:.5; margin-bottom:.35rem; }
        .et-picker select { width:100%; padding:.6rem .8rem; border-radius:9px; border:1px solid var(--line,#E5E2DA);
                            background:var(--paper-raised,#fff); font-size:.9rem; font-family:'Inter',sans-serif;
                            color:var(--ink); outline:none; }

        .et-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:.75rem; }
        @media (max-width:900px) { .et-grid { grid-template-columns:1fr; } }

        .et-day { background:var(--paper-raised,#fff); border:1px solid var(--line,#E5E2DA); border-radius:14px; overflow:hidden; }
        .et-day-head { padding:.7rem; text-align:center; font-family:'Fraunces',serif; font-weight:600;
                       font-size:.95rem; color:#fff; background:var(--sidebar,#2A3F7E); }
        .et-day-body { padding:.6rem; display:flex; flex-direction:column; gap:.5rem; min-height:80px; }

        .et-slot { border-radius:9px; padding:.6rem .7rem; border-left:3px solid; }
        .et-slot-time { font-family:'JetBrains Mono',monospace; font-size:11px; font-weight:600; opacity:.75; }
        .et-slot-subject { font-weight:600; font-size:.875rem; margin-top:2px; }
        .et-slot-meta { font-size:.75rem; color:var(--dsh-muted,#8A8578); margin-top:2px; }

        .et-day-empty { text-align:center; font-size:.75rem; color:var(--dsh-muted,#8A8578);
                        font-style:italic; padding:1rem .5rem; }
        .et-empty { padding:3rem; text-align:center; color:var(--dsh-muted,#8A8578);
                    background:var(--paper-raised,#fff); border:1px dashed var(--line,#E5E2DA); border-radius:14px; }
    </style>

    <div class="et-head">
        <div class="et-title">Emploi du temps</div>
        <div class="et-sub">Année {{ $year?->label ?? '—' }}</div>
    </div>

    @if ($children->isEmpty())
        <div class="et-empty">Aucun enfant rattaché à votre compte pour l'année en cours.</div>
    @else
        {{-- Sélecteur d'enfant --}}
        <div class="et-picker">
            <label>Choisir un enfant</label>
            <select wire:model.live="childSsyId">
                @foreach ($children as $child)
                    <option value="{{ $child->id }}">
                        {{ $child->student->fullName() }} — {{ $child->schoolClass?->name ?? 'Sans classe' }}
                    </option>
                @endforeach
            </select>
        </div>

        @if (! $selected || $slots->isEmpty())
            <div class="et-empty">
                Aucun emploi du temps disponible pour cette classe pour le moment.
            </div>
        @else
            @php $slotsByDay = $slots->groupBy('day_of_week'); @endphp
            <div class="et-grid">
                @foreach (\App\Models\TimetableSlot::$SCHOOL_DAYS as $dayNum)
                    <div class="et-day">
                        <div class="et-day-head">{{ \App\Models\TimetableSlot::$DAYS[$dayNum] }}</div>
                        <div class="et-day-body">
                            @forelse (($slotsByDay[$dayNum] ?? collect()) as $slot)
                                @php $c = $slot->effectiveColor(); @endphp
                                <div class="et-slot" style="background:{{ $c }}14; border-left-color:{{ $c }};">
                                    <div class="et-slot-time">
                                        {{ \Illuminate\Support\Str::substr($slot->start_time, 0, 5) }} – {{ \Illuminate\Support\Str::substr($slot->end_time, 0, 5) }}
                                    </div>
                                    <div class="et-slot-subject">{{ $slot->subject?->name ?? 'Matière' }}</div>
                                    <div class="et-slot-meta">
                                        @if ($slot->staff?->user)
                                            {{ $slot->staff->user->name }}
                                        @endif
                                        @if ($slot->room)
                                            · Salle {{ $slot->room }}
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="et-day-empty">—</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>