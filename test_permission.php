<?php

use App\Models\Role;
use App\Helpers\PermissionHelper;

echo "=== BINARY PERMISSION SYSTEM TEST ===\n\n";

// ดึงข้อมูล roles ทั้งหมด
$roles = Role::all();

foreach ($roles as $role) {
    echo "Role: {$role->role}\n";
    echo "Displayed Number (role_code): {$role->role_code}\n";
    echo "Binary Code (role_code_bin): {$role->role_code_bin}\n";
    echo "Binary Representation: " . PermissionHelper::toBinaryString($role->role_code) . "\n";
    
    // Test binary integrity
    $integrityCheck = PermissionHelper::verifyBinaryIntegrity($role->role_code, $role->role_code_bin);
    echo "Binary Integrity: " . ($integrityCheck ? "✓ PASS" : "✗ FAIL") . "\n";
    
    // Test permissions
    echo "Permissions:\n";
    $permissions = [
        'Admin' => PermissionHelper::hasPermission($role->role_code, PermissionHelper::ADMIN_PERMISSION),
        'Coordinator' => PermissionHelper::hasPermission($role->role_code, PermissionHelper::COORDINATOR_PERMISSION),
        'Lecturer' => PermissionHelper::hasPermission($role->role_code, PermissionHelper::LECTURER_PERMISSION),
        'Staff' => PermissionHelper::hasPermission($role->role_code, PermissionHelper::STAFF_PERMISSION),
        'Student' => PermissionHelper::hasPermission($role->role_code, PermissionHelper::STUDENT_PERMISSION),
        'Guest' => PermissionHelper::hasPermission($role->role_code, PermissionHelper::GUEST_PERMISSION)
    ];
    
    foreach ($permissions as $perm => $hasAccess) {
        echo "  - {$perm}: " . ($hasAccess ? "✓ YES" : "✗ NO") . "\n";
    }
    
    echo str_repeat("-", 50) . "\n\n";
}

// Test specific scenarios
echo "\n=== SPECIFIC PERMISSION TESTS ===\n\n";

// Test Admin permission
$admin = Role::where('role', 'Admin')->first();
if ($admin) {
    echo "Testing Admin (32768):\n";
    echo "Has Admin Permission: " . (($admin->role_code & PermissionHelper::ADMIN_PERMISSION) ? "✓ YES" : "✗ NO") . "\n";
    echo "Binary AND Result: " . ($admin->role_code & PermissionHelper::ADMIN_PERMISSION) . "\n";
    echo "Admin Permission Constant: " . PermissionHelper::ADMIN_PERMISSION . "\n\n";
}

// Test Coordinator-Lecturer permission
$coordLect = Role::where('role', 'Coordinator - Lecturer')->first();
if ($coordLect) {
    echo "Testing Coordinator-Lecturer (24576):\n";
    echo "Has Coordinator Permission: " . (($coordLect->role_code & PermissionHelper::COORDINATOR_PERMISSION) ? "✓ YES" : "✗ NO") . "\n";
    echo "Has Lecturer Permission: " . (($coordLect->role_code & PermissionHelper::LECTURER_PERMISSION) ? "✓ YES" : "✗ NO") . "\n";
    echo "Combined Permission Check: " . ($coordLect->role_code === PermissionHelper::COORDINATOR_LECTURER ? "✓ MATCH" : "✗ NO MATCH") . "\n\n";
}

// Test Student permission
$student = Role::where('role', 'Student')->first();
if ($student) {
    echo "Testing Student (2048):\n";
    echo "Has Admin Permission: " . (($student->role_code & PermissionHelper::ADMIN_PERMISSION) ? "✓ YES" : "✗ NO") . "\n";
    echo "Has Student Permission: " . (($student->role_code & PermissionHelper::STUDENT_PERMISSION) ? "✓ YES" : "✗ NO") . "\n";
    echo "Should NOT have admin access: " . (($student->role_code & PermissionHelper::ADMIN_PERMISSION) === 0 ? "✓ CORRECT" : "✗ WRONG") . "\n\n";
}

echo "=== TEST COMPLETED ===\n";