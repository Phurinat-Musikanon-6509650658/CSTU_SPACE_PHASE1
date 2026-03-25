<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectEvaluation extends Model
{
    use HasFactory;

    protected $table = 'project_evaluations';
    protected $primaryKey = 'evaluation_id';

    protected $fillable = [
        'project_id',
        'student_id',
        'evaluator_code',
        'evaluator_role',
        'part1_score',
        'part2_score',
        'part3_score',
        'total_score',
        'comments',
        'submitted_at'
    ];

    protected $casts = [
        'part1_score' => 'decimal:2',
        'part2_score' => 'decimal:2',
        'part3_score' => 'decimal:2',
        'total_score' => 'decimal:2',
        'submitted_at' => 'datetime'
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_code', 'user_code');
    }

    // Auto-calculate total_score before saving
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($evaluation) {
            // Part 1 (10 points) - Advisor only
            if ($evaluation->evaluator_role === 'advisor') {
                $evaluation->total_score = ($evaluation->part1_score ?? 0) + ($evaluation->part2_score ?? 0) + ($evaluation->part3_score ?? 0);
            } else {
                // Part 2+3 (90 points) - Committee members don't have part1
                $evaluation->total_score = ($evaluation->part2_score ?? 0) + ($evaluation->part3_score ?? 0);
            }
        });
    }

    // Helper: Check if scores are valid
    public function hasValidScores()
    {
        if ($this->evaluator_role === 'advisor') {
            // Advisor: part1 (0-10) + part2 (0-30) + part3 (0-60) = 0-100
            return ($this->part1_score ?? 0) >= 0 && ($this->part1_score ?? 0) <= 10
                && ($this->part2_score ?? 0) >= 0 && ($this->part2_score ?? 0) <= 30
                && ($this->part3_score ?? 0) >= 0 && ($this->part3_score ?? 0) <= 60;
        } else {
            // Committee: part2 (0-30) + part3 (0-60) = 0-90
            return ($this->part2_score ?? 0) >= 0 && ($this->part2_score ?? 0) <= 30
                && ($this->part3_score ?? 0) >= 0 && ($this->part3_score ?? 0) <= 60;
        }
    }
}
