<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectLecturer extends Model
{
    protected $table = 'project_lecturers';

    protected $fillable = [
        'project_id',
        'user_code',
        'relationship_id',
        'sort_order',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_code', 'user_code');
    }

    public function relationship()
    {
        return $this->belongsTo(RelationshipWithProject::class, 'relationship_id');
    }
}
