<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSchedule extends Model
{
    protected $table = 'exam_schedule';
    protected $primaryKey = 'ex_id';
    
    protected $fillable = [
        'project_id',
        'ex_start_time',
        'ex_end_time',
        'location',
        'notes',
    ];
    
    protected $casts = [
        'ex_start_time' => 'datetime',
        'ex_end_time' => 'datetime',
    ];
    
    /**
     * Relationship: ExamSchedule belongs to Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }
}
