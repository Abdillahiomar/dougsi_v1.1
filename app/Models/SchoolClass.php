<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SchoolClass extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'school_classes';

    protected $fillable = [
        'school_id', 'academic_year_id', 'level_id', 'name',
        'main_teacher_id', 'capacity',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function mainTeacher()
    {
        return $this->belongsTo(Staff::class, 'main_teacher_id');
    }

    public function classSubjects()
    {
        return $this->hasMany(ClassSubjectTeacher::class);
    }

    public function studentSchoolYears()
    {
        return $this->hasMany(StudentSchoolYear::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function scopeVisibleTo(Builder $query, $user): Builder
    {
        // Admin / comptable : toutes les classes de l'école
        if ($user->hasRole(['admin', 'comptable'])) {
            return $query;
        }

        // Enseignant : ses classes (prof principal OU matière enseignée)
        if ($user->hasRole('enseignant')) {
            $staffId = Staff::where('user_id', $user->id)->value('id');

            if (! $staffId) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where(function ($q) use ($staffId) {
                $q->where('main_teacher_id', $staffId)
                ->orWhereIn('id',
                    ClassSubjectTeacher::where('staff_id', $staffId)
                        ->pluck('school_class_id')
                );
            });
        }

        // Parent : les classes où sont inscrits ses enfants
        if ($user->hasRole('parent')) {
            return $query->whereHas('studentSchoolYears.student.guardians', fn ($q) =>
                $q->where('guardians.user_id', $user->id)
            );
        }

        return $query->whereRaw('1 = 0');
    }
}
