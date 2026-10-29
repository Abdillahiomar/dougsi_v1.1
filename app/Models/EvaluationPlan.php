<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationPlan extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'school_id', 'level_id', 'subject_id', 'period', 'label', 'max_score', 'order',
    ];

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }
}
