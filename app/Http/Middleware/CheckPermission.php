<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;
use App\Models\UserRole;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  int $requiredPermission - ตัวเลขที่ต้องการ (เช่น 32768 สำหรับ admin)
     */
    public function handle(Request $request, Closure $next, int $requiredPermission): Response
    {
        // ตรวจสอบการ login
        if (!Session::has('displayname')) {
            return redirect()->route('login')->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        // ดึง role_code จาก session
        $roleCode = Session::get('role_code', 0);
        
        if (!$roleCode) {
            return redirect()->route('menu')->with('error', 'ไม่พบข้อมูลสิทธิ์การเข้าถึง');
        }

        // เช็คว่ามีสิทธิ์เข้าถึงหน้านี้หรือไม่โดยใช้ bitwise AND
        if (($roleCode & $requiredPermission) === 0) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        return $next($request);
    }
}
