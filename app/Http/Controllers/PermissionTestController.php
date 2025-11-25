<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionTestController extends Controller
{
    public function testPermission()
    {
        $user = Auth::guard('web')->user();
        
        if (!$user) {
            return response()->json([
                'error' => 'Not authenticated',
                'message' => 'Please login first'
            ], 401);
        }

        $roleNames = [
            32768 => 'Admin',
            16384 => 'Coordinator',
            8192 => 'Lecturer',
            4096 => 'Staff',
        ];

        return response()->json([
            'user' => [
                'username' => $user->username_user,
                'name' => $user->firstname_user . ' ' . $user->lastname_user,
                'role_code' => $user->role,
                'role_name' => $roleNames[$user->role] ?? 'Unknown',
            ],
            'permissions' => [
                'can_access_admin' => $user->role === 32768,
                'can_access_coordinator' => in_array($user->role, [32768, 16384]),
                'can_access_lecturer' => in_array($user->role, [32768, 8192]),
            ],
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
