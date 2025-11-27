<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Helpers\PermissionHelper;
use App\Models\User;
use App\Models\LoginLog;
use App\Models\SystemSetting;
use App\Models\ExamSchedule;
use App\Models\Project;

class SystemSettingsController extends Controller
{
    /**
     * Display the system settings dashboard
     */
    public function index()
    {
        // ตรวจสอบ permission (admin only)
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        // รวบรวมข้อมูลระบบ
        $systemInfo = $this->getSystemInformation();
        $databaseInfo = $this->getDatabaseInformation();
        $cacheInfo = $this->getCacheInformation();
        
        // ข้อมูลสถานะระบบ
        $systemStatus = SystemSetting::get('system_status', 'open');
        $currentYear = SystemSetting::get('current_year', '2568');
        $currentSemester = SystemSetting::get('current_semester', '1');
        
        return view('admin.system.index', compact('systemInfo', 'databaseInfo', 'cacheInfo', 'systemStatus', 'currentYear', 'currentSemester'));
    }

    /**
     * Get system information
     */
    private function getSystemInformation()
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'memory_usage' => memory_get_usage(true),
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'timezone' => config('app.timezone'),
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug'),
        ];
    }

    /**
     * Get database information
     */
    private function getDatabaseInformation()
    {
        try {
            $userCount = DB::table('user')->count();
            $studentCount = DB::table('student')->count();
            $loginLogCount = DB::table('login_logs')->count();
            
            return [
                'connection' => config('database.default'),
                'database_name' => config('database.connections.mysql.database'),
                'user_count' => $userCount,
                'student_count' => $studentCount,
                'login_log_count' => $loginLogCount,
                'status' => 'Connected'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'Connection Error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get cache information
     */
    private function getCacheInformation()
    {
        try {
            return [
                'default_store' => config('cache.default'),
                'stores' => array_keys(config('cache.stores')),
                'status' => 'Active'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'Error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Clear application cache
     */
    public function clearCache(Request $request)
    {
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        try {
            $cacheType = $request->input('cache_type', 'all');
            
            switch ($cacheType) {
                case 'config':
                    Artisan::call('config:clear');
                    $message = 'Configuration cache cleared successfully';
                    break;
                case 'route':
                    Artisan::call('route:clear');
                    $message = 'Route cache cleared successfully';
                    break;
                case 'view':
                    Artisan::call('view:clear');
                    $message = 'View cache cleared successfully';
                    break;
                case 'application':
                    Cache::flush();
                    $message = 'Application cache cleared successfully';
                    break;
                case 'all':
                default:
                    Artisan::call('cache:clear');
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    Artisan::call('view:clear');
                    Cache::flush();
                    $message = 'All caches cleared successfully';
                    break;
            }
            
            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Error clearing cache: ' . $e->getMessage());
        }
    }

    /**
     * Optimize application
     */
    public function optimize()
    {
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        try {
            // Clear caches first
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            // Optimize
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            
            return back()->with('success', 'Application optimized successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Error optimizing application: ' . $e->getMessage());
        }
    }

    /**
     * Show application configuration
     */
    public function showConfig()
    {
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $configs = [
            'App' => [
                'name' => config('app.name'),
                'url' => config('app.url'),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale'),
                'environment' => config('app.env'),
                'debug' => config('app.debug') ? 'Enabled' : 'Disabled',
            ],
            'Database' => [
                'default' => config('database.default'),
                'host' => config('database.connections.mysql.host'),
                'port' => config('database.connections.mysql.port'),
                'database' => config('database.connections.mysql.database'),
                'username' => config('database.connections.mysql.username'),
            ],
            'Mail' => [
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ],
            'Cache' => [
                'default' => config('cache.default'),
                'prefix' => config('cache.prefix'),
            ],
            'Session' => [
                'driver' => config('session.driver'),
                'lifetime' => config('session.lifetime') . ' minutes',
                'cookie' => config('session.cookie'),
            ]
        ];

        return view('admin.system.config', compact('configs'));
    }

    /**
     * Run database migrations
     */
    public function runMigrations()
    {
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        try {
            Artisan::call('migrate');
            $output = Artisan::output();
            
            return back()->with('success', 'Migrations completed successfully')->with('migration_output', $output);
        } catch (\Exception $e) {
            return back()->with('error', 'Migration failed: ' . $e->getMessage());
        }
    }

    /**
     * Get system logs
     */
    public function showLogs()
    {
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $logPath = storage_path('logs/laravel.log');
        $logs = [];

        if (file_exists($logPath)) {
            $logContent = file_get_contents($logPath);
            $logLines = explode("\n", $logContent);
            
            // Get last 100 lines
            $logs = array_slice(array_reverse($logLines), 0, 100);
        }

        return view('admin.system.logs', compact('logs'));
    }

    /**
     * Toggle system status (open/close)
     */
    public function toggleSystemStatus(Request $request)
    {
        if (!PermissionHelper::isAdmin()) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $currentStatus = SystemSetting::get('system_status', 'open');
        $newStatus = $currentStatus === 'open' ? 'closed' : 'open';
        
        SystemSetting::set('system_status', $newStatus, 'System status (open/closed)');

        return response()->json([
            'success' => true,
            'status' => $newStatus,
            'message' => 'System status updated to ' . $newStatus
        ]);
    }

    /**
     * Update system settings (year/semester)
     */
    public function updateSettings(Request $request)
    {
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $request->validate([
            'current_year' => 'required|numeric|digits:4',
            'current_semester' => 'required|in:1,2,3'
        ]);

        SystemSetting::set('current_year', $request->current_year, 'Current academic year');
        SystemSetting::set('current_semester', $request->current_semester, 'Current semester (1/2/3)');

        return back()->with('success', 'System settings updated successfully');
    }

    /**
     * List all exam schedules (Admin)
     */
    public function examScheduleIndex()
    {
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $examSchedules = ExamSchedule::with('project')
            ->orderBy('ex_start_time', 'desc')
            ->paginate(20);

        return view('admin.exam-schedules.index', compact('examSchedules'));
    }

    /**
     * List all exam schedules (Coordinator)
     */
    public function coordinatorExamScheduleIndex()
    {
        if (!PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $examSchedules = ExamSchedule::with('project')
            ->orderBy('ex_start_time', 'desc')
            ->paginate(20);

        return view('coordinator.exam-schedules.index', compact('examSchedules'));
    }

    /**
     * Show exam schedules in calendar view (Admin)
     */
    public function examScheduleCalendar()
    {
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $examSchedules = ExamSchedule::with('project')
            ->orderBy('ex_start_time', 'asc')
            ->get();

        // Group by date
        $examsByDate = $examSchedules->groupBy(function($schedule) {
            return $schedule->ex_start_time->format('Y-m-d');
        });

        // Get unique locations for filter
        $locations = $examSchedules->pluck('location')->unique()->filter()->values();

        // Statistics
        $totalExams = $examSchedules->count();
        $upcomingExams = $examSchedules->filter(function($schedule) {
            return $schedule->ex_start_time->isFuture();
        })->count();
        $todayExams = $examSchedules->filter(function($schedule) {
            return $schedule->ex_start_time->isToday();
        })->count();
        $pastExams = $examSchedules->filter(function($schedule) {
            return $schedule->ex_start_time->isPast() && !$schedule->ex_start_time->isToday();
        })->count();

        return view('admin.exam-schedules.calendar', compact(
            'examsByDate', 
            'locations', 
            'totalExams', 
            'upcomingExams', 
            'todayExams', 
            'pastExams'
        ));
    }

    /**
     * Show exam schedules in calendar view (Coordinator)
     */
    public function coordinatorExamScheduleCalendar()
    {
        if (!PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $examSchedules = ExamSchedule::with('project')
            ->orderBy('ex_start_time', 'asc')
            ->get();

        // Group by date
        $examsByDate = $examSchedules->groupBy(function($schedule) {
            return $schedule->ex_start_time->format('Y-m-d');
        });

        // Get unique locations for filter
        $locations = $examSchedules->pluck('location')->unique()->filter()->values();

        // Statistics
        $totalExams = $examSchedules->count();
        $upcomingExams = $examSchedules->filter(function($schedule) {
            return $schedule->ex_start_time->isFuture();
        })->count();
        $todayExams = $examSchedules->filter(function($schedule) {
            return $schedule->ex_start_time->isToday();
        })->count();
        $pastExams = $examSchedules->filter(function($schedule) {
            return $schedule->ex_start_time->isPast() && !$schedule->ex_start_time->isToday();
        })->count();

        return view('coordinator.exam-schedules.calendar', compact(
            'examsByDate', 
            'locations', 
            'totalExams', 
            'upcomingExams', 
            'todayExams', 
            'pastExams'
        ));
    }

    /**
     * Show form for creating new exam schedule (Coordinator)
     */
    public function coordinatorExamScheduleCreate()
    {
        if (!PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $projects = Project::orderBy('project_id', 'desc')->get();

        return view('coordinator.exam-schedules.create', compact('projects'));
    }

    /**
     * Store exam schedule (Coordinator)
     */
    public function coordinatorExamScheduleStore(Request $request)
    {
        if (!PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $request->validate([
            'project_ids' => 'required|array|min:1',
            'project_ids.*' => 'required|exists:projects,project_id',
            'ex_start_time' => 'required|date',
            'ex_end_time' => 'required|date|after:ex_start_time',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $createdCount = 0;
        foreach ($request->project_ids as $projectId) {
            $existing = ExamSchedule::where('project_id', $projectId)->first();
            
            if (!$existing) {
                ExamSchedule::create([
                    'project_id' => $projectId,
                    'ex_start_time' => $request->ex_start_time,
                    'ex_end_time' => $request->ex_end_time,
                    'location' => $request->location,
                    'notes' => $request->notes
                ]);
                
                // อัพเดต exam_datetime ใน projects table
                DB::table('projects')
                    ->where('project_id', $projectId)
                    ->update(['exam_datetime' => $request->ex_start_time]);
                
                $createdCount++;
            }
        }

        $message = $createdCount > 0 
            ? "สร้างตารางสอบสำเร็จ {$createdCount} โครงงาน" 
            : "ไม่มีการสร้างตารางสอบ (โครงงานที่เลือกมีตารางสอบอยู่แล้ว)";
        
        $type = $createdCount > 0 ? 'success' : 'warning';

        return redirect()->route('coordinator.exam-schedules.index')
            ->with($type, $message);
    }

    /**
     * Show form for editing exam schedule (Coordinator)
     */
    public function coordinatorExamScheduleEdit($id)
    {
        if (!PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $examSchedule = ExamSchedule::findOrFail($id);
        $projects = Project::orderBy('project_id', 'desc')->get();

        return view('coordinator.exam-schedules.edit', compact('examSchedule', 'projects'));
    }

    /**
     * Update exam schedule (Coordinator)
     */
    public function coordinatorExamScheduleUpdate(Request $request, $id)
    {
        if (!PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $request->validate([
            'project_id' => 'required|exists:projects,project_id',
            'ex_start_time' => 'required|date',
            'ex_end_time' => 'required|date|after:ex_start_time',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $examSchedule = ExamSchedule::findOrFail($id);
        
        // ลบ exam_datetime เดิมจาก project เดิม (ถ้าเปลี่ยน project)
        if ($examSchedule->project_id != $request->project_id) {
            DB::table('projects')
                ->where('project_id', $examSchedule->project_id)
                ->update(['exam_datetime' => null]);
        }
        
        $examSchedule->update([
            'project_id' => $request->project_id,
            'ex_start_time' => $request->ex_start_time,
            'ex_end_time' => $request->ex_end_time,
            'location' => $request->location,
            'notes' => $request->notes
        ]);
        
        // อัพเดต exam_datetime ใน projects table
        DB::table('projects')
            ->where('project_id', $request->project_id)
            ->update(['exam_datetime' => $request->ex_start_time]);

        return redirect()->route('coordinator.exam-schedules.index')
            ->with('success', 'Exam schedule updated successfully');
    }

    /**
     * Delete exam schedule (Coordinator)
     */
    public function coordinatorExamScheduleDestroy($id)
    {
        if (!PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin()) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $examSchedule = ExamSchedule::findOrFail($id);
        
        // ลบ exam_datetime ใน projects table
        DB::table('projects')
            ->where('project_id', $examSchedule->project_id)
            ->update(['exam_datetime' => null]);
        
        $examSchedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'ลบตารางสอบสำเร็จ'
        ]);
        $examSchedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'ลบตารางสอบสำเร็จ'
        ]);
    }

    /**
     * Show form for creating new exam schedule (Admin)
     */
    public function examScheduleCreate()
    {
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $projects = Project::orderBy('project_id', 'desc')->get();

        return view('admin.exam-schedules.create', compact('projects'));
    }

    /**
     * Store a newly created exam schedule
     */
    public function examScheduleStore(Request $request)
    {
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $request->validate([
            'project_ids' => 'required|array|min:1',
            'project_ids.*' => 'required|exists:projects,project_id',
            'ex_start_time' => 'required|date',
            'ex_end_time' => 'required|date|after:ex_start_time',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        // Create exam schedule for each selected project
        $createdCount = 0;
        foreach ($request->project_ids as $projectId) {
            // Check if this project already has an exam schedule
            $existing = ExamSchedule::where('project_id', $projectId)->first();
            
            if (!$existing) {
                ExamSchedule::create([
                    'project_id' => $projectId,
                    'ex_start_time' => $request->ex_start_time,
                    'ex_end_time' => $request->ex_end_time,
                    'location' => $request->location,
                    'notes' => $request->notes
                ]);
                
                // อัพเดต exam_datetime ใน projects table
                DB::table('projects')
                    ->where('project_id', $projectId)
                    ->update(['exam_datetime' => $request->ex_start_time]);
                
                $createdCount++;
            }
        }

        $message = $createdCount > 0 
            ? "สร้างตารางสอบสำเร็จ {$createdCount} โครงงาน" 
            : "ไม่มีการสร้างตารางสอบ (โครงงานที่เลือกมีตารางสอบอยู่แล้ว)";
        
        $type = $createdCount > 0 ? 'success' : 'warning';

        return redirect()->route('admin.exam-schedules.index')
            ->with($type, $message);
    }

    /**
     * Show form for editing exam schedule
     */
    public function examScheduleEdit($id)
    {
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $examSchedule = ExamSchedule::findOrFail($id);
        $projects = Project::orderBy('project_id', 'desc')->get();

        return view('admin.exam-schedules.edit', compact('examSchedule', 'projects'));
    }

    /**
     * Update exam schedule
     */
    public function examScheduleUpdate(Request $request, $id)
    {
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $request->validate([
            'project_id' => 'required|exists:projects,project_id',
            'ex_start_time' => 'required|date',
            'ex_end_time' => 'required|date|after:ex_start_time',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $examSchedule = ExamSchedule::findOrFail($id);
        
        // ลบ exam_datetime เดิมจาก project เดิม (ถ้าเปลี่ยน project)
        if ($examSchedule->project_id != $request->project_id) {
            DB::table('projects')
                ->where('project_id', $examSchedule->project_id)
                ->update(['exam_datetime' => null]);
        }
        
        $examSchedule->update([
            'project_id' => $request->project_id,
            'ex_start_time' => $request->ex_start_time,
            'ex_end_time' => $request->ex_end_time,
            'location' => $request->location,
            'notes' => $request->notes
        ]);
        
        // อัพเดต exam_datetime ใน projects table
        DB::table('projects')
            ->where('project_id', $request->project_id)
            ->update(['exam_datetime' => $request->ex_start_time]);

        return redirect()->route('admin.exam-schedules.index')
            ->with('success', 'Exam schedule updated successfully');
    }

    /**
     * Delete exam schedule
     */
    public function examScheduleDestroy($id)
    {
        if (!PermissionHelper::isAdmin()) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $examSchedule = ExamSchedule::findOrFail($id);
        
        // ลบ exam_datetime ใน projects table
        DB::table('projects')
            ->where('project_id', $examSchedule->project_id)
            ->update(['exam_datetime' => null]);
        
        $examSchedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Exam schedule deleted successfully'
        ]);
    }

    // ==========================================
    // Staff Exam Schedules (View Only)
    // ==========================================
    
    /**
     * แสดงรายการตารางสอบทั้งหมดสำหรับ Staff (ดูอย่างเดียว)
     */
    public function staffExamSchedules()
    {
        if (!PermissionHelper::isStaff() && !PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $examSchedules = ExamSchedule::with('project')
            ->orderBy('ex_start_time', 'asc')
            ->paginate(20);

        return view('staff.exam-schedules.index', compact('examSchedules'));
    }

    /**
     * แสดงตารางสอบแบบปฏิทินสำหรับ Staff
     */
    public function staffExamSchedulesCalendar()
    {
        if (!PermissionHelper::isStaff() && !PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $query = ExamSchedule::with('project');

        // Filter by project status if provided
        if (request('status')) {
            $query->whereHas('project', function($q) {
                $q->where('status', request('status'));
            });
        }

        // Filter by location if provided
        if (request('location')) {
            $query->where('location', 'LIKE', '%' . request('location') . '%');
        }

        // Search by project name or notes
        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('project', function($subQ) use ($search) {
                    $subQ->where('project_name', 'LIKE', '%' . $search . '%');
                })
                ->orWhere('notes', 'LIKE', '%' . $search . '%');
            });
        }

        $examSchedules = $query->orderBy('ex_start_time', 'asc')->get();

        // Group by date
        $schedulesByDate = $examSchedules->groupBy(function($schedule) {
            return $schedule->ex_start_time->format('Y-m-d');
        });

        return view('staff.exam-schedules.calendar', compact('schedulesByDate'));
    }
}
