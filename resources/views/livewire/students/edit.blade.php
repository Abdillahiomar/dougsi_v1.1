<?php

use App\Models\Student;
use App\Models\Guardian;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Models\RequiredDocument;
use App\Models\StudentDocument;
use App\Models\StudentSchoolYear;
use App\Services\AcademicYearService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public Student $student;

    // ── Infos personnelles ───────────────────────────────────────
    public string $first_name     = '';
    public string $last_name      = '';
    public string $matricule      = '';
    public string $birth_date     = '';
    public string $birth_place    = '';
    public string $gender         = '';
    public string $status         = '';
    public $photo                 = null;
    public string $existing_photo = '';

    // ── Scolarité ─────────────────────────────────────────────────
    public string $level_id        = '';
    public string $school_class_id = '';

    // ── Édition d'un tuteur (inline) ─────────────────────────────
    public ?int   $editingGuardianId = null;
    public string $eg_first_name     = '';
    public string $eg_last_name      = '';
    public string $eg_phone          = '';
    public string $eg_email          = '';
    public string $eg_profession     = '';
    public string $eg_relationship   = 'pere';

    // ── Ajout d'un tuteur ─────────────────────────────────────────
    public bool   $showAddGuardian  = false;
    public string $addGuardianMode  = 'new'; // existing|new
    public string $ag_existing_id   = '';
    public string $ag_first_name    = '';
    public string $ag_last_name     = '';
    public string $ag_phone         = '';
    public string $ag_email         = '';
    public string $ag_profession    = '';
    public string $ag_relationship  = 'pere';

    // ── Documents ─────────────────────────────────────────────────
    public array $docFiles = [];

    public bool $saved = false;

    public function mount(Student $student): void
    {
        $this->student = $student;

        $this->first_name     = $student->first_name;
        $this->last_name      = $student->last_name;
        $this->matricule      = $student->matricule;
        $this->birth_date     = $student->birth_date?->format('Y-m-d') ?? '';
        $this->birth_place    = $student->birth_place ?? '';
        $this->gender         = $student->gender ?? '';
        $this->status         = $student->status;
        $this->existing_photo = $student->photo_path ?? '';

        $year = AcademicYearService::current();
        $schoolYear = $student->schoolYears()
            ->where('academic_year_id', $year?->id)
            ->with('schoolClass')
            ->first();

        $this->school_class_id = (string) ($schoolYear?->school_class_id ?? '');
        $this->level_id        = (string) ($schoolYear?->schoolClass?->level_id ?? '');
    }

    public function updatedLevelId(): void
    {
        $this->school_class_id = '';
    }

    public function save(): void
    {
        $this->validate([
            'first_name'  => 'required|string|max:100',
            'last_name'   => 'required|string|max:100',
            'matricule'   => ['required', 'string', 'max:50', Rule::unique('students', 'matricule')->ignore($this->student->id)],
            'birth_date'  => 'nullable|date',
            'birth_place' => 'nullable|string|max:100',
            'gender'      => 'nullable|in:M,F',
            'status'      => 'required|in:active,transferred,graduated,dropped',
            'school_class_id' => 'nullable|exists:school_classes,id',
            'photo'       => 'nullable|image|max:2048',
        ]);

        $data = [
            'first_name'  => $this->first_name,
            'last_name'   => $this->last_name,
            'matricule'   => $this->matricule,
            'birth_date'  => $this->birth_date ?: null,
            'birth_place' => $this->birth_place ?: null,
            'gender'      => $this->gender ?: null,
            'status'      => $this->status,
        ];

        if ($this->photo) {
            if ($this->existing_photo) {
                Storage::disk('public')->delete($this->existing_photo);
            }
            $data['photo_path']   = $this->photo->store('students/photos', 'public');
            $this->existing_photo = $data['photo_path'];
            $this->photo          = null;
        }

        $this->student->update($data);

        // Classe (année active)
        $year = AcademicYearService::current();
        $ssy  = null;
        if ($year && $this->school_class_id) {
            $ssy = StudentSchoolYear::updateOrCreate(
                ['student_id' => $this->student->id, 'academic_year_id' => $year->id],
                ['school_class_id' => $this->school_class_id]
            );
        } elseif ($year) {
            $ssy = $this->student->schoolYears()->where('academic_year_id', $year->id)->first();
        }

        // Documents fournis/remplacés
        if ($ssy) {
            foreach ($this->docFiles as $docId => $file) {
                if (! $file) continue;

                $existingDoc = StudentDocument::where('student_school_year_id', $ssy->id)
                    ->where('required_document_id', $docId)
                    ->first();

                if ($existingDoc?->file_path) {
                    Storage::disk('public')->delete($existingDoc->file_path);
                }

                $path = $file->store('students/documents', 'public');

                StudentDocument::updateOrCreate(
                    ['student_school_year_id' => $ssy->id, 'required_document_id' => $docId],
                    [
                        'file_path'     => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'status'        => 'provided',
                        'provided_at'   => now(),
                    ]
                );
            }
            $this->docFiles = [];
        }

        $this->saved = true;
    }

    // ── Tuteurs ──────────────────────────────────────────────────

    public function startEditGuardian(int $guardianId): void
    {
        $g = $this->student->guardians()->where('guardians.id', $guardianId)->first();
        if (! $g) return;

        $this->editingGuardianId = $guardianId;
        $this->eg_first_name     = $g->first_name;
        $this->eg_last_name      = $g->last_name;
        $this->eg_phone          = $g->phone;
        $this->eg_email          = $g->email ?? '';
        $this->eg_profession     = $g->profession ?? '';
        $this->eg_relationship   = $g->pivot->relationship ?? 'pere';
    }

    public function cancelEditGuardian(): void
    {
        $this->editingGuardianId = null;
    }

    public function saveGuardianEdit(): void
    {
        $this->validate([
            'eg_first_name'   => 'required|string|max:100',
            'eg_last_name'    => 'required|string|max:100',
            'eg_phone'        => 'required|string|max:30',
            'eg_email'        => 'nullable|email|max:150',
            'eg_relationship' => 'required|in:pere,mere,tuteur,autre',
        ]);

        $guardian = Guardian::findOrFail($this->editingGuardianId);
        $guardian->update([
            'first_name' => $this->eg_first_name,
            'last_name'  => $this->eg_last_name,
            'phone'      => $this->eg_phone,
            'email'      => $this->eg_email ?: null,
            'profession' => $this->eg_profession ?: null,
        ]);

        $this->student->guardians()->updateExistingPivot($guardian->id, [
            'relationship' => $this->eg_relationship,
        ]);

        $this->editingGuardianId = null;
    }

    public function makeGuardianPrimary(int $guardianId): void
    {
        foreach ($this->student->guardians as $g) {
            $this->student->guardians()->updateExistingPivot($g->id, [
                'is_primary_contact' => $g->id === $guardianId,
            ]);
        }
    }

    public function removeGuardian(int $guardianId): void
    {
        if ($this->student->guardians()->count() <= 1) {
            $this->addError('showAddGuardian', "Impossible de retirer le dernier tuteur de l'élève.");
            return;
        }

        $target = $this->student->guardians()->where('guardians.id', $guardianId)->first();
        $wasPrimary = (bool) ($target?->pivot?->is_primary_contact);

        $this->student->guardians()->detach($guardianId);

        if ($wasPrimary) {
            $newPrimary = $this->student->guardians()->first();
            if ($newPrimary) {
                $this->makeGuardianPrimary($newPrimary->id);
            }
        }
    }

    public function toggleAddGuardian(): void
    {
        $this->showAddGuardian = ! $this->showAddGuardian;
        $this->addGuardianMode = 'new';
        $this->reset(['ag_existing_id', 'ag_first_name', 'ag_last_name', 'ag_phone', 'ag_email', 'ag_profession']);
        $this->ag_relationship = 'pere';
    }

    public function addGuardian(): void
    {
        $schoolId = auth()->user()->school_id;

        if ($this->addGuardianMode === 'existing') {
            $this->validate(['ag_existing_id' => 'required|exists:guardians,id']);
            $guardianId = (int) $this->ag_existing_id;
        } else {
            $this->validate([
                'ag_first_name' => 'required|string|max:100',
                'ag_last_name'  => 'required|string|max:100',
                'ag_phone'      => 'required|string|max:30',
                'ag_email'      => 'nullable|email|max:150',
            ]);

            // Réutilise un tuteur existant par téléphone (évite les doublons)
            $guardian = Guardian::where('school_id', $schoolId)
                ->where('phone', $this->ag_phone)
                ->first();

            if (! $guardian) {
                $guardian = Guardian::create([
                    'school_id'  => $schoolId,
                    'first_name' => $this->ag_first_name,
                    'last_name'  => $this->ag_last_name,
                    'phone'      => $this->ag_phone,
                    'email'      => $this->ag_email ?: null,
                    'profession' => $this->ag_profession ?: null,
                ]);
            }
            $guardianId = $guardian->id;
        }

        if ($this->student->guardians()->where('guardians.id', $guardianId)->exists()) {
            $this->addError('ag_phone', 'Ce tuteur est déjà rattaché à cet élève.');
            return;
        }

        $isFirst = $this->student->guardians()->count() === 0;

        $this->student->guardians()->attach($guardianId, [
            'relationship'       => $this->ag_relationship,
            'is_primary_contact' => $isFirst,
        ]);

        $this->toggleAddGuardian();
    }

    public function with(): array
    {
        $schoolId = auth()->user()->school_id;
        $year     = AcademicYearService::current();

        $levels = Level::where('school_id', $schoolId)->orderBy('order')->get();

        $classes = $this->level_id
            ? SchoolClass::where('school_id', $schoolId)
                ->where('academic_year_id', $year?->id)
                ->where('level_id', $this->level_id)
                ->get()
            : collect();

        $studentGuardians = $this->student->guardians()->get();

        $availableGuardians = Guardian::where('school_id', $schoolId)
            ->whereNotIn('id', $studentGuardians->pluck('id'))
            ->orderBy('last_name')
            ->get();

        $ssy = $this->student->schoolYears()->where('academic_year_id', $year?->id)->first();

        $requiredDocs = collect();
        $existingDocs = collect();

        if ($this->level_id) {
            // Contrairement à la réinscription, l'édition doit permettre de
            // compléter/remplacer n'importe quelle pièce du dossier — y compris
            // celles marquées "new" (inscription initiale) si elles manquent encore.
            $requiredDocs = RequiredDocument::where('school_id', $schoolId)
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('applies_to_levels')
                      ->orWhereJsonContains('applies_to_levels', (int) $this->level_id);
                })
                ->orderBy('order')
                ->get();
        }

        if ($ssy) {
            $existingDocs = StudentDocument::where('student_school_year_id', $ssy->id)
                ->get()
                ->keyBy('required_document_id');
        }

        return compact('classes', 'levels', 'studentGuardians', 'availableGuardians', 'requiredDocs', 'existingDocs', 'year');
    }
}; ?>

<style>
    /* ── Breadcrumb ── */
    .breadcrumb {
        display: flex; align-items: center; gap: 0.5rem;
        font-size: 0.8125rem; margin-bottom: 1.5rem;
        color: var(--ink); opacity: 0.5;
    }
    .breadcrumb a { color: inherit; text-decoration: none; }
    .breadcrumb a:hover { opacity: 1; color: var(--sidebar-soft); }
    .breadcrumb svg { width: 14px; height: 14px; }
    .breadcrumb-current { opacity: 1; font-weight: 600; color: var(--ink); }

    /* ── Layout 2 colonnes ── */
    .edit-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
        align-items: start;
    }
    @media (max-width: 900px) { .edit-grid { grid-template-columns: 1fr; } }

    /* ── Cards ── */
    .card {
        border-radius: 12px;
        border: 1px solid var(--line);
        background: var(--paper-raised);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .card:last-child { margin-bottom: 0; }
    .card-header {
        padding: 0.875rem 1.5rem;
        border-bottom: 1px solid var(--line);
        display: flex; align-items: center; gap: 0.6rem;
    }
    .card-header-icon {
        width: 28px; height: 28px; border-radius: 7px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .card-header-icon svg { width: 15px; height: 15px; }
    .card-title {
        font-family: 'Fraunces', serif;
        font-size: 1rem; font-weight: 600; color: var(--ink);
    }
    .card-body { padding: 1.25rem 1.5rem; }

    /* ── Formulaire ── */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .form-row.single { grid-template-columns: 1fr; }
    .form-row.triple { grid-template-columns: 1fr 1fr 1fr; }
    @media (max-width: 600px) { .form-row, .form-row.triple { grid-template-columns: 1fr; } }

    .form-field { display: flex; flex-direction: column; gap: 0.35rem; }
    .form-label {
        font-family: 'JetBrains Mono', monospace;
        font-size: 10px; font-weight: 600;
        text-transform: uppercase; letter-spacing: 0.08em;
        color: var(--ink); opacity: 0.5;
    }
    .form-input, .form-select {
        padding: 0.5rem 0.75rem;
        border-radius: 8px; border: 1px solid var(--line);
        background: var(--paper);
        font-size: 0.875rem; font-family: 'Inter', sans-serif;
        color: var(--ink); outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
        width: 100%;
    }
    .form-input:focus, .form-select:focus {
        border-color: var(--sidebar-soft);
        box-shadow: 0 0 0 3px rgba(42,63,126,0.08);
    }
    .form-error {
        font-size: 0.75rem; color: var(--accent-red);
        margin-top: 0.2rem;
    }

    /* Radio genre */
    .radio-group { display: flex; gap: 0.5rem; }
    .radio-btn {
        flex: 1; padding: 0.45rem 0.5rem;
        border-radius: 7px; border: 1.5px solid var(--line);
        background: var(--paper);
        font-size: 0.8125rem; font-weight: 500; font-family: 'Inter', sans-serif;
        color: var(--ink); cursor: pointer; text-align: center;
        transition: border-color 0.12s, background 0.12s, color 0.12s;
        appearance: none;
    }
    .radio-btn.selected-m {
        border-color: var(--sidebar); background: rgba(30,45,90,0.07); color: var(--sidebar);
    }
    .radio-btn.selected-f {
        border-color: #B0307A; background: rgba(176,48,122,0.07); color: #B0307A;
    }

    /* ── Profil card (colonne droite) ── */
    .profile-card {
        border-radius: 12px; border: 1px solid var(--line);
        background: var(--paper-raised); overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .profile-avatar-wrap {
        padding: 2rem 1.5rem 1.25rem;
        display: flex; flex-direction: column; align-items: center;
        border-bottom: 1px solid var(--line);
        background: var(--paper);
    }
    .profile-avatar {
        width: 72px; height: 72px; border-radius: 50%;
        background: rgba(42,63,126,0.1); color: var(--sidebar-soft);
        font-family: 'JetBrains Mono', monospace;
        font-size: 22px; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 0.75rem;
    }
    .profile-name {
        font-family: 'Fraunces', serif;
        font-size: 1.1rem; font-weight: 600; color: var(--ink);
        text-align: center;
    }
    .profile-matric {
        font-family: 'JetBrains Mono', monospace;
        font-size: 11px; color: var(--ink); opacity: 0.4;
        margin-top: 2px; text-align: center;
    }
    .profile-meta { padding: 1rem 1.5rem; }
    .meta-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--line);
        font-size: 0.8125rem;
    }
    .meta-row:last-child { border-bottom: none; }
    .meta-label { color: var(--ink); opacity: 0.45; font-size: 0.75rem; }
    .meta-value { font-weight: 500; }

    /* Badges statut */
    .badge {
        display: inline-block;
        font-family: 'JetBrains Mono', monospace; font-size: 10px; font-weight: 600;
        padding: 2px 8px; border-radius: 4px;
        text-transform: uppercase; letter-spacing: 0.05em;
    }
    .badge-active      { background: rgba(42,63,126,0.1);  color: var(--sidebar-soft); }
    .badge-transferred { background: rgba(232,168,56,0.15); color: #8A6010; }
    .badge-dropped     { background: rgba(224,92,58,0.12);  color: #C04020; }
    .badge-graduated   { background: rgba(30,120,80,0.12);  color: #1A6040; }

    /* ── Boutons ── */
    .form-actions {
        display: flex; align-items: center; justify-content: flex-end;
        gap: 0.65rem; padding-top: 1rem;
        border-top: 1px solid var(--line); margin-top: 1.25rem;
    }
    .btn-cancel {
        padding: 0.5rem 1.1rem; border-radius: 8px;
        border: 1px solid var(--line); background: var(--paper);
        font-size: 0.875rem; font-weight: 500; font-family: 'Inter', sans-serif;
        color: var(--ink); cursor: pointer; text-decoration: none;
        transition: border-color 0.15s;
    }
    .btn-cancel:hover { border-color: var(--ink); }
    .btn-save {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 0.5rem 1.25rem; border-radius: 8px;
        background: var(--sidebar); color: #FFFFFF;
        font-size: 0.875rem; font-weight: 600; font-family: 'Inter', sans-serif;
        border: none; cursor: pointer;
        transition: background 0.15s;
    }
    .btn-save:hover { background: var(--sidebar-soft); }
    .btn-save svg { width: 15px; height: 15px; }

    /* ── Toast succès ── */
    .toast-success {
        display: flex; align-items: center; gap: 0.65rem;
        padding: 0.75rem 1.1rem; border-radius: 10px;
        background: rgba(30,120,80,0.1); border: 1px solid rgba(30,120,80,0.2);
        color: #1A6040; font-size: 0.875rem; font-weight: 500;
        margin-bottom: 1.25rem;
        animation: slideDown 0.2s ease;
    }
    .toast-success svg { width: 18px; height: 18px; flex-shrink: 0; }
    @keyframes slideDown { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }

    /* Danger zone */
    .danger-zone {
        border-radius: 12px;
        border: 1px solid rgba(224,92,58,0.25);
        background: rgba(224,92,58,0.04);
        overflow: hidden;
    }
    .danger-header {
        padding: 0.875rem 1.5rem;
        border-bottom: 1px solid rgba(224,92,58,0.15);
        font-family: 'JetBrains Mono', monospace;
        font-size: 10px; font-weight: 600;
        text-transform: uppercase; letter-spacing: 0.08em;
        color: var(--accent-red);
    }
    .danger-body { padding: 1rem 1.5rem; }
    .danger-desc { font-size: 0.8125rem; color: var(--ink); opacity: 0.6; margin-bottom: 0.875rem; }
    .btn-danger {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 0.45rem 1rem; border-radius: 8px;
        border: 1px solid rgba(224,92,58,0.4);
        background: transparent; color: var(--accent-red);
        font-size: 0.8125rem; font-weight: 600; font-family: 'Inter', sans-serif;
        cursor: pointer; transition: background 0.15s;
    }
    .btn-danger:hover { background: rgba(224,92,58,0.08); }
    .btn-danger svg { width: 14px; height: 14px; }

    /* ── Photo ── */
    .photo-wrap { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem; }
    .photo-circle {
        width: 64px; height: 64px; border-radius: 50%; overflow: hidden;
        border: 2px solid var(--line); flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: rgba(42,63,126,0.08); color: var(--sidebar-soft);
        font-family: 'JetBrains Mono', monospace; font-size: 18px; font-weight: 700;
    }
    .photo-circle img { width: 100%; height: 100%; object-fit: cover; }
    .photo-upload-btn {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 0.4rem 0.8rem; border-radius: 7px; border: 1px solid var(--line);
        background: var(--paper); font-size: 0.8125rem; cursor: pointer; position: relative;
    }
    .photo-upload-btn input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }

    /* ── Tuteurs ── */
    .guardian-card {
        border: 1px solid var(--line); border-radius: 10px;
        padding: 0.875rem 1rem; margin-bottom: 0.75rem;
    }
    .guardian-card:last-child { margin-bottom: 0; }
    .guardian-card-head { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap; }
    .guardian-name { font-weight: 600; font-size: 0.9rem; color: var(--ink); }
    .guardian-meta { font-size: 0.8125rem; color: var(--ink); opacity: 0.55; margin-top: 2px; }
    .guardian-badges { display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap; }
    .badge-primary { background: rgba(30,120,80,0.1); color: #1A6040; }
    .badge-relationship { background: rgba(42,63,126,0.08); color: var(--sidebar-soft); }
    .guardian-actions { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.65rem; flex-wrap: wrap; }
    .btn-link {
        font-size: 0.75rem; font-weight: 600; background: none; border: none;
        cursor: pointer; padding: 0; color: var(--sidebar-soft);
    }
    .btn-link:hover { text-decoration: underline; }
    .btn-link.danger { color: var(--accent-red); }
    .btn-link[disabled] { opacity: 0.35; cursor: not-allowed; }

    .mode-toggle { display: flex; gap: 0.5rem; margin-bottom: 1rem; }
    .mode-btn {
        flex: 1; padding: 0.5rem; border-radius: 8px; border: 1.5px solid var(--line);
        background: var(--paper); font-size: 0.8125rem; font-weight: 500;
        font-family: 'Inter', sans-serif; color: var(--ink); cursor: pointer;
        text-align: center; transition: all 0.12s;
    }
    .mode-btn.active { border-color: var(--sidebar); background: rgba(30,45,90,0.07); color: var(--sidebar); }

    /* ── Documents ── */
    .doc-list { display: flex; flex-direction: column; gap: 0.875rem; }
    .doc-item { border: 1px solid var(--line); border-radius: 10px; overflow: hidden; }
    .doc-item-header { padding: 0.75rem 1rem; background: var(--paper); display: flex; align-items: center; gap: 0.65rem; }
    .doc-icon { width: 32px; height: 32px; border-radius: 7px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .doc-icon svg { width: 16px; height: 16px; }
    .doc-item-name { font-weight: 600; font-size: 0.875rem; color: var(--ink); }
    .doc-item-desc { font-size: 0.8rem; color: var(--ink); opacity: 0.5; }
    .badge-required { font-family: 'JetBrains Mono', monospace; font-size: 9px; font-weight: 600; padding: 1px 6px; border-radius: 3px; background: rgba(224,92,58,0.1); color: var(--accent-red); margin-left: auto; flex-shrink: 0; }
    .badge-optional { font-family: 'JetBrains Mono', monospace; font-size: 9px; font-weight: 600; padding: 1px 6px; border-radius: 3px; background: rgba(42,63,126,0.08); color: var(--sidebar-soft); margin-left: auto; flex-shrink: 0; }
    .doc-item-body { padding: 0.75rem 1rem; border-top: 1px solid var(--line); }
    .upload-row { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }
    .upload-label {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 0.4rem 0.875rem; border-radius: 7px; border: 1px dashed var(--line);
        background: var(--paper); font-size: 0.8125rem; font-family: 'Inter', sans-serif;
        color: var(--ink); cursor: pointer; position: relative; overflow: hidden; transition: all 0.12s;
    }
    .upload-label:hover { border-color: var(--sidebar-soft); color: var(--sidebar-soft); }
    .upload-label input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .upload-label svg { width: 14px; height: 14px; }
    .file-preview {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 0.35rem 0.75rem; border-radius: 6px;
        background: rgba(30,120,80,0.08); color: #166534;
        font-size: 0.8125rem; font-weight: 600; text-decoration: none;
    }
    .file-preview svg { width: 13px; height: 13px; }
    .doc-status-pending {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 0.35rem 0.75rem; border-radius: 6px;
        background: rgba(232,168,56,0.15); color: #8A6010;
        font-size: 0.8125rem; font-weight: 600;
    }
</style>

<div>

    {{-- Breadcrumb --}}
    <div class="breadcrumb">
        <a href="{{ route('students.index') }}">Elèves</a>
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="breadcrumb-current">{{ $student->fullName() }}</span>
    </div>

    {{-- Toast succès --}}
    @if ($saved)
        <div class="toast-success" x-data x-init="setTimeout(() => $el.remove(), 3500)">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Modifications enregistrées avec succès.
        </div>
    @endif

    <div class="edit-grid">

        {{-- Colonne gauche : formulaires --}}
        <div>

            {{-- Informations personnelles --}}
            <div class="card">
                <div class="card-header">
                    <div class="card-header-icon" style="background:rgba(42,63,126,0.1); color:var(--sidebar-soft);">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <span class="card-title">Informations personnelles</span>
                </div>
                <div class="card-body">

                    <div class="photo-wrap">
                        <div class="photo-circle">
                            @if ($photo)
                                <img src="{{ $photo->temporaryUrl() }}" alt="">
                            @elseif ($existing_photo)
                                <img src="{{ asset('storage/'.$existing_photo) }}" alt="">
                            @else
                                {{ strtoupper(substr($first_name ?: '?', 0, 1)) }}
                            @endif
                        </div>
                        <label class="photo-upload-btn">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Changer la photo
                            <input wire:model="photo" type="file" accept="image/*">
                        </label>
                        @error('photo') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-field">
                            <label class="form-label">Prénom</label>
                            <input wire:model="first_name" type="text" class="form-input" placeholder="Prénom">
                            @error('first_name') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-field">
                            <label class="form-label">Nom</label>
                            <input wire:model="last_name" type="text" class="form-input" placeholder="Nom de famille">
                            @error('last_name') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-field">
                            <label class="form-label">Matricule</label>
                            <input wire:model="matricule" type="text" class="form-input" placeholder="ELV-001">
                            @error('matricule') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-field">
                            <label class="form-label">Genre</label>
                            <div class="radio-group">
                                <button type="button"
                                    wire:click="$set('gender', 'M')"
                                    class="radio-btn {{ $gender === 'M' ? 'selected-m' : '' }}">
                                    Masculin
                                </button>
                                <button type="button"
                                    wire:click="$set('gender', 'F')"
                                    class="radio-btn {{ $gender === 'F' ? 'selected-f' : '' }}">
                                    Féminin
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-field">
                            <label class="form-label">Date de naissance</label>
                            <input wire:model="birth_date" type="date" class="form-input">
                            @error('birth_date') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-field">
                            <label class="form-label">Lieu de naissance</label>
                            <input wire:model="birth_place" type="text" class="form-input" placeholder="Djibouti">
                            @error('birth_place') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                </div>
            </div>

            {{-- Scolarité --}}
            <div class="card">
                <div class="card-header">
                    <div class="card-header-icon" style="background:rgba(232,168,56,0.12); color:#8A6010;">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                        </svg>
                    </div>
                    <span class="card-title">Scolarité</span>
                </div>
                <div class="card-body">

                    <div class="form-row">
                        <div class="form-field">
                            <label class="form-label">Niveau</label>
                            <select wire:model.live="level_id" class="form-select">
                                <option value="">— Sélectionner un niveau —</option>
                                @foreach ($levels as $level)
                                    <option value="{{ $level->id }}">{{ $level->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field">
                            <label class="form-label">Classe (année active)</label>
                            <select wire:model="school_class_id" class="form-select" @if(!$level_id) disabled @endif>
                                <option value="">— Sélectionner une classe —</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            @error('school_class_id') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-row single">
                        <div class="form-field">
                            <label class="form-label">Statut</label>
                            <select wire:model="status" class="form-select">
                                <option value="active">Inscrit</option>
                                <option value="transferred">Transféré</option>
                                <option value="graduated">Diplômé</option>
                                <option value="dropped">Abandonné</option>
                            </select>
                            @error('status') <span class="form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>

                </div>
            </div>

            {{-- Tuteurs --}}
            <div class="card">
                <div class="card-header">
                    <div class="card-header-icon" style="background:rgba(30,120,80,.08); color:#1A6040;">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <span class="card-title">Tuteurs</span>
                </div>
                <div class="card-body">

                    @error('showAddGuardian') <span class="form-error" style="display:block;margin-bottom:.75rem;">{{ $message }}</span> @enderror

                    @forelse ($studentGuardians as $guardian)
                        <div class="guardian-card">
                            @if ($editingGuardianId === $guardian->id)
                                <div class="form-row">
                                    <div class="form-field">
                                        <label class="form-label">Prénom</label>
                                        <input wire:model="eg_first_name" type="text" class="form-input">
                                        @error('eg_first_name') <span class="form-error">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label">Nom</label>
                                        <input wire:model="eg_last_name" type="text" class="form-input">
                                        @error('eg_last_name') <span class="form-error">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-field">
                                        <label class="form-label">Téléphone</label>
                                        <input wire:model="eg_phone" type="tel" class="form-input">
                                        @error('eg_phone') <span class="form-error">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label">Email</label>
                                        <input wire:model="eg_email" type="email" class="form-input">
                                        @error('eg_email') <span class="form-error">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-field">
                                        <label class="form-label">Profession</label>
                                        <input wire:model="eg_profession" type="text" class="form-input">
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label">Lien de parenté</label>
                                        <select wire:model="eg_relationship" class="form-select">
                                            <option value="pere">Père</option>
                                            <option value="mere">Mère</option>
                                            <option value="tuteur">Tuteur légal</option>
                                            <option value="autre">Autre</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="guardian-actions">
                                    <button wire:click="saveGuardianEdit" class="btn-link">Enregistrer</button>
                                    <button wire:click="cancelEditGuardian" class="btn-link">Annuler</button>
                                </div>
                            @else
                                <div class="guardian-card-head">
                                    <div>
                                        <div class="guardian-name">{{ $guardian->fullName() }}</div>
                                        <div class="guardian-meta">{{ $guardian->phone }}{{ $guardian->email ? ' · '.$guardian->email : '' }}</div>
                                    </div>
                                    <div class="guardian-badges">
                                        @if ($guardian->pivot->is_primary_contact)
                                            <span class="badge badge-primary">Contact principal</span>
                                        @endif
                                        <span class="badge badge-relationship">
                                            {{ match($guardian->pivot->relationship) { 'pere'=>'Père','mere'=>'Mère','tuteur'=>'Tuteur légal',default=>'Autre' } }}
                                        </span>
                                    </div>
                                </div>
                                <div class="guardian-actions">
                                    <button wire:click="startEditGuardian({{ $guardian->id }})" class="btn-link">Modifier</button>
                                    @if (! $guardian->pivot->is_primary_contact)
                                        <button wire:click="makeGuardianPrimary({{ $guardian->id }})" class="btn-link">Définir comme principal</button>
                                    @endif
                                    <button wire:click="removeGuardian({{ $guardian->id }})"
                                            wire:confirm="Retirer {{ $guardian->fullName() }} de cet élève ?"
                                            class="btn-link danger"
                                            @if ($studentGuardians->count() <= 1) disabled @endif>
                                        Retirer
                                    </button>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p style="font-size:.875rem; color:var(--ink); opacity:.5; margin-bottom:1rem;">Aucun tuteur rattaché.</p>
                    @endforelse

                    @if ($showAddGuardian)
                        <div class="guardian-card" style="background:var(--paper); border-style:dashed;">
                            <div class="mode-toggle">
                                <button type="button" wire:click="$set('addGuardianMode','new')" class="mode-btn {{ $addGuardianMode==='new' ? 'active' : '' }}">Nouveau tuteur</button>
                                <button type="button" wire:click="$set('addGuardianMode','existing')" class="mode-btn {{ $addGuardianMode==='existing' ? 'active' : '' }}">Tuteur existant</button>
                            </div>

                            @if ($addGuardianMode === 'existing')
                                <div class="form-row single">
                                    <div class="form-field">
                                        <label class="form-label">Tuteur</label>
                                        <select wire:model="ag_existing_id" class="form-select">
                                            <option value="">— Sélectionner —</option>
                                            @foreach ($availableGuardians as $g)
                                                <option value="{{ $g->id }}">{{ $g->fullName() }} — {{ $g->phone }}</option>
                                            @endforeach
                                        </select>
                                        @error('ag_existing_id') <span class="form-error">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            @else
                                <div class="form-row">
                                    <div class="form-field">
                                        <label class="form-label">Prénom</label>
                                        <input wire:model="ag_first_name" type="text" class="form-input">
                                        @error('ag_first_name') <span class="form-error">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label">Nom</label>
                                        <input wire:model="ag_last_name" type="text" class="form-input">
                                        @error('ag_last_name') <span class="form-error">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-field">
                                        <label class="form-label">Téléphone</label>
                                        <input wire:model="ag_phone" type="tel" class="form-input" placeholder="77 00 00 00">
                                        @error('ag_phone') <span class="form-error">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-field">
                                        <label class="form-label">Email</label>
                                        <input wire:model="ag_email" type="email" class="form-input">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-field">
                                        <label class="form-label">Profession</label>
                                        <input wire:model="ag_profession" type="text" class="form-input">
                                    </div>
                                </div>
                            @endif

                            <div class="form-row single">
                                <div class="form-field">
                                    <label class="form-label">Lien de parenté</label>
                                    <select wire:model="ag_relationship" class="form-select">
                                        <option value="pere">Père</option>
                                        <option value="mere">Mère</option>
                                        <option value="tuteur">Tuteur légal</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>
                            </div>

                            <div class="guardian-actions">
                                <button wire:click="addGuardian" class="btn-link">Ajouter</button>
                                <button wire:click="toggleAddGuardian" class="btn-link">Annuler</button>
                            </div>
                        </div>
                    @else
                        <button type="button" wire:click="toggleAddGuardian" class="btn-cancel" style="margin-top:.25rem;">
                            + Ajouter un tuteur
                        </button>
                    @endif

                </div>
            </div>

            {{-- Documents --}}
            <div class="card">
                <div class="card-header">
                    <div class="card-header-icon" style="background:rgba(99,102,241,.1); color:#3730A3;">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    </div>
                    <span class="card-title">Pièces à fournir</span>
                </div>
                <div class="card-body">
                    @if (! $level_id)
                        <div style="text-align:center;padding:1.5rem;font-size:.875rem;color:var(--ink);opacity:.45;">
                            Sélectionne un niveau pour voir les pièces requises.
                        </div>
                    @elseif ($requiredDocs->isEmpty())
                        <div style="text-align:center;padding:1.5rem;font-size:.875rem;color:var(--ink);opacity:.45;">
                            Aucune pièce requise configurée pour ce niveau.
                            <a href="{{ route('school-config.admission') }}" style="color:var(--sidebar-soft);">Configurer →</a>
                        </div>
                    @else
                        <div class="doc-list">
                            @foreach ($requiredDocs as $doc)
                                @php $existingDoc = $existingDocs->get($doc->id); @endphp
                                <div class="doc-item">
                                    <div class="doc-item-header">
                                        <div class="doc-icon" style="background:rgba(42,63,126,.08);color:var(--sidebar-soft);">
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        </div>
                                        <div>
                                            <div class="doc-item-name">{{ $doc->name }}</div>
                                            @if ($doc->description)
                                                <div class="doc-item-desc">{{ $doc->description }}</div>
                                            @endif
                                        </div>
                                        <span class="{{ $doc->is_mandatory ? 'badge-required' : 'badge-optional' }}">
                                            {{ $doc->is_mandatory ? 'Obligatoire' : 'Optionnel' }}
                                        </span>
                                    </div>
                                    <div class="doc-item-body">
                                        <div class="upload-row">
                                            <label class="upload-label">
                                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                                {{ $existingDoc ? 'Remplacer' : 'Joindre le document' }}
                                                <input wire:model="docFiles.{{ $doc->id }}"
                                                       type="file"
                                                       accept=".pdf,.jpg,.jpeg,.png,.webp">
                                            </label>
                                            @if (isset($this->docFiles[$doc->id]) && $this->docFiles[$doc->id])
                                                <span class="file-preview">
                                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    Nouveau fichier prêt
                                                </span>
                                            @elseif ($existingDoc && $existingDoc->status === 'provided')
                                                <a href="{{ $existingDoc->fileUrl() }}" target="_blank" class="file-preview">
                                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    Voir le document fourni
                                                </a>
                                            @else
                                                <span class="doc-status-pending">En attente</span>
                                            @endif
                                        </div>
                                        @error('docFiles.'.$doc->id) <span class="form-error">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('students.index') }}" class="btn-cancel">Annuler</a>
                <button wire:click="save" class="btn-save">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    Enregistrer
                </button>
            </div>

        </div>

        {{-- Colonne droite : profil + danger zone --}}
        <div>

            {{-- Carte profil --}}
            <div class="profile-card">
                <div class="profile-avatar-wrap">
                    <div class="profile-avatar">
                        {{ strtoupper(substr($student->first_name,0,1).substr($student->last_name,0,1)) }}
                    </div>
                    <div class="profile-name">{{ $first_name }} {{ $last_name }}</div>
                    <div class="profile-matric">{{ $student->matricule }}</div>
                </div>
                <div class="profile-meta">
                    <div class="meta-row">
                        <span class="meta-label">Statut</span>
                        @php
                            $badgeClass = match($status) {
                                'active'      => 'badge-active',
                                'transferred' => 'badge-transferred',
                                'graduated'   => 'badge-graduated',
                                'dropped'     => 'badge-dropped',
                                default       => 'badge-active',
                            };
                            $statusLabel = match($status) {
                                'active'      => 'Inscrit',
                                'transferred' => 'Transféré',
                                'graduated'   => 'Diplômé',
                                'dropped'     => 'Abandonné',
                                default       => $status,
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Genre</span>
                        <span class="meta-value">{{ $gender === 'M' ? 'Masculin' : ($gender === 'F' ? 'Féminin' : '—') }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Naissance</span>
                        <span class="meta-value">{{ $birth_date ? \Carbon\Carbon::parse($birth_date)->format('d/m/Y') : '—' }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Inscrit le</span>
                        <span class="meta-value">{{ $student->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Danger zone --}}
            <div class="danger-zone">
                <div class="danger-header">Zone de danger</div>
                <div class="danger-body">
                    <p class="danger-desc">La suppression d'un élève est irréversible et effacera toutes ses données (notes, présences, bulletins).</p>
                    <button class="btn-danger">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Supprimer cet élève
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>