<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class SystemSettingsController extends Controller
{
    /**
     * Display the system settings dashboard
     */
    public function index()
    {
        // ตรวจสอบ permission (admin only)
        if (session('department') !== 'admin') {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        // รวบรวมข้อมูลระบบ
        $systemInfo = $this->getSystemInformation();
        $databaseInfo = $this->getDatabaseInformation();
        $cacheInfo = $this->getCacheInformation();
        
        return view('admin.system.index', compact('systemInfo', 'databaseInfo', 'cacheInfo'));
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
        if (session('department') !== 'admin') {
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
        if (session('department') !== 'admin') {
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
        if (session('department') !== 'admin') {
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
        if (session('department') !== 'admin') {
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
        if (session('department') !== 'admin') {
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
}