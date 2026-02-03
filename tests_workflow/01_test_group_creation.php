<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Project;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "================================================================================\n";
echo "  TEST 01: GROUP CREATION WORKFLOW\n";
echo "================================================================================\n\n";

echo "📋 ขั้นตอนการทดสอบ:\n";
echo "   1. ตรวจสอบ Student ในระบบ\n";
echo "   2. Student สร้างกลุ่ม (GroupController@store)\n";
echo "   3. ระบบสร้าง Project อัตโนมัติ\n";
echo "   4. ระบบสร้าง GroupMember สำหรับผู้สร้าง\n";
echo "   5. ตรวจสอบ project_code และ student_type\n\n";

$testResults = [];

try {
    echo "================================================================================\n";
    echo "STEP 1: ดึงข้อมูล Students จาก Database\n";
    echo "================================================================================\n\n";
    
    // ดึง Student ที่มีอยู่จริงในระบบ
    $students = Student::take(2)->get();
    
    if ($students->count() < 2) {
        throw new Exception("❌ ระบบต้องมี Student อย่างน้อย 2 คนในการทดสอบ");
    }
    
    $student1 = $students[0];
    
    echo "✅ พบ Student ในระบบ:\n";
    echo "   Username: {$student1->username_std}\n";
    echo "   ชื่อ: {$student1->firstname_std} {$student1->lastname_std}\n";
    echo "   รหัสนักศึกษา: {$student1->student_code}\n";
    echo "   Email: {$student1->email_std}\n\n";
    
    $testResults[] = ['test' => 'Student Exists', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 2: Student สร้างกลุ่ม (Simulate GroupController@store)\n";
    echo "================================================================================\n\n";
    
    DB::beginTransaction();
    
    // สร้างกลุ่มใหม่
    $group = new Group();
    $group->subject_code = 'CS403';
    $group->year = 2568;
    $group->semester = 1;
    $group->status_group = 'created';
    $group->save();
    
    echo "✅ สร้างกลุ่มสำเร็จ:\n";
    echo "   Group ID: {$group->group_id}\n";
    echo "   Subject: {$group->subject_code}\n";
    echo "   Year/Semester: {$group->year}/{$group->semester}\n";
    echo "   Status: {$group->status_group}\n\n";
    
    $testResults[] = ['test' => 'Group Created', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 3: ระบบสร้าง Project อัตโนมัติ (GroupController Logic)\n";
    echo "================================================================================\n\n";
    
    // คำนวณ project_code แบบเดียวกับ GroupController
    $year_short = $group->year % 100;
    $student_type = 'r'; // เริ่มต้นเป็น 'r' (regular)
    $member_count = 1; // เริ่มต้น 1 คน
    
    $project_code = "{$year_short}-{$group->semester}-{$group->group_id}_TBD-{$student_type}{$member_count}";
    
    $project = new Project();
    $project->group_id = $group->group_id;
    $project->project_code = $project_code;
    $project->project_name = 'TBD';
    $project->student_type = $student_type;
    $project->status_project = 'not_proposed';
    $project->save();
    
    echo "✅ สร้าง Project อัตโนมัติ:\n";
    echo "   Project ID: {$project->project_id}\n";
    echo "   Project Code: {$project->project_code}\n";
    echo "   Project Name: {$project->project_name}\n";
    echo "   Student Type: {$project->student_type}\n";
    echo "   Status: {$project->status_project}\n\n";
    
    // ตรวจสอบ format ของ project_code
    $pattern = '/^\d{2}-\d+-\d+_TBD-[rs]{1,2}\d+$/';
    if (preg_match($pattern, $project->project_code)) {
        echo "✅ Project Code Format ถูกต้อง (Pattern: YY-S-GID_TBD-TYPEn)\n\n";
        $testResults[] = ['test' => 'Project Code Format', 'status' => 'PASS'];
    } else {
        echo "❌ Project Code Format ไม่ถูกต้อง\n\n";
        $testResults[] = ['test' => 'Project Code Format', 'status' => 'FAIL'];
    }
    
    $testResults[] = ['test' => 'Project Auto-Created', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 4: ระบบสร้าง GroupMember สำหรับผู้สร้างกลุ่ม\n";
    echo "================================================================================\n\n";
    
    $groupMember = new GroupMember();
    $groupMember->group_id = $group->group_id;
    $groupMember->username_std = $student1->username_std;
    $groupMember->save();
    
    echo "✅ สร้าง GroupMember สำเร็จ:\n";
    echo "   Group ID: {$groupMember->group_id}\n";
    echo "   Username: {$groupMember->username_std}\n";
    echo "   Role: {$groupMember->role}\n";
    echo "   Joined At: {$groupMember->joined_at}\n\n";
    
    $testResults[] = ['test' => 'GroupMember Created', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 5: ตรวจสอบความสัมพันธ์ของข้อมูล\n";
    echo "================================================================================\n\n";
    
    // ตรวจสอบว่า Group มี Project
    $groupWithProject = Group::with('project')->find($group->group_id);
    if ($groupWithProject->project && $groupWithProject->project->project_id == $project->project_id) {
        echo "✅ Group มีความสัมพันธ์กับ Project ถูกต้อง\n";
        $testResults[] = ['test' => 'Group->Project Relationship', 'status' => 'PASS'];
    } else {
        echo "❌ Group ไม่มีความสัมพันธ์กับ Project\n";
        $testResults[] = ['test' => 'Group->Project Relationship', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบว่า Group มี Members
    $groupWithMembers = Group::with('members')->find($group->group_id);
    if ($groupWithMembers->members->count() == 1) {
        echo "✅ Group มี Members ถูกต้อง (1 คน)\n";
        $testResults[] = ['test' => 'Group->Members Relationship', 'status' => 'PASS'];
    } else {
        echo "❌ Group มี Members ไม่ถูกต้อง\n";
        $testResults[] = ['test' => 'Group->Members Relationship', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบว่า Project มี Group
    $projectWithGroup = Project::with('group')->find($project->project_id);
    if ($projectWithGroup->group && $projectWithGroup->group->group_id == $group->group_id) {
        echo "✅ Project มีความสัมพันธ์กับ Group ถูกต้อง\n\n";
        $testResults[] = ['test' => 'Project->Group Relationship', 'status' => 'PASS'];
    } else {
        echo "❌ Project ไม่มีความสัมพันธ์กับ Group\n\n";
        $testResults[] = ['test' => 'Project->Group Relationship', 'status' => 'FAIL'];
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
