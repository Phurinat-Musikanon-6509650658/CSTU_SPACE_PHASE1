<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class GroupInvitation extends Model
{
    protected $table = 'group_invitations';
    protected $primaryKey = 'invitation_id';
    
    protected $fillable = [
        'group_id',
        'inviter_username',
        'invitee_username',
        'status',
        'message',
        'responded_at'
    ];

    protected $casts = [
        'responded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DECLINED = 'declined';

    // Relationships
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'group_id');
    }

    public function inviter()
    {
        return $this->belongsTo(Student::class, 'inviter_username', 'username_std');
    }

    public function invitee()
    {
        return $this->belongsTo(Student::class, 'invitee_username', 'username_std');
    }

    // Helper methods
    public function accept()
    {
        $this->update([
            'status' => self::STATUS_ACCEPTED,
            'responded_at' => Carbon::now()
        ]);
    }

    public function decline()
    {
        $this->update([
            'status' => self::STATUS_DECLINED,
            'responded_at' => Carbon::now()
        ]);
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAccepted()
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isDeclined()
    {
        return $this->status === self::STATUS_DECLINED;
    }
}
