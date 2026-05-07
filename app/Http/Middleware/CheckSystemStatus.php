<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use App\Helpers\PermissionHelper;

class CheckSystemStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // ให้ admin ผ่านได้เสมอ
        if (PermissionHelper::isAdmin()) {
            return $next($request);
        }

        // ตรวจสอบสถานะระบบ (ถ้า table ยังไม่มีให้ถือว่าระบบเปิด)
        try {
            if (!SystemSetting::isSystemOpen()) {
                return response()->view('errors.system_closed', [], 503);
            }
        } catch (\Exception $e) {
            // system_settings table ยังไม่ถูกสร้าง → ถือว่าระบบเปิดปกติ
        }

        return $next($request);
    }
}
