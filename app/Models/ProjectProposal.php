<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectProposal extends Model
{
    protected $table = 'project_proposals';
    protected $primaryKey = 'proposal_id';
    
    protected $fillable = [
        'group_id',
        'proposed_title',
        'description',
        'proposed_to',
        'proposed_by',
        'status',
        'rejection_reason',
        'proposed_at',
        'responded_at'
    ];
    
    protected $casts = [
        'proposed_at' => 'datetime',
        'responded_at' => 'datetime',
    ];
    
    // Relationships
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }
    
    public function lecturer()
    {
        return $this->belongsTo(User::class, 'proposed_to', 'username_user');
    }
    
    public function student()
    {
        return $this->belongsTo(Student::class, 'proposed_by', 'username_std');
    }
}
