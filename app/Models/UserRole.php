<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table = 'user_role';
    protected $primaryKey = 'role_id';
    
    protected $fillable = [
        'role_name',
        'role_code',
        'role_code_bin'
    ];

    protected $casts = [
        'role_code' => 'integer',
        'role_code_bin' => 'integer'
    ];

    /**
     * Get users with this role
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role', 'role_code');
    }
}
