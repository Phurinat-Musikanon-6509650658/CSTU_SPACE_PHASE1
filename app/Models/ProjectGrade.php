<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectGrade extends Model
{
    use HasFactory;

    protected $table = 'project_grades';
    protected $primaryKey = 'grade_id';

    protected $fillable = [
        'project_id',
        'final_score',
        'grade',
        'advisor_confirmed',
        'advisor_confirmed_at',
        'committee1_confirmed',
        'committee1_confirmed_at',
        'committee2_confirmed',
        'committee2_confirmed_at',
        'committee3_confirmed',
        'committee3_confirmed_at',
        'all_confirmed',
        'all_confirmed_at',
        'grade_released',
        'grade_released_at'
    ];

    protected $casts = [
        'final_score' => 'decimal:2',
        'advisor_confirmed' => 'boolean',
        'committee1_confirmed' => 'boolean',
        'committee2_confirmed' => 'boolean',
        'committee3_confirmed' => 'boolean',
        'all_confirmed' => 'boolean',
        'grade_released' => 'boolean',
        'advisor_confirmed_at' => 'datetime',
        'committee1_confirmed_at' => 'datetime',
        'committee2_confirmed_at' => 'datetime',
        'committee3_confirmed_at' => 'datetime',
        'all_confirmed_at' => 'datetime',
        'grade_released_at' => 'datetime'
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    // Helper: คำนวณเกรดจากคะแนน (ตามเกณฑ์ใหม่)
    public static function calculateGrade($score)
    {
        if ($score >= 90) return 'A';      // 90-100
        if ($score >= 85) return 'B+';     // 85-89
        if ($score >= 80) return 'B';      // 80-84
        if ($score >= 70) return 'C+';     // 70-79
        if ($score >= 60) return 'C';      // 60-69
        if ($score >= 55) return 'D+';     // 55-59
        if ($score >= 50) return 'D';      // 50-54
        return 'F';                         // 0-49
    }

    // Calculator: คำนวณ final score จาก evaluations
    // ส่วน 1: 10 คะแนน (advisor เท่านั้น)
    // ส่วน 2-3: 90 คะแนน (advisor + committee, หาค่าเฉลี่ย)
    public function calculateFinalScore()
    {
        $project = $this->project;
        
        // Get all evaluations for this project
        $evaluations = ProjectEvaluation::where('project_id', $this->project_id)
            ->orWhere('submitted_at', '!=', null)
            ->get();

        if ($evaluations->isEmpty()) {
            return 0;
        }

        $advisorEval = $evaluations->where('evaluator_role', 'advisor')->first();
        $committeeEvals = $evaluations->whereIn('evaluator_role', ['committee1', 'committee2', 'committee3']);

        $part1Score = 0;
        $part23Sum = 0;
        $part23Count = 0;

        // Part 1: From advisor only (10 points)
        if ($advisorEval) {
            $part1Score = $advisorEval->part1_score ?? 0;
        }

        // Part 2+3: Average from advisor + all committees (90 points)
        if ($advisorEval) {
            $part23Sum += ($advisorEval->part2_score ?? 0) + ($advisorEval->part3_score ?? 0);
            $part23Count++;
        }

        foreach ($committeeEvals as $eval) {
            $part23Sum += ($eval->part2_score ?? 0) + ($eval->part3_score ?? 0);
            $part23Count++;
        }

        $part23Average = $part23Count > 0 ? ($part23Sum / $part23Count) : 0;

        // Final score = part1 + average(part2+3)
        return $part1Score + $part23Average;
    }

    // Helper: ตรวจสอบว่า committee ให้คะแนนครบหรือยัง
    public function allCommitteesSubmitted()
    {
        $project = $this->project;
        $requiredRoles = ['advisor'];
        
        if ($project->committee1_code) $requiredRoles[] = 'committee1';
        if ($project->committee2_code) $requiredRoles[] = 'committee2';
        if ($project->committee3_code) $requiredRoles[] = 'committee3';
        
        // Check if all required roles have submitted evaluations
        $submittedRoles = ProjectEvaluation::where('project_id', $this->project_id)
            ->whereNotNull('submitted_at')
            ->pluck('evaluator_role')
            ->toArray();
        
        return count(array_intersect($requiredRoles, $submittedRoles)) === count($requiredRoles);
    }

    // Helper: ตรวจสอบว่ายืนยันครบหรือยัง
    public function checkAllConfirmed()
    {
        $project = $this->project;
        
        $required = [];
        if ($project->advisor_code) $required[] = 'advisor';
        if ($project->committee1_code) $required[] = 'committee1';
        if ($project->committee2_code) $required[] = 'committee2';
        if ($project->committee3_code) $required[] = 'committee3';
        
        $confirmed = [];
        if ($this->advisor_confirmed) $confirmed[] = 'advisor';
        if ($this->committee1_confirmed) $confirmed[] = 'committee1';
        if ($this->committee2_confirmed) $confirmed[] = 'committee2';
        if ($this->committee3_confirmed) $confirmed[] = 'committee3';
        
        return count($required) === count($confirmed) && count($required) > 0;
    }

    // Auto-update all_confirmed when saving
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($grade) {
            if ($grade->checkAllConfirmed()) {
                $grade->all_confirmed = true;
                if (!$grade->all_confirmed_at) {
                    $grade->all_confirmed_at = now();
                }
            } else {
                $grade->all_confirmed = false;
                $grade->all_confirmed_at = null;
            }
        });
    }
}
