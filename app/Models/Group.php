<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'groups';
    protected $primaryKey = 'group_id';
    
    protected $fillable = [
        'project_name',
        'project_code',
        'subject_code',
        'year',
        'semester',
        'status_group',
        'description'
    ];

    protected $casts = [
        'year' => 'integer',
        'semester' => 'integer',
    ];

    // Relationships
    public function members()
    {
        return $this->hasMany(GroupMember::class, 'group_id', 'group_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'group_members', 'group_id', 'username_std', 'group_id', 'username_std');
    }

    public function invitations()
    {
        return $this->hasMany(GroupInvitation::class, 'group_id', 'group_id');
    }

    public function pendingInvitations()
    {
        return $this->invitations()->where('status', 'pending');
    }

    // Helper methods
    public function getMemberCount()
    {
        return $this->members()->count();
    }

    public function canAddMember()
    {
        return $this->getMemberCount() < 2;
    }

    public function getCreator()
    {
        return $this->members()->first()?->student;
    }
}
