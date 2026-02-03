<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelationshipWithProject extends Model
{
    protected $table = 'relationship_with_projects';

    protected $fillable = [
        'relationship',
        'relationship_abbrev',
    ];

    /**
     * Get projects where this relationship is used as advisor
     */
    public function advisorProjects()
    {
        return $this->hasMany(Project::class, 'advisor_id');
    }

    /**
     * Get projects where this relationship is used as committee 1
     */
    public function comm1Projects()
    {
        return $this->hasMany(Project::class, 'comm1_id');
    }

    /**
     * Get projects where this relationship is used as committee 2
     */
    public function comm2Projects()
    {
        return $this->hasMany(Project::class, 'comm2_id');
    }

    /**
     * Get projects where this relationship is used as committee 3
     */
    public function comm3Projects()
    {
        return $this->hasMany(Project::class, 'comm3_id');
    }
}
