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

    // Helper: คำนวณเกรดจากคะแนน
    public static function calculateGrade($score)
    {
        if ($score >= 80) return 'A';
        if ($score >= 75) return 'B+';
        if ($score >= 70) return 'B';
        if ($score >= 65) return 'C+';
        if ($score >= 60) return 'C';
        if ($score >= 55) return 'D+';
        if ($score >= 50) return 'D';
        return 'F';
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
        if ($this->advisor_confirmed && in_array('advisor', $required)) $confirmed[] = 'advisor';
        if ($this->committee1_confirmed && in_array('committee1', $required)) $confirmed[] = 'committee1';
        if ($this->committee2_confirmed && in_array('committee2', $required)) $confirmed[] = 'committee2';
        if ($this->committee3_confirmed && in_array('committee3', $required)) $confirmed[] = 'committee3';
        
        return count($required) === count($confirmed);
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
