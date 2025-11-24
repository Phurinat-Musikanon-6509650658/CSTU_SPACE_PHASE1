<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserRole;
use App\Helpers\PermissionHelper;

class TestPermissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:permission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the binary permission system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== BINARY PERMISSION SYSTEM TEST ===');
        $this->newLine();

        // ดึงข้อมูล roles ทั้งหมด
        $roles = UserRole::all();

        foreach ($roles as $role) {
            $this->info("Role: {$role->role_name}");
            $this->line("Displayed Number (role_code): {$role->role_code}");
            $this->line("Binary Code (role_code_bin): {$role->role_code_bin}");
            $this->line("Binary Representation: " . PermissionHelper::toBinaryString($role->role_code));
            
            // Test binary integrity
            $integrityCheck = PermissionHelper::verifyBinaryIntegrity($role->role_code, $role->role_code_bin);
            if ($integrityCheck) {
                $this->info("Binary Integrity: ✓ PASS");
            } else {
                $this->error("Binary Integrity: ✗ FAIL");
            }
            
            // Test permissions
            $this->line("Permissions:");
            $permissions = [
                'Admin' => PermissionHelper::hasPermission($role->role_code, PermissionHelper::ADMIN_PERMISSION),
                'Coordinator' => PermissionHelper::hasPermission($role->role_code, PermissionHelper::COORDINATOR_PERMISSION),
                'Lecturer' => PermissionHelper::hasPermission($role->role_code, PermissionHelper::LECTURER_PERMISSION),
                'Staff' => PermissionHelper::hasPermission($role->role_code, PermissionHelper::STAFF_PERMISSION),
                'Student' => PermissionHelper::hasPermission($role->role_code, PermissionHelper::STUDENT_PERMISSION),
                'Guest' => PermissionHelper::hasPermission($role->role_code, PermissionHelper::GUEST_PERMISSION)
            ];
            
            foreach ($permissions as $perm => $hasAccess) {
                if ($hasAccess) {
                    $this->line("  - {$perm}: <fg=green>✓ YES</fg=green>");
                } else {
                    $this->line("  - {$perm}: <fg=red>✗ NO</fg=red>");
                }
            }
            
            $this->line(str_repeat("-", 50));
            $this->newLine();
        }

        // Test specific scenarios
        $this->info('=== SPECIFIC PERMISSION TESTS ===');
        $this->newLine();

        // Test Admin permission
        $admin = UserRole::where('role_name', 'admin')->first();
        if ($admin) {
            $this->info("Testing Admin (32768):");
            $hasAdmin = ($admin->role_code & PermissionHelper::ADMIN_PERMISSION) !== 0;
            $this->line("Has Admin Permission: " . ($hasAdmin ? "<fg=green>✓ YES</fg=green>" : "<fg=red>✗ NO</fg=red>"));
            $this->line("Binary AND Result: " . ($admin->role_code & PermissionHelper::ADMIN_PERMISSION));
            $this->line("Admin Permission Constant: " . PermissionHelper::ADMIN_PERMISSION);
            $this->newLine();
        }

        // Test Student permission (should NOT have admin access)
        $student = UserRole::where('role_name', 'student')->first();
        if ($student) {
            $this->info("Testing Student (2048):");
            $hasAdmin = ($student->role_code & PermissionHelper::ADMIN_PERMISSION) !== 0;
            $hasStudent = ($student->role_code & PermissionHelper::STUDENT_PERMISSION) !== 0;
            $this->line("Has Admin Permission: " . ($hasAdmin ? "<fg=red>✗ YES (WRONG!)</fg=red>" : "<fg=green>✓ NO (CORRECT)</fg=green>"));
            $this->line("Has Student Permission: " . ($hasStudent ? "<fg=green>✓ YES</fg=green>" : "<fg=red>✗ NO</fg=red>"));
            $this->newLine();
        }

        // Test Combined Roles
        $coordLect = UserRole::where('role_name', 'coordinator-lecturer')->first();
        if ($coordLect) {
            $this->info("Testing Coordinator-Lecturer (24576):");
            $hasCoord = ($coordLect->role_code & PermissionHelper::COORDINATOR_PERMISSION) !== 0;
            $hasLect = ($coordLect->role_code & PermissionHelper::LECTURER_PERMISSION) !== 0;
            $this->line("Has Coordinator Permission: " . ($hasCoord ? "<fg=green>✓ YES</fg=green>" : "<fg=red>✗ NO</fg=red>"));
            $this->line("Has Lecturer Permission: " . ($hasLect ? "<fg=green>✓ YES</fg=green>" : "<fg=red>✗ NO</fg=red>"));
            $this->line("Binary value: " . PermissionHelper::toBinaryString($coordLect->role_code));
            $this->newLine();
        }

        $this->info('=== TEST COMPLETED ===');
        return Command::SUCCESS;
    }
}
