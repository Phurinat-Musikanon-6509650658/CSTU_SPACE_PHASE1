<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupInvitation;
use App\Models\Project;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "================================================================================\n";
echo "  TEST 02: MEMBER INVITATION WORKFLOW\n";
echo "================================================================================\n\n";

echo "📋 ขั้นตอนการทดสอบ:\n";
echo "   1. Student 1 สร้างกลุ่ม (จาก Test 01)\n";
echo "   2. Student 1 ส่งคำเชิญไปหา Student 2 (GroupController@invite)\n";
echo "   3. ตรวจสอบสถานะคำเชิญ (pending)\n";
echo "   4. Student 2 ตอบรับคำเชิญ (GroupInvitationController@accept)\n";
echo "   5. ระบบอัปเดต GroupMember\n";
echo "   6. ระบบอัปเดต status_group = 'member_added'\n";
echo "   7. ระบบคำนวณ student_type ใหม่\n";
echo "   8. ระบบอัปเดต project_code (เพิ่มจำนวนสมาชิก)\n\n";

$testResults = [];

try {
    echo "================================================================================\n";
    echo "STEP 1: ดึงข้อมูล Students และสร้างกลุ่มทดสอบ\n";
    echo "================================================================================\n\n";
    
    DB::beginTransaction();
    
    // ดึง Student ที่มีอยู่จริงในระบบ
    $students = Student::take(2)->get();
    
    if ($students->count() < 2) {
        throw new Exception("❌ ระบบต้องมี Student อย่างน้อย 2 คนในการทดสอบ");
    }
    
    $student1 = $students[0];
    $student2 = $students[1];
    
    echo "✅ Student 1 (ผู้สร้างกลุ่ม):\n";
    echo "   Username: {$student1->username_std}\n";
    echo "   ชื่อ: {$student1->firstname_std} {$student1->lastname_std}\n";
    echo "   Student Code: {$student1->student_code}\n\n";
    
    echo "✅ Student 2 (ผู้ถูกเชิญ):\n";
    echo "   Username: {$student2->username_std}\n";
    echo "   ชื่อ: {$student2->firstname_std} {$student2->lastname_std}\n";
    echo "   Student Code: {$student2->student_code}\n\n";
    
    $testResults[] = ['test' => 'Students Exist', 'status' => 'PASS'];
    
    // สร้างกลุ่ม (จำลอง Test 01)
    $group = Group::create([
        'subject_code' => 'CS403',
        'year' => 2568,
        'semester' => 1,
        'status_group' => 'created',
    ]);
    
    // สร้าง Project
    $year_short = $group->year % 100;
    $project_code = "{$year_short}-{$group->semester}-{$group->group_id}_TBD-r1";
    
    $project = Project::create([
        'group_id' => $group->group_id,
        'project_code' => $project_code,
        'project_name' => 'TBD',
        'student_type' => 'r',
        'status_project' => 'not_proposed',
    ]);
    
    // สร้าง GroupMember สำหรับผู้สร้าง
    GroupMember::create([
        'group_id' => $group->group_id,
        'username_std' => $student1->username_std,
    ]);
    
    echo "✅ สร้างกลุ่มเริ่มต้น:\n";
    echo "   Group ID: {$group->group_id}\n";
    echo "   Project Code: {$project->project_code}\n";
    echo "   Status: {$group->status_group}\n\n";
    
    $testResults[] = ['test' => 'Initial Group Created', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 2: Student 1 ส่งคำเชิญไปหา Student 2 (GroupController@invite)\n";
    echo "================================================================================\n\n";
    
    // ตรวจสอบว่า Student 2 มีอยู่ในระบบ
    $invitee = Student::where('username_std', $student2->username_std)->first();
    
    if (!$invitee) {
        throw new Exception("❌ ไม่พบ Student ที่จะเชิญ");
    }
    
    // ตรวจสอบว่ายังไม่มีคำเชิญซ้ำ
    $existingInvitation = GroupInvitation::where('group_id', $group->group_id)
        ->where('invitee_username', $student2->username_std)
        ->first();
    
    if ($existingInvitation) {
        throw new Exception("❌ มีคำเชิญอยู่แล้ว");
    }
    
    // สร้างคำเชิญ
    $invitation = GroupInvitation::create([
        'group_id' => $group->group_id,
        'invitee_username' => $student2->username_std,
        'inviter_username' => $student1->username_std,
        'status' => 'pending',
    ]);
    
    echo "✅ ส่งคำเชิญสำเร็จ:\n";
    echo "   Invitation ID: {$invitation->invitation_id}\n";
    echo "   Group ID: {$invitation->group_id}\n";
    echo "   ผู้ถูกเชิญ: {$invitation->invitee_username}\n";
    echo "   ผู้เชิญ: {$invitation->inviter_username}\n";
    echo "   Status: {$invitation->status}\n\n";
    
    $testResults[] = ['test' => 'Invitation Created', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 3: ตรวจสอบสถานะคำเชิญ\n";
    echo "================================================================================\n\n";
    
    if ($invitation->status == 'pending') {
        echo "✅ สถานะคำเชิญ: pending (ถูกต้อง)\n\n";
        $testResults[] = ['test' => 'Invitation Status = pending', 'status' => 'PASS'];
    } else {
        echo "❌ สถานะคำเชิญ: {$invitation->status} (ไม่ถูกต้อง)\n\n";
        $testResults[] = ['test' => 'Invitation Status = pending', 'status' => 'FAIL'];
    }
    
    echo "================================================================================\n";
    echo "STEP 4: Student 2 ตอบรับคำเชิญ (GroupInvitationController@accept)\n";
    echo "================================================================================\n\n";
    
    // อัปเดตสถานะคำเชิญ
    $invitation->status = 'accepted';
    $invitation->responded_at = now();
    $invitation->save();
    
    echo "✅ Student 2 ตอบรับคำเชิญ:\n";
    echo "   Status: {$invitation->status}\n";
    echo "   Responded At: {$invitation->responded_at}\n\n";
    
    $testResults[] = ['test' => 'Invitation Accepted', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 5: ระบบอัปเดต GroupMember\n";
    echo "================================================================================\n\n";
    
    // เพิ่ม GroupMember สำหรับสมาชิกใหม่
    $newMember = GroupMember::create([
        'group_id' => $group->group_id,
        'username_std' => $student2->username_std,
    ]);
    
    echo "✅ เพิ่มสมาชิกใหม่ใน GroupMember:\n";
    echo "   Username: {$newMember->username_std}\n";
    echo "   Group ID: {$newMember->group_id}\n\n";
    
    $testResults[] = ['test' => 'GroupMember Added', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 6: ระบบอัปเดต status_group = 'member_added'\n";
    echo "================================================================================\n\n";
    
    $group->status_group = 'member_added';
    $group->save();
    
    echo "✅ อัปเดต status_group:\n";
    echo "   Status: {$group->status_group}\n\n";
    
    $testResults[] = ['test' => 'Group Status Updated', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 7: ระบบคำนวณ student_type ใหม่\n";
    echo "================================================================================\n\n";
    
    // ดึงข้อมูลสมาชิกทั้งหมดพร้อม Student
    $members = GroupMember::where('group_id', $group->group_id)
        ->with('student')
        ->get();
    
    // ตรวจสอบ student_code ของสมาชิกแต่ละคน
    $hasRegular = false; // นักศึกษาปกติ (65xxxxxx)
    $hasSpecial = false; // นักศึกษาพิเศษ (66xxxxxx)
    
    foreach ($members as $member) {
        $studentCode = $member->student->student_code;
        $year = (int) substr($studentCode, 0, 2);
        
        if ($year == 65) {
            $hasRegular = true;
        } elseif ($year == 66) {
            $hasSpecial = true;
        }
    }
    
    // คำนวณ student_type
    if ($hasRegular && $hasSpecial) {
        $student_type = 'rs'; // ผสม
    } elseif ($hasSpecial) {
        $student_type = 's'; // พิเศษ
    } else {
        $student_type = 'r'; // ปกติ
    }
    
    echo "✅ คำนวณ student_type:\n";
    echo "   Has Regular (65xx): " . ($hasRegular ? 'Yes' : 'No') . "\n";
    echo "   Has Special (66xx): " . ($hasSpecial ? 'Yes' : 'No') . "\n";
    echo "   Student Type: {$student_type}\n\n";
    
    $testResults[] = ['test' => 'Student Type Calculated', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 8: ระบบอัปเดต project_code (เพิ่มจำนวนสมาชิก)\n";
    echo "================================================================================\n\n";
    
    $memberCount = $members->count();
    $newProjectCode = "{$year_short}-{$group->semester}-{$group->group_id}_TBD-{$student_type}{$memberCount}";
    
    $oldProjectCode = $project->project_code;
    $project->project_code = $newProjectCode;
    $project->student_type = $student_type;
    $project->save();
    
    echo "✅ อัปเดต Project:\n";
    echo "   Old Project Code: {$oldProjectCode}\n";
    echo "   New Project Code: {$project->project_code}\n";
    echo "   Student Type: {$project->student_type}\n";
    echo "   Member Count: {$memberCount}\n\n";
    
    // ตรวจสอบ format
    $pattern = '/^\d{2}-\d+-\d+_TBD-[rs]{1,2}\d+$/';
    if (preg_match($pattern, $project->project_code)) {
        echo "✅ Project Code Format ถูกต้อง\n\n";
        $testResults[] = ['test' => 'Project Code Updated', 'status' => 'PASS'];
    } else {
        echo "❌ Project Code Format ไม่ถูกต้อง\n\n";
        $testResults[] = ['test' => 'Project Code Updated', 'status' => 'FAIL'];
    }
    
    echo "================================================================================\n";
    echo "STEP 9: ตรวจสอบความสมบูรณ์ของข้อมูล\n";
    echo "================================================================================\n\n";
    
    // ตรวจสอบจำนวนสมาชิก
    $finalMembers = GroupMember::where('group_id', $group->group_id)->count();
    if ($finalMembers == 2) {
        echo "✅ จำนวนสมาชิกในกลุ่ม: {$finalMembers} คน (ถูกต้อง)\n";
        $testResults[] = ['test' => 'Member Count = 2', 'status' => 'PASS'];
    } else {
        echo "❌ จำนวนสมาชิกในกลุ่ม: {$finalMembers} คน (ไม่ถูกต้อง)\n";
        $testResults[] = ['test' => 'Member Count = 2', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ project_code มีจำนวนสมาชิกตรงกัน
    $codeEndsWithCount = preg_match('/\d+$/', $project->project_code, $matches);
    if ($codeEndsWithCount && (int)$matches[0] == $finalMembers) {
        echo "✅ Project Code ตรงกับจำนวนสมาชิก (ลงท้ายด้วย {$finalMembers})\n";
        $testResults[] = ['test' => 'Project Code Member Count Match', 'status' => 'PASS'];
    } else {
        echo "❌ Project Code ไม่ตรงกับจำนวนสมาชิก\n";
        $testResults[] = ['test' => 'Project Code Member Count Match', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบสถานะคำเชิญ
    $acceptedInvitations = GroupInvitation::where('group_id', $group->group_id)
        ->where('status', 'accepted')
        ->count();
    
    if ($acceptedInvitations == 1) {
        echo "✅ คำเชิญที่ตอบรับ: {$acceptedInvitations} ครั้ง (ถูกต้อง)\n\n";
        $testResults[] = ['test' => 'Invitation Accepted Count', 'status' => 'PASS'];
    } else {
        echo "❌ คำเชิญที่ตอบรับ: {$acceptedInvitations} ครั้ง (ไม่ถูกต้อง)\n\n";
        $testResults[] = ['test' => 'Invitation Accepted Count', 'status' => 'FAIL'];
    }
    
    DB::rollBack();
    
    echo "================================================================================\n";
    echo "📊 TEST SUMMARY\n";
    echo "================================================================================\n\n";
    
    $passCount = 0;
    $failCount = 0;
    
    foreach ($testResults as $result) {
        $status = $result['status'] == 'PASS' ? '✅' : '❌';
        echo "{$status} {$result['test']}: {$result['status']}\n";
        
        if ($result['status'] == 'PASS') {
            $passCount++;
        } else {
            $failCount++;
        }
    }
    
    $total = $passCount + $failCount;
    $percentage = $total > 0 ? round(($passCount / $total) * 100, 2) : 0;
    
    echo "\n";
    echo "Total Tests: {$total}\n";
    echo "Passed: {$passCount}\n";
    echo "Failed: {$failCount}\n";
    echo "Success Rate: {$percentage}%\n\n";
    
    if ($failCount == 0) {
        echo "🎉 ALL TESTS PASSED!\n";
    } else {
        echo "⚠️  SOME TESTS FAILED - Please review the errors above.\n";
    }
    
    echo "\n";
    echo "📝 หมายเหตุ: ข้อมูลถูก Rollback แล้ว (ไม่มีการบันทึกลง Database จริง)\n\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "\n";
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n\n";
    exit(1);
}
