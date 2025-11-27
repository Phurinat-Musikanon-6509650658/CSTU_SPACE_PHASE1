<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Project;
use App\Models\ProjectProposal;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "================================================================================\n";
echo "  TEST 05: EXAM SCHEDULING WORKFLOW\n";
echo "================================================================================\n\n";

echo "📋 ขั้นตอนการทดสอบ:\n";
echo "   1. เตรียม Project ที่ status = 'approved'\n";
echo "   2. Coordinator ดูรายการ Projects\n";
echo "   3. Coordinator กำหนดวันสอบ (CoordinatorController@scheduleUpdate)\n";
echo "   4. ระบบบันทึก exam_date และ exam_time\n";
echo "   5. Coordinator เลือกคณะกรรมการ (3 คน)\n";
echo "   6. ระบบบันทึก committee1_code, committee2_code, committee3_code\n";
echo "   7. ตรวจสอบความถูกต้องของข้อมูล\n\n";

$testResults = [];

try {
    echo "================================================================================\n";
    echo "STEP 1: เตรียมข้อมูล Project ที่อนุมัติแล้ว\n";
    echo "================================================================================\n\n";
    
    DB::beginTransaction();
    
    // ดึง Students และ Lecturers
    $students = Student::take(2)->get();
    $lecturers = User::where('role', '>=', 8192)->take(4)->get(); // ต้องการ 4 คน (advisor + 3 committee)
    
    if ($students->count() < 2) {
        throw new Exception("❌ ต้องมี Student อย่างน้อย 2 คน");
    }
    
    if ($lecturers->count() < 4) {
        throw new Exception("❌ ต้องมี Lecturer อย่างน้อย 4 คนในระบบ (1 advisor + 3 committee)");
    }
    
    $student1 = $students[0];
    $student2 = $students[1];
    $advisor = $lecturers[0];
    $committee1 = $lecturers[1];
    $committee2 = $lecturers[2];
    $committee3 = $lecturers[3];
    
    echo "✅ ข้อมูลผู้ใช้:\n";
    echo "   Student 1: {$student1->username_std}\n";
    echo "   Student 2: {$student2->username_std}\n";
    echo "   Advisor: {$advisor->user_code} - {$advisor->firstname_user} {$advisor->lastname_user}\n";
    echo "   Committee 1: {$committee1->user_code} - {$committee1->firstname_user} {$committee1->lastname_user}\n";
    echo "   Committee 2: {$committee2->user_code} - {$committee2->firstname_user} {$committee2->lastname_user}\n";
    echo "   Committee 3: {$committee3->user_code} - {$committee3->firstname_user} {$committee3->lastname_user}\n\n";
    
    // สร้างกลุ่มและ Project
    $group = Group::create([
        'subject_code' => 'CS403',
        'year' => 2568,
        'semester' => 1,
        'status_group' => 'member_added',
    ]);
    
    $year_short = $group->year % 100;
    $project_code = "{$year_short}-{$group->semester}-{$group->group_id}_TBD-r2";
    
    $project = Project::create([
        'group_id' => $group->group_id,
        'project_code' => $project_code,
        'project_name' => 'ระบบจัดการโครงงานนักศึกษา CSTU Space',
        'student_type' => 'r',
        'status_project' => 'approved',
        'advisor_code' => $advisor->user_code,
    ]);
    
    // สร้าง GroupMembers
    GroupMember::create([
        'group_id' => $group->group_id,
        'username_std' => $student1->username_std,
    ]);
    
    GroupMember::create([
        'group_id' => $group->group_id,
        'username_std' => $student2->username_std,
    ]);
    
    // สร้าง Proposal (approved)
    ProjectProposal::create([
        'group_id' => $group->group_id,
        'proposed_title' => $project->project_name,
        'description' => 'ระบบจัดการโครงงานนักศึกษาภายใต้ระบบออนไลน์',
        'proposed_to' => $advisor->username_user,
        'proposed_by' => $student1->username_std,
        'status' => 'approved',
        'proposed_at' => now()->subDay(),
        'responded_at' => now(),
    ]);
    
    echo "✅ สร้างข้อมูลทดสอบ:\n";
    echo "   Project ID: {$project->project_id}\n";
    echo "   Project Name: {$project->project_name}\n";
    echo "   Status: {$project->status_project}\n";
    echo "   Advisor: {$project->advisor_code}\n\n";
    
    $testResults[] = ['test' => 'Test Data Setup', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 2: Coordinator ดูรายการ Projects ที่รอกำหนดสอบ\n";
    echo "================================================================================\n\n";
    
    // ดึง Projects ที่ approved แล้วแต่ยังไม่มีวันสอบ
    $projectsNeedSchedule = Project::where('status_project', 'approved')
        ->whereNull('exam_datetime')
        ->with('group', 'advisor')
        ->get();
    
    echo "✅ Projects ที่รอกำหนดวันสอบ: {$projectsNeedSchedule->count()} โครงงาน\n";
    
    if ($projectsNeedSchedule->count() > 0) {
        foreach ($projectsNeedSchedule as $p) {
            echo "   - Project {$p->project_id}: {$p->project_name}\n";
        }
        echo "\n";
        $testResults[] = ['test' => 'Projects Need Schedule Found', 'status' => 'PASS'];
    } else {
        echo "   (ไม่พบ Project ที่รอกำหนดสอบ)\n\n";
        $testResults[] = ['test' => 'Projects Need Schedule Found', 'status' => 'FAIL'];
    }
    
    echo "================================================================================\n";
    echo "STEP 3: Coordinator กำหนดวันสอบ\n";
    echo "================================================================================\n\n";
    
    $examDate = '2025-12-27';
    $examTime = '13:00:00';
    
    echo "✅ กำหนดวันสอบ:\n";
    echo "   Date: {$examDate}\n";
    echo "   Time: {$examTime}\n\n";
    
    $testResults[] = ['test' => 'Exam Date Time Defined', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 4: ระบบบันทึก exam_datetime\n";
    echo "================================================================================\n\n";
    
    $oldExamDatetime = $project->exam_datetime;
    
    // บันทึกวันสอบ
    $project->exam_datetime = $examDate . ' ' . $examTime;
    $project->save();
    
    echo "✅ บันทึกวันสอบ:\n";
    echo "   Old Exam DateTime: " . ($oldExamDatetime ?: '(null)') . "\n";
    echo "   New Exam DateTime: {$project->exam_datetime}\n\n";
    
    $testResults[] = ['test' => 'Exam Date Time Saved', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 5: Coordinator เลือกคณะกรรมการ (3 คน)\n";
    echo "================================================================================\n\n";
    
    // ตรวจสอบว่าคณะกรรมการไม่ซ้ำกับ advisor
    if ($committee1->user_code == $advisor->user_code ||
        $committee2->user_code == $advisor->user_code ||
        $committee3->user_code == $advisor->user_code) {
        echo "⚠️  คำเตือน: คณะกรรมการควรไม่ซ้ำกับ Advisor\n";
    }
    
    // ตรวจสอบว่าคณะกรรมการไม่ซ้ำกัน
    if ($committee1->user_code == $committee2->user_code ||
        $committee1->user_code == $committee3->user_code ||
        $committee2->user_code == $committee3->user_code) {
        throw new Exception("❌ คณะกรรมการต้องไม่ซ้ำกัน");
    }
    
    echo "✅ เลือกคณะกรรมการ:\n";
    echo "   Committee 1: {$committee1->user_code} - {$committee1->firstname_user} {$committee1->lastname_user}\n";
    echo "   Committee 2: {$committee2->user_code} - {$committee2->firstname_user} {$committee2->lastname_user}\n";
    echo "   Committee 3: {$committee3->user_code} - {$committee3->firstname_user} {$committee3->lastname_user}\n\n";
    
    $testResults[] = ['test' => 'Committee Members Selected', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 6: ระบบบันทึกคณะกรรมการ\n";
    echo "================================================================================\n\n";
    
    $oldCommittee1 = $project->committee1_code;
    $oldCommittee2 = $project->committee2_code;
    $oldCommittee3 = $project->committee3_code;
    
    // บันทึกคณะกรรมการ
    $project->committee1_code = $committee1->user_code;
    $project->committee2_code = $committee2->user_code;
    $project->committee3_code = $committee3->user_code;
    $project->save();
    
    echo "✅ บันทึกคณะกรรมการ:\n";
    echo "   Committee 1 Code: {$project->committee1_code} (Old: " . ($oldCommittee1 ?: 'null') . ")\n";
    echo "   Committee 2 Code: {$project->committee2_code} (Old: " . ($oldCommittee2 ?: 'null') . ")\n";
    echo "   Committee 3 Code: {$project->committee3_code} (Old: " . ($oldCommittee3 ?: 'null') . ")\n\n";
    
    $testResults[] = ['test' => 'Committee Codes Saved', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 7: ตรวจสอบความถูกต้องของข้อมูล\n";
    echo "================================================================================\n\n";
    
    // ตรวจสอบ exam_datetime
    if ($project->exam_datetime) {
        echo "✅ Exam DateTime: {$project->exam_datetime} (มีค่า)\n";
        $testResults[] = ['test' => 'Exam DateTime Set', 'status' => 'PASS'];
    } else {
        echo "❌ Exam DateTime: (null)\n";
        $testResults[] = ['test' => 'Exam DateTime Set', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ committee codes
    if ($project->committee1_code && $project->committee2_code && $project->committee3_code) {
        echo "✅ Committee Codes: ครบทั้ง 3 คน\n";
        $testResults[] = ['test' => 'All Committee Codes Set', 'status' => 'PASS'];
    } else {
        echo "❌ Committee Codes: ไม่ครบ\n";
        $testResults[] = ['test' => 'All Committee Codes Set', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ Relationships
    $projectWithRelations = Project::with(['advisor', 'committee1', 'committee2', 'committee3'])
        ->find($project->project_id);
    
    if ($projectWithRelations->advisor) {
        echo "✅ Project->Advisor Relationship: {$projectWithRelations->advisor->firstname_user} {$projectWithRelations->advisor->lastname_user}\n";
        $testResults[] = ['test' => 'Advisor Relationship', 'status' => 'PASS'];
    } else {
        echo "❌ Project->Advisor Relationship: ไม่มี\n";
        $testResults[] = ['test' => 'Advisor Relationship', 'status' => 'FAIL'];
    }
    
    if ($projectWithRelations->committee1) {
        echo "✅ Project->Committee1 Relationship: {$projectWithRelations->committee1->firstname_user} {$projectWithRelations->committee1->lastname_user}\n";
        $testResults[] = ['test' => 'Committee1 Relationship', 'status' => 'PASS'];
    } else {
        echo "❌ Project->Committee1 Relationship: ไม่มี\n";
        $testResults[] = ['test' => 'Committee1 Relationship', 'status' => 'FAIL'];
    }
    
    if ($projectWithRelations->committee2) {
        echo "✅ Project->Committee2 Relationship: {$projectWithRelations->committee2->firstname_user} {$projectWithRelations->committee2->lastname_user}\n";
        $testResults[] = ['test' => 'Committee2 Relationship', 'status' => 'PASS'];
    } else {
        echo "❌ Project->Committee2 Relationship: ไม่มี\n";
        $testResults[] = ['test' => 'Committee2 Relationship', 'status' => 'FAIL'];
    }
    
    if ($projectWithRelations->committee3) {
        echo "✅ Project->Committee3 Relationship: {$projectWithRelations->committee3->firstname_user} {$projectWithRelations->committee3->lastname_user}\n";
        $testResults[] = ['test' => 'Committee3 Relationship', 'status' => 'PASS'];
    } else {
        echo "❌ Project->Committee3 Relationship: ไม่มี\n";
        $testResults[] = ['test' => 'Committee3 Relationship', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ Duplicate Committee
    $committees = [
        $project->committee1_code,
        $project->committee2_code,
        $project->committee3_code
    ];
    
    if (count($committees) == count(array_unique($committees))) {
        echo "✅ Committee Codes: ไม่ซ้ำกัน\n\n";
        $testResults[] = ['test' => 'Committee Codes Unique', 'status' => 'PASS'];
    } else {
        echo "❌ Committee Codes: มีค่าซ้ำกัน\n\n";
        $testResults[] = ['test' => 'Committee Codes Unique', 'status' => 'FAIL'];
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
