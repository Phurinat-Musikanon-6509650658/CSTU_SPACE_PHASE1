<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentGrade extends Model
{
    use HasFactory;

    protected $table = 'student_grades';

    protected $fillable = [
        'project_id',
        'student_id',
        'final_score',
        'grade',
        'released',
        'released_at'
    ];

    protected $casts = [
        'final_score' => 'decimal:2',
        'released' => 'boolean',
        'released_at' => 'datetime'
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

    // Calculate grade letter from score
    public static function calculateGrade($score)
    {
        if ($score >= 90) return 'A';
        elseif ($score >= 85) return 'B+';
        elseif ($score >= 80) return 'B';
        elseif ($score >= 75) return 'B-';
        elseif ($score >= 70) return 'C+';
        elseif ($score >= 65) return 'C';
        elseif ($score >= 60) return 'C-';
        elseif ($score >= 55) return 'D+';
        elseif ($score >= 50) return 'D';
        else return 'F';
    }
}
