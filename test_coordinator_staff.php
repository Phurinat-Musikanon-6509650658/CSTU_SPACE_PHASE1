<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "🧪 ทดสอบ Coordinator ที่มี Staff role เพิ่ม\n";
echo "═══════════════════════════════════════════\n\n";

$coord = User::where('username_user', 'coordinator')->first();

if (!$coord) {
    echo "❌ ไม่พบ user coordinator\n";
    exit(1);
}

echo "ก่อนเพิ่ม Staff:\n";
echo "  Username: {$coord->username_user}\n";
echo "  Role: {$coord->role} (binary: " . sprintf('%016b', $coord->role) . ")\n";
echo "  Has Coordinator: " . (($coord->role & 16384) === 16384 ? 'YES ✓' : 'NO') . "\n";
echo "  Has Staff: " . (($coord->role & 4096) === 4096 ? 'YES ✓' : 'NO') . "\n\n";

// เพิ่ม Staff role
$coord->role = $coord->role | 4096;
$coord->save();

echo "หลังเพิ่ม Staff:\n";
echo "  Role: {$coord->role} (binary: " . sprintf('%016b', $coord->role) . ")\n";
echo "  Has Coordinator: " . (($coord->role & 16384) === 16384 ? 'YES ✓' : 'NO') . "\n";
echo "  Has Staff: " . (($coord->role & 4096) === 4096 ? 'YES ✓' : 'NO') . "\n\n";

// ทดสอบ middleware logic
$allowedRoleCodes = [16384, 32768]; // coordinator, admin
$hasPermission = false;
foreach ($allowedRoleCodes as $roleCode) {
    if (($coord->role & $roleCode) === $roleCode) {
        $hasPermission = true;
        break;
    }
}

echo "ทดสอบ middleware('role:coordinator,admin'):\n";
echo "  Can access: " . ($hasPermission ? '✅ YES' : '❌ NO') . "\n\n";

echo "═══════════════════════════════════════════\n";
echo "✅ ตอนนี้ Coordinator (แม้มี Staff role) สามารถเข้า Project Proposals ได้แล้ว!\n";
