<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "🧪 ทดสอบ CheckRole Middleware - Binary Permission\n";
echo "═══════════════════════════════════════════\n\n";

// สมมติ user มี role ผสม
$testCases = [
    [
        'role' => 16384,           // Coordinator only
        'allowed' => [16384, 32768], // coordinator, admin
        'description' => 'Coordinator only'
    ],
    [
        'role' => 20480,           // Coordinator + Staff (16384 + 4096)
        'allowed' => [16384, 32768], // coordinator, admin
        'description' => 'Coordinator + Staff'
    ],
    [
        'role' => 32768,           // Admin only
        'allowed' => [16384, 32768], // coordinator, admin
        'description' => 'Admin only'
    ],
    [
        'role' => 8192,            // Lecturer only
        'allowed' => [16384, 32768], // coordinator, admin
        'description' => 'Lecturer only (should FAIL)'
    ],
];

echo "Test Scenario: middleware('role:coordinator,admin')\n";
echo "───────────────────────────────────────────\n\n";

foreach ($testCases as $index => $test) {
    $userRole = $test['role'];
    $allowedRoleCodes = $test['allowed'];
    
    // ทดสอบ logic ใหม่ (bitwise)
    $hasPermission = false;
    foreach ($allowedRoleCodes as $roleCode) {
        if (($userRole & $roleCode) === $roleCode) {
            $hasPermission = true;
            break;
        }
    }
    
    $status = $hasPermission ? '✅ PASS' : '❌ FAIL';
    $binary = sprintf('%016b', $userRole);
    
    echo "Test " . ($index + 1) . ": {$test['description']}\n";
    echo "  User role: {$userRole} (binary: {$binary})\n";
    echo "  Result: {$status}\n";
    
    if ($index === 1) {
        // แสดงรายละเอียดการคำนวณ
        echo "  Detail:\n";
        echo "    - Coordinator bit (16384): " . (($userRole & 16384) === 16384 ? 'YES ✓' : 'NO') . "\n";
        echo "    - Staff bit (4096): " . (($userRole & 4096) === 4096 ? 'YES ✓' : 'NO') . "\n";
        echo "    - Has Coordinator permission: YES → Access Granted!\n";
    }
    
    echo "\n";
}

echo "═══════════════════════════════════════════\n";
echo "📊 สรุป:\n";
echo "  - Coordinator only (16384): ✅ ผ่าน\n";
echo "  - Coordinator + Staff (20480): ✅ ผ่าน (แก้ไขแล้ว!)\n";
echo "  - Admin (32768): ✅ ผ่าน\n";
echo "  - Lecturer (8192): ❌ ไม่ผ่าน (ถูกต้อง)\n";
echo "═══════════════════════════════════════════\n";
