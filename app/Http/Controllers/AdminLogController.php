<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoginLog;
use App\Models\UserRole;
use App\Helpers\PermissionHelper;
use Session;
use Carbon\Carbon;

class AdminLogController extends Controller
{
    public function index(Request $request)
    {
        // เริ่มต้น query
        $query = LoginLog::query();

        // Admin เห็นทุก log, Coordinator/Lecturer/Staff เห็นเฉพาะของตัวเอง
        if (!PermissionHelper::canViewAllData()) {
            if (PermissionHelper::canManageRoles()) {
                $username = PermissionHelper::getCurrentUsername();
                $query->where('username', $username);
            } else {
                return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
            }
        }

        // Filter by role
        if ($request->filled('role') && $request->role !== 'all') {
            $query->byRole($request->role);
        }

        // Filter by login status
        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'success') {
                $query->successfulLogins();
            } else {
                $query->failedLogins();
            }
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $dateFrom = Carbon::parse($request->date_from)->startOfDay();
            $query->where('login_time', '>=', $dateFrom);
        }

        if ($request->filled('date_to')) {
            $dateTo = Carbon::parse($request->date_to)->endOfDay();
            $query->where('login_time', '<=', $dateTo);
        }

        // Filter by username
        if ($request->filled('username')) {
            $query->where('username', 'like', '%' . $request->username . '%');
        }

        // Order by login time (newest first)
        $query->orderBy('login_time', 'desc');

        // Pagination
        $logs = $query->paginate(25)->appends($request->query());

        // Statistics
        $stats = $this->getStatistics($request);

        return view('admin.logs.index', compact('logs', 'stats'));
    }

    private function getStatistics($request)
    {
        $baseQuery = LoginLog::query();

        // Apply same filters as main query
        if ($request->filled('role') && $request->role !== 'all') {
            $baseQuery->byRole($request->role);
        }

        if ($request->filled('date_from')) {
            $dateFrom = Carbon::parse($request->date_from)->startOfDay();
            $baseQuery->where('login_time', '>=', $dateFrom);
        }

        if ($request->filled('date_to')) {
            $dateTo = Carbon::parse($request->date_to)->endOfDay();
            $baseQuery->where('login_time', '<=', $dateTo);
        }

        if ($request->filled('username')) {
            $baseQuery->where('username', 'like', '%' . $request->username . '%');
        }

        return [
            'total_logins' => (clone $baseQuery)->count(),
            'successful_logins' => (clone $baseQuery)->successfulLogins()->count(),
            'failed_logins' => (clone $baseQuery)->failedLogins()->count(),
            'unique_users' => (clone $baseQuery)->distinct('username')->count(),
            'today_logins' => LoginLog::activeToday()->count(),
        ];
    }

    public function show($id)
    {
        // ตรวจสอบว่าเป็น admin หรือไม่
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $log = LoginLog::findOrFail($id);
        return view('admin.logs.show', compact('log'));
    }

    public function export(Request $request)
    {
        // ตรวจสอบว่าเป็น admin หรือไม่
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $query = LoginLog::query();

        // Apply same filters as index
        if ($request->filled('role') && $request->role !== 'all') {
            $query->byRole($request->role);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'success') {
                $query->successfulLogins();
            } else {
                $query->failedLogins();
            }
        }

        if ($request->filled('date_from')) {
            $dateFrom = Carbon::parse($request->date_from)->startOfDay();
            $query->where('login_time', '>=', $dateFrom);
        }

        if ($request->filled('date_to')) {
            $dateTo = Carbon::parse($request->date_to)->endOfDay();
            $query->where('login_time', '<=', $dateTo);
        }

        if ($request->filled('username')) {
            $query->where('username', 'like', '%' . $request->username . '%');
        }

        $logs = $query->orderBy('login_time', 'desc')->get();

        $csv = "ID,Username,User Type,Role,IP Address,User Agent,Login Status,Failure Reason,Login Time,Logout Time,Session Duration\n";

        foreach ($logs as $log) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,\"%s\",%s,%s,%s,%s,%s\n",
                $log->id,
                $log->username,
                $log->user_type,
                $log->role,
                $log->ip_address,
                str_replace('"', '""', $log->user_agent), // Escape quotes in CSV
                $log->login_status,
                $log->failure_reason ?? '',
                $log->login_time_format,
                $log->logout_time_format,
                $log->session_duration_format
            );
        }

        $filename = 'login_logs_' . date('Y-m-d_H-i-s') . '.csv';

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
