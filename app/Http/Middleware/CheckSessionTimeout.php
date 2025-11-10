<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionTimeout
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ตรวจสอบเฉพาะเมื่อมี session login
        if (Session::has('displayname') && Session::has('last_activity')) {
            $lastActivity = Session::get('last_activity');
            $sessionTimeout = 15 * 60; // 15 นาที
            
            // ถ้าเกิน timeout
            if (time() - $lastActivity > $sessionTimeout) {
                Session::flush();
                
                // ถ้าเป็น AJAX request
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 'session_expired',
                        'message' => 'Your session has expired. Please login again.',
                        'redirect' => route('login')
                    ], 401);
                }
                
                return redirect()->route('login')->with('error', 'Session หมดอายุ กรุณา login ใหม่');
            }
            
            // อัพเดท last activity
            Session::put('last_activity', time());
        }

        return $next($request);
    }
}
