<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserRole;
use App\Http\Controllers\MenuController;
use ReflectionClass;

class TestMenuRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:menu-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test MenuController for all roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== MENU CONTROLLER ROLE TEST ===');
        $this->newLine();

        // ตรวจสอบ roles ใน database
        $this->info('🔍 Checking roles in database:');
        $roles = UserRole::orderBy('role_code_bin', 'desc')->get();

        if ($roles->isEmpty()) {
            $this->error('❌ No roles found! Run: php artisan db:seed --class=UserRoleSeeder');
            return 1;
        }

        $this->table(
            ['Role', 'Code', 'Binary'],
            $roles->map(function ($role) {
                return [
                    'role' => ucfirst($role->role_name),
                    'code' => $role->role_code_bin,
                    'binary' => str_pad(decbin($role->role_code_bin), 15, '0', STR_PAD_LEFT)
                ];
            })->toArray()
        );

        $this->info("✅ Found {$roles->count()} roles");
        $this->newLine();

        // ทดสอบแต่ละ role
        $this->info('🎭 Testing Menu Generation:');
        $this->line(str_repeat('=', 80));

        foreach ($roles as $role) {
            $this->newLine();
            $this->info("🎯 ROLE: " . strtoupper($role->role));
            $this->line("📊 Code: {$role->role_code_bin} | Binary: " . str_pad(decbin($role->role_code_bin), 15, '0', STR_PAD_LEFT));
            $this->line(str_repeat('-', 60));

            try {
                // สร้าง MenuController
                $controller = new MenuController();
                
                // ใช้ reflection เพื่อเข้าถึง private methods
                $reflection = new ReflectionClass($controller);
                
                // Test getRoleCodeBinFromDatabase
                $getRoleMethod = $reflection->getMethod('getRoleCodeBinFromDatabase');
                $getRoleMethod->setAccessible(true);
                $roleCodeFromDB = $getRoleMethod->invoke($controller, $role->role);
                
                // Test getMenuByPermission
                $getMenuMethod = $reflection->getMethod('getMenuByPermission');
                $getMenuMethod->setAccessible(true);
                $menuGroups = $getMenuMethod->invoke($controller, $roleCodeFromDB);
                
                $this->line("🔢 DB Role Code: {$roleCodeFromDB}");
                $this->line("📂 Menu Groups: " . count($menuGroups));
                
                $totalItems = 0;
                foreach ($menuGroups as $index => $group) {
                    $itemCount = count($group['items']);
                    $totalItems += $itemCount;
                    $this->line("   " . ($index + 1) . ". {$group['title']} ({$itemCount} items)");
                    
                    foreach ($group['items'] as $itemIndex => $item) {
                        $this->line("      " . chr(97 + $itemIndex) . ") {$item['title']} - {$item['description']}");
                    }
                }
                
                $this->info("✅ Total Items: {$totalItems}");
                
                // แสดง permissions
                $permissions = $this->getActivePermissions($roleCodeFromDB);
                $this->line("🔐 Permissions: " . implode(', ', $permissions));
                
            } catch (\Exception $e) {
                $this->error("❌ Error: " . $e->getMessage());
            }
            
            $this->line(str_repeat('=', 80));
        }

        $this->newLine();
        $this->info('✅ All role tests completed successfully!');
        
        return 0;
    }

    private function getActivePermissions($roleCodeBin)
    {
        $permissions = [];
        
        $adminRole = UserRole::where('role_name', 'admin')->first();
        $coordinatorRole = UserRole::where('role_name', 'coordinator')->first();
        $lecturerRole = UserRole::where('role_name', 'lecturer')->first();
        $staffRole = UserRole::where('role_name', 'staff')->first();
        $studentRole = UserRole::where('role_name', 'student')->first();

        if ($adminRole && ($roleCodeBin & $adminRole->role_code_bin) !== 0) $permissions[] = 'Admin';
        if ($coordinatorRole && ($roleCodeBin & $coordinatorRole->role_code_bin) !== 0) $permissions[] = 'Coordinator';
        if ($lecturerRole && ($roleCodeBin & $lecturerRole->role_code_bin) !== 0) $permissions[] = 'Lecturer';
        if ($staffRole && ($roleCodeBin & $staffRole->role_code_bin) !== 0) $permissions[] = 'Staff';
        if ($studentRole && ($roleCodeBin & $studentRole->role_code_bin) !== 0) $permissions[] = 'Student';
        if ($roleCodeBin === 1) $permissions[] = 'Guest';
        
        return $permissions;
    }
}
