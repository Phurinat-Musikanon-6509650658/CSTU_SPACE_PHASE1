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
        'evaluator_code',
        'evaluator_role',
        'document_score',
        'presentation_score',
        'total_score',
        'comments',
        'submitted_at'
    ];

    protected $casts = [
        'document_score' => 'decimal:2',
        'presentation_score' => 'decimal:2',
        'total_score' => 'decimal:2',
        'submitted_at' => 'datetime'
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
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
            $evaluation->total_score = ($evaluation->document_score ?? 0) + ($evaluation->presentation_score ?? 0);
        });
    }

    // Helper: Check if scores are valid
    public function hasValidScores()
    {
        return $this->document_score >= 0 && $this->document_score <= 30
            && $this->presentation_score >= 0 && $this->presentation_score <= 70;
    }
}
