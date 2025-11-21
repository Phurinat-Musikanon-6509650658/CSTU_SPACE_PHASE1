<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use Notifiable;

    protected $table = 'student';
    protected $primaryKey = 'student_id';
    
    protected $fillable = [
        'firstname_std',
        'lastname_std',
        'email_std',
        'username_std',
        'password_std',
        'role'
    ];

    protected $hidden = [
        'password_std',
    ];

    // Override auth field names
    public function getAuthIdentifierName()
    {
        return 'username_std';
    }

    public function getAuthPassword()
    {
        return $this->password_std;
    }

    // Relationships
    public function groupMembers()
    {
        return $this->hasMany(GroupMember::class, 'username_std', 'username_std');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_members', 'username_std', 'group_id', 'username_std', 'group_id');
    }

    public function sentInvitations()
    {
        return $this->hasMany(GroupInvitation::class, 'inviter_username', 'username_std');
    }

    public function receivedInvitations()
    {
        return $this->hasMany(GroupInvitation::class, 'invitee_username', 'username_std');
    }

    public function pendingInvitations()
    {
        return $this->receivedInvitations()->where('status', 'pending');
    }

    // Helper methods
    public function getFullNameAttribute()
    {
        return $this->firstname_std . ' ' . $this->lastname_std;
    }

    public function hasGroup()
    {
        return $this->groups()->exists();
    }

    public function canCreateGroup()
    {
        return !$this->hasGroup();
    }

    public function canJoinGroup()
    {
        return !$this->hasGroup();
    }

    /**
     * Get the role that the student belongs to
     */
    public function roleData()
    {
        return $this->belongsTo(Role::class, 'role', 'role');
    }
}
