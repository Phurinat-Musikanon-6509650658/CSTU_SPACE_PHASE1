<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Explicit table name and primary key to match existing migrations.
     * The project's migrations create `user` (singular) with primary key `user_id`.
     */
    protected $table = 'user';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'displayname',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return ($this->role & 32768) === 32768; // Admin role_code
    }

    /**
     * Check if user is coordinator
     */
    public function isCoordinator(): bool
    {
        return ($this->role & 16384) === 16384; // Coordinator role_code
    }

    /**
     * Check if user is advisor (lecturer)
     */
    public function isAdvisor(): bool
    {
        return ($this->role & 8192) === 8192; // Lecturer role_code
    }

    /**
     * Check if user is student
     */
    public function isStudent(): bool
    {
        return ($this->role & 2048) === 2048; // Student role_code
    }

    /**
     * Check if user has specific role (by role_code)
     */
    public function hasRole(int $roleCode): bool
    {
        return ($this->role & $roleCode) === $roleCode;
    }

    /**
     * Check if user has any of the given roles (by role_codes)
     */
    public function hasAnyRole(array $roleCodes): bool
    {
        foreach ($roleCodes as $roleCode) {
            if (($this->role & $roleCode) === $roleCode) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get user role information from user_role table
     */
    public function userRole()
    {
        return $this->belongsTo(UserRole::class, 'role', 'role_code');
    }

    /**
     * Get role name
     */
    public function getRoleName(): string
    {
        $userRole = $this->userRole;
        return $userRole ? $userRole->role_name : 'Unknown';
    }

    /**
     * Get the role that the user belongs to
     */
    public function roleData()
    {
        return $this->belongsTo(Role::class, 'role', 'role');
    }
}
