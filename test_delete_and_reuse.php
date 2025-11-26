<?php
/**
 * ทดสอบการลบกลุ่ม 1 และสร้างกลุ่มใหม่
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

echo "🧪 ทดสอบการนำเลขกลุ่มที่ถูกลบกลับมาใช้ใหม่\n";
echo "═══════════════════════════════════════════\n\n";

echo "📊 กลุ่มก่อนลบ: " . json_encode(Group::pluck('group_id')->toArray()) . "\n";

// ลบกลุ่ม 1
echo "🗑️ ลบกลุ่ม 1...\n";
$group1 = Group::find(1);
if ($group1) {
    DB::beginTransaction();
    try {
        $group1->members()->delete();
        if ($group1->project) {
            $group1->project->delete();
        }
        $group1->delete();
        DB::commit();
        echo "✅ ลบกลุ่ม 1 สำเร็จ\n\n";
    } catch (\Exception $e) {
        DB::rollback();
        echo "❌ ลบกลุ่ม 1 ไม่สำเร็จ: {$e->getMessage()}\n\n";
    }
} else {
    echo "⚠️ ไม่พบกลุ่ม 1\n\n";
}

echo "📊 กลุ่มหลังลบ: " . json_encode(Group::pluck('group_id')->toArray()) . "\n\n";

// สร้างกลุ่มใหม่
echo "🆕 สร้างกลุ่มใหม่...\n";

$student = Student::whereDoesntHave('groups')->first();
if (!$student) {
    echo "❌ ไม่มี student ที่ว่าง\n";
    exit(1);
}

DB::beginTransaction();
try {
    $existingGroupIds = Group::lockForUpdate()->pluck('group_id')->sort()->values()->toArray();
    
    $nextGroupId = null;
    for ($i = 1; $i <= count($existingGroupIds) + 1; $i++) {
        if (!in_array($i, $existingGroupIds)) {
            $nextGroupId = $i;
            break;
        }
    }
    
    if ($nextGroupId === null) {
        $nextGroupId = 1;
    }
    
    echo "   → หาเลขกลุ่มได้: {$nextGroupId}\n";
    
    $group = Group::create([
        'group_id' => $nextGroupId,
        'subject_code' => 'CS303',
        'year' => 2568,
        'semester' => 1,
        'status_group' => 'created'
    ]);
    
    GroupMember::create([
        'group_id' => $group->group_id,
        'username_std' => $student->username_std
    ]);
    
    DB::commit();
    echo "   ✅ สร้างกลุ่ม {$group->group_id} สำเร็จ (student: {$student->username_std})\n\n";
    
} catch (\Exception $e) {
    DB::rollback();
    echo "   ❌ เกิดข้อผิดพลาด: {$e->getMessage()}\n\n";
}

echo "═══════════════════════════════════════════\n";
echo "📊 กลุ่มสุดท้าย: " . json_encode(Group::pluck('group_id')->toArray()) . "\n";
echo "═══════════════════════════════════════════\n\n";

if (in_array(1, Group::pluck('group_id')->toArray())) {
    echo "✅ เลขกลุ่ม 1 ถูกนำกลับมาใช้ใหม่สำเร็จ!\n";
} else {
    echo "❌ เลขกลุ่ม 1 ไม่ถูกนำกลับมาใช้\n";
}
