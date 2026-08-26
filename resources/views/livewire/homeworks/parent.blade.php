<?php

use App\Models\Guardian;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\StudentSchoolYear;
use App\Services\AcademicYearService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    #[Url] public string $childSsyId = '';

    // Rendu en cours : [homework_id => fichier]
    public $submissionFile = null;
    public ?int $submittingHomeworkId = null;

    public bool $saved = false;

    /** Résout le guardian du parent connecté */
    private function guardian(): ?Guardian
    {
        return Guardian::where('user_id', auth()->id())->first();
    }

    /** Vérifie qu'un student_school_year appartient bien à un enfant du parent */
    private function ownsChild(int $ssyId): bool
    {
        $guardian = $this->guardian();
        if (! $guardian) return false;

        return StudentSchoolYear::where('id', $ssyId)
            ->whereHas('student.guardians', fn ($q) => $q->where('guardians.id', $guardian->id))
            ->exists();
    }

    public function startSubmission(int $homeworkId): void
    {
        $this->submittingHomeworkId = $homeworkId;
        $this->submissionFile = null;
    }

    public function cancelSubmission(): void
    {
        $this->submittingHomeworkId = null;
        $this->submissionFile = null;
    }

    public function submitHomework(int $homeworkId, int $ssyId): void
    {
        // ── Garde serveur 1 : l'enfant appartient-il à ce parent ? ──
        abort_unless($this->ownsChild($ssyId), 403);

        $homework = Homework::findOrFail($homeworkId);

        // ── Garde serveur 2 : le rendu est-il autorisé ? ──
        abort_unless($homework->allow_submission, 403);

        // ── Garde serveur 3 : l'enfant est-il bien dans la classe du devoir ? ──
        $ssy = StudentSchoolYear::findOrFail($ssyId);
        abort_unless($ssy->school_class_id === $homework->school_class_id, 403);

        $this->validate([
            'submissionFile' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
        ], [
            'submissionFile.required' => 'Veuillez choisir un fichier à rendre.',
            'submissionFile.max'      => 'Le fichier ne doit pas dépasser 10 Mo.',
            'submissionFile.mimes'    => 'Formats acceptés : PDF, Word, image ou texte.',
        ]);

        // Rendu existant ?
        $existing = HomeworkSubmission::where('homework_id', $homeworkId)
            ->where('student_school_year_id', $ssyId)
            ->first();

        // ── Garde serveur 4 : pas de remplacement si déjà noté ──
        if ($existing && $existing->graded_at) {
            $this->addError('submissionFile', 'Ce devoir a déjà été noté, le rendu ne peut plus être modifié.');
            return;
        }

        // Supprimer l'ancien fichier si remplacement
        if ($existing && $existing->file_path) {
            Storage::disk('public')->delete($existing->file_path);
        }

        $path = $this->submissionFile->store('homework-submissions', 'public');

        $data = [
            'homework_id'            => $homeworkId,
            'student_school_year_id' => $ssyId,
            'file_path'              => $path,
            'file_name'              => $this->submissionFile->getClientOriginalName(),
            'file_size'              => $this->formatSize($this->submissionFile->getSize()),
            'submitted_by'           => auth()->id(),
            'submitted_at'           => now(),
            'status'                 => 'submitted',
        ];

        if ($existing) {
            $existing->update($data);
        } else {
            HomeworkSubmission::create($data);
        }

        $this->submittingHomeworkId = null;
        $this->submissionFile = null;
        $this->saved = true;
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' Mo';
        if ($bytes >= 1024)    return round($bytes / 1024) . ' Ko';
        return $bytes . ' o';
    }

    public function with(): array
    {
        $year     = AcademicYearService::current();
        $guardian = $this->guardian();

        if (! $guardian) {
            return ['children' => collect(), 'selected' => null, 'homeworks' => collect(), 'year' => $year];
        }

        $children = StudentSchoolYear::whereHas('student.guardians',
                fn ($q) => $q->where('guardians.id', $guardian->id)
            )
            ->when($year, fn ($q) => $q->where('academic_year_id', $year->id))
            ->with(['student', 'schoolClass'])
            ->get();

        $selected = $this->childSsyId
            ? $children->firstWhere('id', (int) $this->childSsyId)
            : $children->first();

        // Devoirs de la classe de l'enfant + rendu éventuel de cet enfant
        $homeworks = collect();
        if ($selected && $selected->school_class_id) {
            $homeworks = Homework::where('school_class_id', $selected->school_class_id)
                ->when($year, fn ($q) => $q->where('academic_year_id', $year->id))
                ->with(['subject', 'staff.user'])
                ->orderByDesc('due_date')
                ->get()
                ->map(function ($hw) use ($selected) {
                    $hw->mySubmission = HomeworkSubmission::where('homework_id', $hw->id)
                        ->where('student_school_year_id', $selected->id)
                        ->first();
                    return $hw;
                });
        }

        return compact('children', 'selected', 'homeworks', 'year');
    }
}; ?>

<div>
    @include('layouts.partials.finance-styles')

    <style>
        .hw-head { margin-bottom:1.25rem; }
        .hw-title { font-family:'Fraunces',serif; font-size:1.5rem; font-weight:700; color:var(--ink); }
        .hw-sub { font-size:.875rem; color:var(--dsh-muted,#8A8578); margin-top:.15rem; }

        .hw-picker { margin-bottom:1.5rem; max-width:340px; }
        .hw-picker label { display:block; font-family:'JetBrains Mono',monospace; font-size:10px; font-weight:600;
                           text-transform:uppercase; letter-spacing:.08em; opacity:.5; margin-bottom:.35rem; }
        .hw-picker select { width:100%; padding:.6rem .8rem; border-radius:9px; border:1px solid var(--line,#E5E2DA);
                            background:var(--paper-raised,#fff); font-size:.9rem; font-family:'Inter',sans-serif;
                            color:var(--ink); outline:none; }

        .hw-card { background:var(--paper-raised,#fff); border:1px solid var(--line,#E5E2DA);
                   border-radius:14px; padding:1.35rem; margin-bottom:1rem; }
        .hw-card-top { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; margin-bottom:.75rem; }
        .hw-card-title { font-weight:700; font-size:1.05rem; color:var(--ink); }
        .hw-card-meta { font-size:.8rem; color:var(--dsh-muted,#8A8578); margin-top:3px; }
        .hw-badge { display:inline-block; padding:3px 11px; border-radius:20px; font-size:11px; font-weight:600;
                    font-family:'JetBrains Mono',monospace; white-space:nowrap; }
        .hw-desc { font-size:.9rem; color:var(--ink); line-height:1.55; margin:.6rem 0; opacity:.85; }

        .hw-row { display:flex; align-items:center; gap:.75rem; flex-wrap:wrap; padding-top:.85rem;
                  margin-top:.85rem; border-top:1px solid var(--line,#E5E2DA); }
        .hw-link { display:inline-flex; align-items:center; gap:5px; padding:.45rem .85rem; border-radius:8px;
                   font-size:.8rem; font-weight:600; text-decoration:none; border:1px solid var(--line,#E5E2DA);
                   background:var(--paper,#F5F3EE); color:var(--ink); }
        .hw-link:hover { background:var(--line,#E5E2DA); }
        .hw-link svg { width:14px; height:14px; }
        .hw-btn { padding:.5rem 1rem; border-radius:8px; font-size:.85rem; font-weight:600; border:none; cursor:pointer; }
        .hw-btn-submit { background:var(--sidebar,#2A3F7E); color:#fff; }
        .hw-btn-cancel { background:var(--paper,#F5F3EE); border:1px solid var(--line,#E5E2DA); color:var(--ink); }

        .hw-submitted { display:inline-flex; align-items:center; gap:6px; font-size:.85rem; color:#166534; font-weight:600; }
        .hw-late { color:var(--accent-red,#E05C3A); font-size:.75rem; font-weight:600; }
        .hw-graded { background:rgba(74,222,128,.12); border:1px solid rgba(74,222,128,.35); border-radius:9px;
                     padding:.6rem .85rem; margin-top:.6rem; font-size:.85rem; }

        .hw-form { background:var(--paper,#F5F3EE); border:1px dashed var(--line,#E5E2DA); border-radius:10px;
                   padding:1rem; margin-top:.85rem; }
        .hw-form input[type=file] { font-size:.85rem; margin-bottom:.6rem; display:block; }
        .hw-form-actions { display:flex; gap:.5rem; }
        .hw-err { color:var(--accent-red,#E05C3A); font-size:.75rem; margin-top:.3rem; }

        .hw-empty { padding:3rem; text-align:center; color:var(--dsh-muted,#8A8578);
                    background:var(--paper-raised,#fff); border:1px dashed var(--line,#E5E2DA); border-radius:14px; }
    </style>

    <div class="hw-head">
        <div class="hw-title">Devoirs à la maison</div>
        <div class="hw-sub">Année {{ $year?->label ?? '—' }} · Consultez et rendez les devoirs de vos enfants</div>
    </div>

    @if ($children->isEmpty())
        <div class="hw-empty">Aucun enfant rattaché à votre compte pour l'année en cours.</div>
    @else
        <div class="hw-picker">
            <label>Choisir un enfant</label>
            <select wire:model.live="childSsyId">
                @foreach ($children as $child)
                    <option value="{{ $child->id }}">
                        {{ $child->student->fullName() }} — {{ $child->schoolClass?->name ?? 'Sans classe' }}
                    </option>
                @endforeach
            </select>
        </div>

        @if ($saved)
            <div style="background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.4);color:#166534;padding:.7rem 1rem;border-radius:9px;font-size:.875rem;margin-bottom:1rem;">
                Devoir rendu avec succès.
            </div>
        @endif

        @forelse ($homeworks as $hw)
            <div class="hw-card">
                <div class="hw-card-top">
                    <div>
                        <div class="hw-card-title">{{ $hw->title }}</div>
                        <div class="hw-card-meta">
                            {{ $hw->subject?->name ?? 'Matière' }}
                            @if ($hw->staff?->user) · {{ $hw->staff->user->name }} @endif
                            · À rendre le {{ $hw->due_date?->format('d/m/Y') }}
                        </div>
                    </div>
                    <span class="hw-badge" style="background:{{ $hw->statusColor() }};">{{ $hw->statusLabel() }}</span>
                </div>

                @if ($hw->description)
                    <div class="hw-desc">{!! nl2br(e($hw->description)) !!}</div>
                @endif

                <div class="hw-row">
                    {{-- Pièce jointe du prof --}}
                    @if ($hw->fileUrl())
                        <a href="{{ $hw->fileUrl() }}" target="_blank" class="hw-link">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            Énoncé ({{ $hw->file_name }})
                        </a>
                    @endif

                    {{-- État du rendu --}}
                    @if ($hw->mySubmission)
                        <span class="hw-submitted">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Rendu le {{ $hw->mySubmission->submitted_at?->format('d/m/Y') }}
                        </span>
                        @if ($hw->mySubmission->isLate())
                            <span class="hw-late">En retard</span>
                        @endif
                        <a href="{{ $hw->mySubmission->fileUrl() }}" target="_blank" class="hw-link">Voir mon rendu</a>
                    @endif

                    {{-- Bouton rendre / remplacer (si autorisé et pas encore noté) --}}
                    @if ($hw->allow_submission && ! ($hw->mySubmission && $hw->mySubmission->graded_at))
                        @if ($submittingHomeworkId !== $hw->id)
                            <button wire:click="startSubmission({{ $hw->id }})" class="hw-btn hw-btn-submit">
                                {{ $hw->mySubmission ? 'Remplacer le rendu' : 'Rendre le devoir' }}
                            </button>
                        @endif
                    @endif
                </div>

                {{-- Formulaire de rendu --}}
                @if ($submittingHomeworkId === $hw->id)
                    <div class="hw-form">
                        <input type="file" wire:model="submissionFile">
                        <div wire:loading wire:target="submissionFile" style="font-size:.75rem;color:var(--dsh-muted,#8A8578);">Chargement...</div>
                        @error('submissionFile') <div class="hw-err">{{ $message }}</div> @enderror
                        <div class="hw-form-actions">
                            <button wire:click="submitHomework({{ $hw->id }}, {{ $selected->id }})" class="hw-btn hw-btn-submit"
                                    wire:loading.attr="disabled" wire:target="submitHomework">
                                <span wire:loading.remove wire:target="submitHomework">Envoyer</span>
                                <span wire:loading wire:target="submitHomework">Envoi...</span>
                            </button>
                            <button wire:click="cancelSubmission" class="hw-btn hw-btn-cancel">Annuler</button>
                        </div>
                    </div>
                @endif

                {{-- Note du professeur --}}
                @if ($hw->mySubmission && $hw->mySubmission->graded_at)
                    <div class="hw-graded">
                        <strong>Noté :</strong> {{ $hw->mySubmission->grade ?? '—' }}
                        @if ($hw->mySubmission->teacher_comment)
                            <div style="margin-top:.35rem;opacity:.85;">{{ $hw->mySubmission->teacher_comment }}</div>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="hw-empty">Aucun devoir pour cette classe pour le moment.</div>
        @endforelse
    @endif
</div>