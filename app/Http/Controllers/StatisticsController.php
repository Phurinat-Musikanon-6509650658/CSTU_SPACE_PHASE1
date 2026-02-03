<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoginLog;
use App\Models\User;
use App\Models\UserRole;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    public function index()
    {
        // Admin เห็นสถิติทั้งหมด
        if (PermissionHelper::canViewAllData()) {
            // สถิติรวม
            $generalStats = $this->getGeneralStatistics();
            
            // สถิติแยกตาม Role
            $roleStats = $this->getRoleStatistics();
            
            // สถิติรายวัน (7 วันล่าสุด)
            $dailyStats = $this->getDailyStatistics();
            
            // สถิติรายชั่วโมง (24 ชั่วโมงล่าสุด)
            $hourlyStats = $this->getHourlyStatistics();
            
            // Top Users
            $topUsers = $this->getTopUsers();
            
            // Security Stats
            $securityStats = $this->getSecurityStatistics();
            
            // Session Duration Stats
            $sessionStats = $this->getSessionStatistics();

            return view('admin.statistics.index', compact(
                'generalStats',
                'roleStats', 
                'dailyStats',
                'hourlyStats',
                'topUsers',
                'securityStats',
                'sessionStats'
            ));
        }
        
        // Coordinator/Lecturer/Staff เห็นเฉพาะสถิติตัวเอง
        if (PermissionHelper::canManageRoles()) {
            $username = PermissionHelper::getCurrentUsername();
            $personalStats = $this->getPersonalStatistics($username);
            return view('admin.statistics.personal', compact('personalStats'));
        }

        return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    }

    private function getGeneralStatistics()
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'total_users' => User::count(),
            'total_logins_all_time' => LoginLog::count(),
            'successful_logins_all_time' => LoginLog::successfulLogins()->count(),
            'failed_logins_all_time' => LoginLog::failedLogins()->count(),
            
            // Today
            'today_total' => LoginLog::activeToday()->count(),
            'today_success' => LoginLog::activeToday()->successfulLogins()->count(),
            'today_failed' => LoginLog::activeToday()->failedLogins()->count(),
            'today_unique_users' => LoginLog::activeToday()->distinct('username')->count(),
            
            // This Week
            'week_total' => LoginLog::where('login_time', '>=', $thisWeek)->count(),
            'week_success' => LoginLog::where('login_time', '>=', $thisWeek)->successfulLogins()->count(),
            'week_unique_users' => LoginLog::where('login_time', '>=', $thisWeek)->distinct('username')->count(),
            
            // This Month
            'month_total' => LoginLog::where('login_time', '>=', $thisMonth)->count(),
            'month_success' => LoginLog::where('login_time', '>=', $thisMonth)->successfulLogins()->count(),
            'month_unique_users' => LoginLog::where('login_time', '>=', $thisMonth)->distinct('username')->count(),
            
            // Success Rate
            'success_rate_today' => $this->calculateSuccessRate(
                LoginLog::activeToday()->successfulLogins()->count(),
                LoginLog::activeToday()->count()
            ),
            'success_rate_week' => $this->calculateSuccessRate(
                LoginLog::where('login_time', '>=', $thisWeek)->successfulLogins()->count(),
                LoginLog::where('login_time', '>=', $thisWeek)->count()
            ),
            'success_rate_month' => $this->calculateSuccessRate(
                LoginLog::where('login_time', '>=', $thisMonth)->successfulLogins()->count(),
                LoginLog::where('login_time', '>=', $thisMonth)->count()
            ),
        ];
    }

    private function getRoleStatistics()
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        
        $roleStats = [];
        
        // Define roles with their codes (hardcoded to match view expectations)
        $roles = [
            'admin' => 32768,
            'coordinator' => 16384,
            'lecturer' => 8192,
            'staff' => 4096,
            'student' => 2048,
        ];
        
        foreach ($roles as $roleName => $roleCode) {
            // Count users with this role using bitwise AND
            $usersWithRole = User::whereRaw("(role & ?) != 0", [$roleCode])->count();
            
            $roleStats[$roleName] = [
                'total_users' => $usersWithRole,
                'today_logins' => LoginLog::byRole($roleCode)->activeToday()->count(),
                'today_success' => LoginLog::byRole($roleCode)->activeToday()->successfulLogins()->count(),
                'today_failed' => LoginLog::byRole($roleCode)->activeToday()->failedLogins()->count(),
                'week_logins' => LoginLog::byRole($roleCode)->where('login_time', '>=', $thisWeek)->count(),
                'active_today' => LoginLog::byRole($roleCode)->activeToday()->distinct('username')->count(),
                'success_rate' => $this->calculateSuccessRate(
                    LoginLog::byRole($roleCode)->activeToday()->successfulLogins()->count(),
                    LoginLog::byRole($roleCode)->activeToday()->count()
                ),
                'avg_session_duration' => $this->getAverageSessionDuration($roleCode)
            ];
        }
        
        return $roleStats;
    }

    private function getDailyStatistics()
    {
        $stats = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $nextDate = $date->copy()->addDay();
            
            $stats[] = [
                'date' => $date->format('Y-m-d'),
                'date_format' => $date->format('d/m'),
                'total' => LoginLog::whereBetween('login_time', [$date, $nextDate])->count(),
                'success' => LoginLog::whereBetween('login_time', [$date, $nextDate])->successfulLogins()->count(),
                'failed' => LoginLog::whereBetween('login_time', [$date, $nextDate])->failedLogins()->count(),
                'unique_users' => LoginLog::whereBetween('login_time', [$date, $nextDate])->distinct('username')->count()
            ];
        }
        
        return $stats;
    }

    private function getHourlyStatistics()
    {
        $stats = [];
        $now = Carbon::now();
        
        for ($i = 23; $i >= 0; $i--) {
            $hour = $now->copy()->subHours($i)->startOfHour();
            $nextHour = $hour->copy()->addHour();
            
            $stats[] = [
                'hour' => $hour->format('H:00'),
                'timestamp' => $hour->timestamp,
                'total' => LoginLog::whereBetween('login_time', [$hour, $nextHour])->count(),
                'success' => LoginLog::whereBetween('login_time', [$hour, $nextHour])->successfulLogins()->count(),
                'failed' => LoginLog::whereBetween('login_time', [$hour, $nextHour])->failedLogins()->count()
            ];
        }
        
        return $stats;
    }

    private function getTopUsers()
    {
        $today = Carbon::today();
        
        return [
            'most_active_today' => LoginLog::select('username', 'role')
                ->selectRaw('COUNT(*) as login_count')
                ->activeToday()
                ->groupBy('username', 'role')
                ->orderByDesc('login_count')
                ->limit(10)
                ->get(),
                
            'longest_sessions' => LoginLog::select('username', 'role', 'session_duration', 'login_time', 'logout_time')
                ->whereNotNull('session_duration')
                ->where('login_time', '>=', $today)
                ->orderByDesc('session_duration')
                ->limit(10)
                ->get()
        ];
    }

    private function getSecurityStatistics()
    {
        $today = Carbon::today();
        $lastHour = Carbon::now()->subHour();
        
        return [
            'failed_attempts_today' => LoginLog::failedLogins()->activeToday()->count(),
            'failed_attempts_last_hour' => LoginLog::failedLogins()
                ->where('login_time', '>=', $lastHour)->count(),
                
            'suspicious_ips' => LoginLog::select('ip_address')
                ->selectRaw('COUNT(*) as attempt_count')
                ->selectRaw('COUNT(DISTINCT username) as unique_usernames')
                ->where('login_time', '>=', $today)
                ->groupBy('ip_address')
                ->havingRaw('COUNT(*) > 10 OR COUNT(DISTINCT username) > 3')
                ->orderByDesc('attempt_count')
                ->limit(10)
                ->get(),
                
            'multiple_fail_users' => LoginLog::select('username')
                ->selectRaw('COUNT(*) as fail_count')
                ->failedLogins()
                ->where('login_time', '>=', $today)
                ->groupBy('username')
                ->havingRaw('COUNT(*) >= 3')
                ->orderByDesc('fail_count')
                ->limit(10)
                ->get(),
                
            'concurrent_sessions' => LoginLog::select('username', 'role')
                ->selectRaw('COUNT(*) as session_count')
                ->whereNull('logout_time')
                ->groupBy('username', 'role')
                ->havingRaw('COUNT(*) > 1')
                ->get()
        ];
    }

    private function getSessionStatistics()
    {
        $today = Carbon::today();
        
        $sessionData = LoginLog::whereNotNull('session_duration')
            ->where('login_time', '>=', $today)
            ->get();
            
        if ($sessionData->isEmpty()) {
            return [
                'avg_duration' => 0,
                'min_duration' => 0,
                'max_duration' => 0,
                'total_sessions' => 0
            ];
        }
        
        return [
            'avg_duration' => round($sessionData->avg('session_duration')),
            'min_duration' => $sessionData->min('session_duration'),
            'max_duration' => $sessionData->max('session_duration'),
            'total_sessions' => $sessionData->count(),
            'avg_duration_by_role' => $this->getAverageSessionByRole()
        ];
    }

    private function getAverageSessionByRole()
    {
        $roles = ['admin', 'coordinator', 'lecturer', 'staff', 'student'];
        $today = Carbon::today();
        $result = [];
        
        foreach ($roles as $role) {
            $avg = LoginLog::where('role', $role)
                ->whereNotNull('session_duration')
                ->where('login_time', '>=', $today)
                ->avg('session_duration');
                
            $result[$role] = $avg ? round($avg) : 0;
        }
        
        return $result;
    }

    private function getAverageSessionDuration($role)
    {
        $today = Carbon::today();
        
        $avg = LoginLog::byRole($role)
            ->whereNotNull('session_duration')
            ->where('login_time', '>=', $today)
            ->avg('session_duration');
            
        return $avg ? round($avg) : 0;
    }

    private function calculateSuccessRate($successful, $total)
    {
        if ($total == 0) return 0;
        return round(($successful / $total) * 100, 1);
    }

    private function getPersonalStatistics($username)
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'username' => $username,
            'total_logins' => LoginLog::where('username', $username)->count(),
            'successful_logins' => LoginLog::where('username', $username)->successfulLogins()->count(),
            'failed_logins' => LoginLog::where('username', $username)->failedLogins()->count(),
            'today_logins' => LoginLog::where('username', $username)->activeToday()->count(),
            'week_logins' => LoginLog::where('username', $username)->where('login_time', '>=', $thisWeek)->count(),
            'month_logins' => LoginLog::where('username', $username)->where('login_time', '>=', $thisMonth)->count(),
            'last_login' => LoginLog::where('username', $username)->latest('login_time')->first(),
            'success_rate' => $this->calculateSuccessRate(
                LoginLog::where('username', $username)->successfulLogins()->count(),
                LoginLog::where('username', $username)->count()
            ),
        ];
    }

    public function export()
    {
        // ตรวจสอบสิทธิ์
        if (!PermissionHelper::canViewAllData()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $generalStats = $this->getGeneralStatistics();
        $roleStats = $this->getRoleStatistics();
        
        $csv = "Type,Metric,Value\n";
        
        // General Statistics
        foreach ($generalStats as $key => $value) {
            $csv .= "General," . str_replace('_', ' ', $key) . ",$value\n";
        }
        
        // Role Statistics
        foreach ($roleStats as $role => $stats) {
            foreach ($stats as $key => $value) {
                $csv .= "Role $role," . str_replace('_', ' ', $key) . ",$value\n";
            }
        }
        
        $filename = 'statistics_' . date('Y-m-d_H-i-s') . '.csv';
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}