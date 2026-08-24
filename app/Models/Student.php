<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\AcademicYearService;
use Illuminate\Database\Eloquent\Builder;
use App\Services\AccessService;

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
    // Parent : ses enfants uniquement (cas non couvert par AccessService)
    if ($user->hasRole('parent')) {
        return $query->whereHas('guardians', fn ($q) =>
            $q->where('guardians.user_id', $user->id)
        );
    }

    // Sinon, on s'appuie sur AccessService
    $classIds = \App\Services\AccessService::myClassIds($user);

    // null = accès total (admin, directeur, comptable, surveillant)
    if ($classIds === null) {
        return $query;
    }

    // Liste vide ou classes précises
    $year = AcademicYearService::current();

    return $query->whereHas('schoolYears', fn ($q) =>
        $q->whereIn('school_class_id', $classIds)
          ->when($year, fn ($q) => $q->where('academic_year_id', $year->id))
    );
}
}
