<?php
/**
 * ทดสอบ Race Condition เมื่อมีหลายคนสร้างกลุ่มพร้อมกัน
 * 
 * วิธีทดสอบ:
 * 1. เปิด Terminal 3 หน้าต่าง
 * 2. รันคำสั่งนี้พร้อมกันใน 3 Terminal:
 *    php test_race_condition.php
 * 
 * ผลลัพธ์ที่คาดหวัง:
 * - คนที่ 1 ได้กลุ่มเลข 1
 * - คนที่ 2 ได้กลุ่มเลข 2 (ไม่ซ้ำกับคนที่ 1)
 * - คนที่ 3 ได้กลุ่มเลข 3 (ไม่ซ้ำกับคนที่ 1, 2)
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Student;

// สุ่มเลือก student (ถ้าไม่พอ 3 คน ก็ใช้เท่าที่มี)
$students = Student::whereDoesntHave('groups')->inRandomOrder()->limit(3)->get();

$testCount = min($students->count(), 3);

if ($testCount < 1) {
    echo "❌ ต้องมี student อย่างน้อย 1 คนที่ยังไม่มีกลุ่ม\n";
    echo "💡 ลองรัน: php artisan tinker --execute=\"App\\Models\\Group::truncate(); App\\Models\\GroupMember::truncate();\"\n";
    exit(1);
}

echo "📝 จำนวน student ที่จะทดสอบ: {$testCount} คน\n";

echo "🚀 เริ่มทดสอบ Race Condition...\n";
echo "กลุ่มที่มีอยู่ก่อนทดสอบ: " . json_encode(Group::pluck('group_id')->toArray()) . "\n\n";

// จำลองการสร้างกลุ่มพร้อมกัน
$results = [];
for ($i = 0; $i < $testCount; $i++) {
    $student = $students[$i];
    echo "👤 Student {$student->username_std} กำลังสร้างกลุ่ม...\n";
    
    DB::beginTransaction();
    try {
            // ล็อคตาราง groups (จำลองขั้นตอนใน GroupController)
            $existingGroupIds = Group::lockForUpdate()->pluck('group_id')->sort()->values()->toArray();
            
            $nextGroupId = null;
            
            // หาเลขที่ว่าง
            for ($i = 1; $i <= count($existingGroupIds) + 1; $i++) {
                if (!in_array($i, $existingGroupIds)) {
                    $nextGroupId = $i;
                    break;
                }
            }
            
            // ถ้ายังไม่ได้เลข (กรณีไม่มีกลุ่มเลย)
            if ($nextGroupId === null) {
                $nextGroupId = 1;
            }        echo "   → หาเลขกลุ่มได้: {$nextGroupId}\n";
        
        // หน่วงเวลาเล็กน้อยเพื่อจำลอง processing time
        usleep(rand(100000, 500000)); // 0.1-0.5 วินาที
        
        // สร้างกลุ่ม
        $group = Group::create([
            'group_id' => $nextGroupId,
            'subject_code' => 'CS303',
            'year' => 2568,
            'semester' => 1,
            'status_group' => 'created'
        ]);
        
        // เพิ่มสมาชิก
        GroupMember::create([
            'group_id' => $group->group_id,
            'username_std' => $student->username_std
        ]);
        
        DB::commit();
        
        $results[] = [
            'student' => $student->username_std,
            'group_id' => $group->group_id,
            'success' => true
        ];
        
        echo "   ✅ สร้างกลุ่ม {$group->group_id} สำเร็จ\n\n";
        
    } catch (\Exception $e) {
        DB::rollback();
        
        $results[] = [
            'student' => $student->username_std,
            'error' => $e->getMessage(),
            'success' => false
        ];
        
        echo "   ❌ เกิดข้อผิดพลาด: {$e->getMessage()}\n\n";
    }
}

// สรุปผลการทดสอบ
echo "═══════════════════════════════════════════\n";
echo "📊 สรุปผลการทดสอบ\n";
echo "═══════════════════════════════════════════\n\n";

$groupIds = array_column(array_filter($results, fn($r) => $r['success']), 'group_id');
$hasDuplicate = count($groupIds) !== count(array_unique($groupIds));

foreach ($results as $result) {
    if ($result['success']) {
        echo "✅ {$result['student']} → กลุ่ม {$result['group_id']}\n";
    } else {
        echo "❌ {$result['student']} → Error: {$result['error']}\n";
    }
}

echo "\n";
if ($hasDuplicate) {
    echo "❌ พบ group_id ซ้ำกัน! (Race Condition เกิดขึ้น)\n";
    echo "   เลขกลุ่มที่สร้าง: " . json_encode($groupIds) . "\n";
} else {
    echo "✅ ไม่มี group_id ซ้ำกัน! (ระบบทำงานถูกต้อง)\n";
    echo "   เลขกลุ่มที่สร้าง: " . json_encode($groupIds) . "\n";
}

echo "\nกลุ่มทั้งหมดในระบบ: " . json_encode(Group::pluck('group_id')->toArray()) . "\n";
