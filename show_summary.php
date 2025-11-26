<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Student;
use App\Models\Group;
use App\Models\Project;
use App\Models\GroupInvitation;

echo "📊 สรุปข้อมูลในระบบ\n";
echo "═══════════════════════════════════════════\n\n";

echo "👥 Users: " . User::count() . " คน\n";
echo "🎓 Students: " . Student::count() . " คน\n";
echo "📁 Groups: " . Group::count() . " กลุ่ม\n";
echo "📝 Projects: " . Project::count() . " โครงงาน\n";
echo "✉️ Invitations: " . GroupInvitation::count() . " คำเชิญ\n\n";

echo "📋 รายละเอียดกลุ่ม:\n";
echo "───────────────────────────────────────────\n";
foreach(Group::with('members')->get() as $g) {
    $members = $g->members->pluck('username_std')->join(', ');
    $count = $g->members->count();
    echo sprintf(
        "  กลุ่ม %02d: %d คน (%s) - status: %s\n", 
        $g->group_id, 
        $count,
        $members, 
        $g->status_group
    );
}

echo "\n";
echo "🔑 Test Accounts:\n";
echo "───────────────────────────────────────────\n";
echo "  Student (เดี่ยว):     student / student123\n";
echo "  Student (กลุ่ม 2):    6509650757 / password\n";
echo "  Student (มีเชิญรอ):  6509650658 / password\n";
echo "  Lecturer:             wanida.p / password\n";
echo "  Coordinator:          coordinator / password\n\n";

echo "💡 กรณีทดสอบ:\n";
echo "───────────────────────────────────────────\n";
echo "  ✓ กลุ่ม 1: กลุ่มเดี่ยว (1 คน)\n";
echo "  ✓ กลุ่ม 2: กลุ่ม 2 คน (พร้อมเสนอหัวข้อ)\n";
echo "  ✓ กลุ่ม 3: กลุ่มที่มีคำเชิญรอ (ต้องยกเลิกก่อนเสนอ)\n";
echo "  ✓ กลุ่ม 4: กลุ่มที่เพิ่งมีสมาชิก (member_added)\n";
echo "  ✓ กลุ่ม 5: ทดสอบลบกลุ่ม → นำเลข 5 กลับมาใช้\n\n";

echo "═══════════════════════════════════════════\n";
echo "✅ ระบบพร้อมใช้งาน!\n";
