<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_class_id', 'subject_id', 'evaluation_plan_id', 'type', 'period', 'date', 'max_score',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function plan()
    {
        return $this->belongsTo(EvaluationPlan::class, 'evaluation_plan_id');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
}
