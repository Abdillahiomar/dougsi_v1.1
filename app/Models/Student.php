<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\AcademicYearService;
use Illuminate\Database\Eloquent\Builder;

class Student extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'school_id', 'matricule', 'first_name', 'last_name', 'birth_date',
        'birth_place', 'gender', 'photo_path', 'status',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function schoolYears()
    {
        return $this->hasMany(StudentSchoolYear::class);
    }

    public function currentSchoolYear()
    {
        return $this->hasOne(StudentSchoolYear::class)
            ->whereHas('academicYear', fn ($q) => $q->where('is_active', true));
    }

    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'student_guardian')
            ->withPivot('relationship', 'is_primary_contact')
            ->withTimestamps();
    }

    public function fullName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function scopeVisibleTo(Builder $query, $user): Builder
    {
        // Admin / comptable : aucun filtre (le tenant school_id suffit)
        if ($user->hasRole(['admin', 'comptable'])) {
            return $query;
        }

        // Parent : uniquement ses enfants
        if ($user->hasRole('parent')) {
            return $query->whereHas('guardians', fn ($q) =>
                $q->where('guardians.user_id', $user->id)
            );
        }

        // Enseignant : élèves de ses classes (prof principal OU matière enseignée)
        if ($user->hasRole('enseignant')) {
            $staffId = Staff::where('user_id', $user->id)->value('id');

            if (! $staffId) {
                return $query->whereRaw('1 = 0');
            }

            $classIds = SchoolClass::query()
                ->where('main_teacher_id', $staffId)
                ->orWhereIn('id',
                    ClassSubjectTeacher::where('staff_id', $staffId)
                        ->pluck('school_class_id')
                )
                ->pluck('id');

            $year = AcademicYearService::current();

            return $query->whereHas('schoolYears', fn ($q) =>
                $q->whereIn('school_class_id', $classIds)
                ->when($year, fn ($q) => $q->where('academic_year_id', $year->id))
            );
        }

        // Rôle inconnu : ne rien montrer (échouer fermé)
        return $query->whereRaw('1 = 0');
    }
}
