<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::guard('web')->user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'กรุณาเข้าสู่ระบบก่อน');
        }

        // แปลง role names เป็น role codes
        $roleMap = [
            'admin' => 32768,      // Admin
            'coordinator' => 16384, // Coordinator
            'lecturer' => 8192,     // Lecturer
            'staff' => 4096,        // Staff
        ];

        $allowedRoleCodes = [];
        foreach ($roles as $role) {
            if (isset($roleMap[$role])) {
                $allowedRoleCodes[] = $roleMap[$role];
            }
        }

        // ตรวจสอบว่า user มี role ที่อนุญาตหรือไม่ (ใช้ bitwise AND)
        $hasPermission = false;
        foreach ($allowedRoleCodes as $roleCode) {
            if (($user->role & $roleCode) === $roleCode) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        return $next($request);
    }
}
