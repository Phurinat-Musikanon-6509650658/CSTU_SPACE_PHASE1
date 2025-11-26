<?php
/**
 * ทดสอบ algorithm การหาเลขกลุ่มที่ว่าง
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Group;

echo "🔍 ทดสอบการหาเลขกลุ่มที่ว่าง\n";
echo "═══════════════════════════════════════════\n\n";

// แสดงกลุ่มที่มีอยู่
$existingGroupIds = Group::pluck('group_id')->sort()->values()->toArray();
echo "กลุ่มที่มีอยู่: " . json_encode($existingGroupIds) . "\n\n";

// ทดสอบ algorithm
echo "🧪 ทดสอบการหาเลขว่าง:\n";
echo "────────────────────────────────────────────\n";

$nextGroupId = null;

// หาเลขที่ว่าง (ถูกลบไปแล้ว)
echo "ตรวจสอบแต่ละตำแหน่ง:\n";
for ($i = 1; $i <= count($existingGroupIds) + 1; $i++) {
    echo "  เลข {$i}: ";
    if (!in_array($i, $existingGroupIds)) {
        $nextGroupId = $i;
        echo "ว่าง ← ใช้เลขนี้!\n";
        break;
    }
    echo "ใช้แล้ว ✓\n";
}

// ถ้ายังไม่ได้เลข (กรณีไม่มีกลุ่มเลย)
if ($nextGroupId === null) {
    $nextGroupId = 1;
    echo "\n✅ ไม่มีกลุ่มเลย → เริ่มจากเลข 1\n";
} else {
    echo "\n✅ พบช่องว่าง → ใช้เลข: {$nextGroupId}\n";
}

echo "\n═══════════════════════════════════════════\n";
echo "📊 ผลลัพธ์: เลขกลุ่มถัดไปคือ {$nextGroupId}\n";
echo "═══════════════════════════════════════════\n\n";

// แสดงตารางเปรียบเทียบ
echo "📋 ตารางเปรียบเทียบ:\n";
echo "────────────────────────────────────────────\n";
echo "Index | ควรเป็น | มีจริง | สถานะ\n";
echo "──────┼─────────┼────────┼────────\n";
for ($i = 0; $i < max(5, count($existingGroupIds) + 2); $i++) {
    $expected = $i + 1;
    $actual = in_array($expected, $existingGroupIds) ? $expected : '-';
    $status = $actual == '-' ? '⭕ ว่าง' : '✓ ใช้แล้ว';
    
    printf("%5d | %7d | %6s | %s\n", $i, $expected, $actual, $status);
}
