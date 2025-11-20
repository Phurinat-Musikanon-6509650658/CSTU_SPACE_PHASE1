<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    protected $table = 'group_members';
    protected $primaryKey = 'groupmem_id';
    
    protected $fillable = [
        'group_id',
        'username_std'
    ];

    // Relationships
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'username_std', 'username_std');
    }
}
