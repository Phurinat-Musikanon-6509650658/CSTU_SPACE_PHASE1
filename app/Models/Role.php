<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'role';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'role',
        'role_code',
        'role_code_bin'
    ];

    protected $casts = [
        'role_code_bin' => 'integer'
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class, 'role', 'role');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'role', 'role');
    }
}
