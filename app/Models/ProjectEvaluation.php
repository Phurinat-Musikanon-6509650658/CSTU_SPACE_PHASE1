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
        'part3a_score',
        'part3b_score',
        'part3c_score',
        'total_score',
        'comments',
        'submitted_at'
    ];

    protected $casts = [
        'part1_score'  => 'decimal:2',
        'part2_score'  => 'decimal:2',
        'part3_score'  => 'decimal:2',
        'part3a_score' => 'decimal:2',
        'part3b_score' => 'decimal:2',
        'part3c_score' => 'decimal:2',
        'total_score'  => 'decimal:2',
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
            // part3_score = sum of sub-criteria (each 0-20)
            $evaluation->part3_score = ($evaluation->part3a_score ?? 0)
                + ($evaluation->part3b_score ?? 0)
                + ($evaluation->part3c_score ?? 0);

            if ($evaluation->evaluator_role === 'advisor') {
                $evaluation->total_score = ($evaluation->part1_score ?? 0)
                    + ($evaluation->part2_score ?? 0)
                    + $evaluation->part3_score;
            } else {
                $evaluation->total_score = ($evaluation->part2_score ?? 0)
                    + $evaluation->part3_score;
            }
        });
    }

    // Helper: Check if scores are valid
    public function hasValidScores()
    {
        $p2  = $this->part2_score  ?? 0;
        $p3a = $this->part3a_score ?? 0;
        $p3b = $this->part3b_score ?? 0;
        $p3c = $this->part3c_score ?? 0;

        $commonValid = $p2 >= 0 && $p2 <= 30
            && $p3a >= 0 && $p3a <= 20
            && $p3b >= 0 && $p3b <= 20
            && $p3c >= 0 && $p3c <= 20;

        if ($this->evaluator_role === 'advisor') {
            $p1 = $this->part1_score ?? 0;
            return $commonValid && $p1 >= 0 && $p1 <= 10;
        }

        return $commonValid;
    }
}
