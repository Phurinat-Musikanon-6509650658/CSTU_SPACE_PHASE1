<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;
use App\Models\Role;

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

        // ดึง role จาก session (ใช้ department เป็น role)
        $userRole = Session::get('department', 'student');
        
        // ดึงข้อมูล role จากฐานข้อมูล
        $role = Role::where('role', 'LIKE', "%{$userRole}%")->first();
        
        if (!$role) {
            return redirect()->route('menu')->with('error', 'ไม่พบข้อมูลสิทธิ์การเข้าถึง');
        }

        // เช็ค permission โดยใช้ binary operation
        // ถ้าตัวเลขที่แสดง (role_code) ไม่ตรงกับ binary ที่คำนวณจาก role_code_bin จะไม่มีสิทธิ์
        $calculatedFromBinary = $role->role_code_bin;
        $displayedNumber = $role->role_code;
        
        // เช็คว่าตัวเลขที่แสดงตรงกับที่คำนวณจาก binary หรือไม่
        if ($displayedNumber !== $calculatedFromBinary) {
            return redirect()->route('menu')->with('error', 'การยืนยันสิทธิ์ไม่ถูกต้อง (Binary mismatch)');
        }
        
        // เช็คว่ามีสิทธิ์เข้าถึงหน้านี้หรือไม่โดยใช้ bitwise AND
        if (($displayedNumber & $requiredPermission) === 0) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        return $next($request);
    }
}
