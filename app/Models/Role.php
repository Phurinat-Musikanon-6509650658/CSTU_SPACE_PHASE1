<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'role',
        'role_code',
        'role_code_bin'
    ];

    protected $casts = [
        'role_code_bin' => 'integer'
    ];
}
