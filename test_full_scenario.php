<?php
/**
 * ทดสอบ Scenario จริง: สร้าง 5 กลุ่ม → ลบกลุ่ม 1, 3, 4 → สร้างใหม่ 3 กลุ่ม
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

echo "🎬 Scenario Test: สร้าง → ลบ → สร้างใหม่\n";
echo "═══════════════════════════════════════════\n\n";

// รีเซ็ตข้อมูล
echo "🔄 รีเซ็ตข้อมูล...\n";
DB::table('project_proposals')->delete();
DB::table('projects')->delete();
DB::table('group_invitations')->delete();
DB::table('group_members')->delete();
DB::table('groups')->delete();
echo "✅ เสร็จสิ้น\n\n";

// ฟังก์ชันสร้างกลุ่ม
function createGroup($groupId = null) {
    $student = Student::whereDoesntHave('groups')->first();
    if (!$student) {
        echo "❌ ไม่มี student ว่าง\n";
        return null;
    }
    
    DB::beginTransaction();
    try {
        $existingGroupIds = Group::lockForUpdate()->pluck('group_id')->sort()->values()->toArray();
        
        if ($groupId === null) {
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
        } else {
            $nextGroupId = $groupId;
        }
        
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
        return $group->group_id;
        
    } catch (\Exception $e) {
        DB::rollback();
        echo "❌ Error: {$e->getMessage()}\n";
        return null;
    }
}

// Step 1: สร้าง 5 กลุ่ม
echo "📝 Step 1: สร้าง 5 กลุ่ม\n";
echo "────────────────────────────────────────────\n";
for ($i = 1; $i <= 5; $i++) {
    $id = createGroup();
    if ($id) {
        echo "✅ สร้างกลุ่ม {$id}\n";
    }
}
$groups = Group::pluck('group_id')->toArray();
echo "\n📊 กลุ่มทั้งหมด: " . json_encode($groups) . "\n\n";

// Step 2: ลบกลุ่ม 1, 3, 4
echo "📝 Step 2: ลบกลุ่ม 1, 3, 4\n";
echo "────────────────────────────────────────────\n";
foreach ([1, 3, 4] as $groupId) {
    $group = Group::find($groupId);
    if ($group) {
        DB::beginTransaction();
        try {
            $group->members()->delete();
            if ($group->project) {
                $group->project->delete();
            }
            $group->delete();
            DB::commit();
            echo "🗑️ ลบกลุ่ม {$groupId}\n";
        } catch (\Exception $e) {
            DB::rollback();
            echo "❌ ลบกลุ่ม {$groupId} ไม่สำเร็จ\n";
        }
    }
}
$groups = Group::pluck('group_id')->toArray();
echo "\n📊 กลุ่มที่เหลือ: " . json_encode($groups) . "\n";
echo "   (ควรเป็น [2, 5])\n\n";

// Step 3: สร้างใหม่ 3 กลุ่ม
echo "📝 Step 3: สร้างกลุ่มใหม่ 3 กลุ่ม\n";
echo "────────────────────────────────────────────\n";
$newGroups = [];
for ($i = 1; $i <= 3; $i++) {
    $id = createGroup();
    if ($id) {
        $newGroups[] = $id;
        echo "✅ สร้างกลุ่ม {$id}\n";
    }
}
$groups = Group::pluck('group_id')->sort()->values()->toArray();
echo "\n📊 กลุ่มสุดท้าย: " . json_encode($groups) . "\n";
echo "   (ควรเป็น [1, 2, 3, 4, 5])\n\n";

// ตรวจสอบผลลัพธ์
echo "═══════════════════════════════════════════\n";
echo "📊 สรุปผลการทดสอบ\n";
echo "═══════════════════════════════════════════\n\n";

$expected = [1, 2, 3, 4, 5];
if ($groups === $expected) {
    echo "✅ PASS: เลขกลุ่มต่อเนื่องจาก 1-5 (นำเลขที่ถูกลบกลับมาใช้สำเร็จ)\n";
    echo "   กลุ่มใหม่ที่สร้าง: " . json_encode($newGroups) . "\n";
    echo "   (ควรได้ [1, 3, 4] ตามลำดับที่ว่าง)\n";
    
    if ($newGroups === [1, 3, 4]) {
        echo "\n🎉 ระบบทำงานสมบูรณ์แบบ!\n";
    } else {
        echo "\n⚠️ ลำดับไม่ตรงตามที่คาดหวัง\n";
    }
} else {
    echo "❌ FAIL: เลขกลุ่มไม่ต่อเนื่อง\n";
    echo "   คาดหวัง: " . json_encode($expected) . "\n";
    echo "   ได้จริง: " . json_encode($groups) . "\n";
}
