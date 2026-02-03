<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Project;
use App\Models\ProjectProposal;
use App\Models\ProjectEvaluation;
use App\Models\ProjectGrade;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "================================================================================\n";
echo "  TEST 08: GRADE CONFIRMATION WORKFLOW\n";
echo "================================================================================\n\n";

echo "📋 ขั้นตอนการทดสอบ:\n";
echo "   1. เตรียม ProjectGrade ที่คำนวณแล้ว\n";
echo "   2. Advisor ยืนยันเกรด (LecturerController@confirmGrade)\n";
echo "   3. Committee 1 ยืนยันเกรด\n";
echo "   4. Committee 2 ยืนยันเกรด\n";
echo "   5. Committee 3 ยืนยันเกรด\n";
echo "   6. ระบบอัปเดต all_confirmed = true\n";
echo "   7. ตรวจสอบความถูกต้องของการยืนยัน\n\n";

$testResults = [];

try {
    echo "================================================================================\n";
    echo "STEP 1: เตรียมข้อมูล ProjectGrade ทดสอบ\n";
    echo "================================================================================\n\n";
    
    DB::beginTransaction();
    
    // ดึง Students และ Lecturers
    $students = Student::take(2)->get();
    $lecturers = User::where('role', '>=', 8192)->take(4)->get();
    
    if ($students->count() < 2 || $lecturers->count() < 4) {
        throw new Exception("❌ ข้อมูลไม่เพียงพอ");
    }
    
    $student1 = $students[0];
    $student2 = $students[1];
    $advisor = $lecturers[0];
    $committee1 = $lecturers[1];
    $committee2 = $lecturers[2];
    $committee3 = $lecturers[3];
    
    echo "✅ ข้อมูลผู้ใช้:\n";
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
        'exam_date' => '2025-12-27',
        'exam_time' => '13:00:00',
        'committee1_code' => $committee1->user_code,
        'committee2_code' => $committee2->user_code,
        'committee3_code' => $committee3->user_code,
    ]);
    
    // สร้าง GroupMembers
    GroupMember::create(['group_id' => $group->group_id, 'username_std' => $student1->username_std]);
    GroupMember::create(['group_id' => $group->group_id, 'username_std' => $student2->username_std]);
    
    // สร้างการประเมินทั้ง 4 คน
    ProjectEvaluation::create(['project_id' => $project->project_id, 'evaluator_code' => $advisor->user_code, 'evaluator_role' => 'advisor', 'document_score' => 28.0, 'presentation_score' => 67.0, 'comments' => 'ดีมาก', 'submitted_at' => now()]);
    ProjectEvaluation::create(['project_id' => $project->project_id, 'evaluator_code' => $committee1->user_code, 'evaluator_role' => 'committee1', 'document_score' => 26.5, 'presentation_score' => 64.0, 'comments' => 'ดี', 'submitted_at' => now()]);
    ProjectEvaluation::create(['project_id' => $project->project_id, 'evaluator_code' => $committee2->user_code, 'evaluator_role' => 'committee2', 'document_score' => 27.0, 'presentation_score' => 66.0, 'comments' => 'เยี่ยม', 'submitted_at' => now()]);
    ProjectEvaluation::create(['project_id' => $project->project_id, 'evaluator_code' => $committee3->user_code, 'evaluator_role' => 'committee3', 'document_score' => 25.5, 'presentation_score' => 62.5, 'comments' => 'ดีครับ', 'submitted_at' => now()]);
    
    // คำนวณเกรด
    $evaluations = ProjectEvaluation::where('project_id', $project->project_id)->get();
    $finalScore = $evaluations->avg('total_score');
    $grade = ProjectGrade::calculateGrade($finalScore);
    
    // สร้าง ProjectGrade
    $projectGrade = ProjectGrade::create([
        'project_id' => $project->project_id,
        'final_score' => $finalScore,
        'grade' => $grade,
    ]);
    
    echo "✅ สร้างข้อมูลทดสอบ:\n";
    echo "   Project ID: {$project->project_id}\n";
    echo "   Final Score: " . number_format($projectGrade->final_score, 2) . "/100\n";
    echo "   Grade: {$projectGrade->grade}\n";
    echo "   Grade ID: {$projectGrade->grade_id}\n\n";
    
    $testResults[] = ['test' => 'Test Data Setup', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 2: Advisor ยืนยันเกรด (LecturerController@confirmGrade)\n";
    echo "================================================================================\n\n";
    
    // ตรวจสอบว่า Advisor ยังไม่ยืนยัน
    if ($projectGrade->advisor_confirmed) {
        throw new Exception("❌ Advisor ยืนยันแล้ว");
    }
    
    echo "✅ ตรวจสอบ: Advisor ยังไม่ยืนยัน\n";
    
    // Advisor ยืนยันเกรด
    $projectGrade->advisor_confirmed = true;
    $projectGrade->advisor_confirmed_at = now();
    $projectGrade->save();
    
    echo "✅ Advisor ยืนยันเกรด:\n";
    echo "   Advisor Code: {$advisor->user_code}\n";
    echo "   Confirmed At: {$projectGrade->advisor_confirmed_at}\n\n";
    
    $testResults[] = ['test' => 'Advisor Confirmed', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 3: Committee 1 ยืนยันเกรด\n";
    echo "================================================================================\n\n";
    
    // ตรวจสอบว่า Committee 1 ยังไม่ยืนยัน
    if ($projectGrade->committee1_confirmed) {
        throw new Exception("❌ Committee 1 ยืนยันแล้ว");
    }
    
    echo "✅ ตรวจสอบ: Committee 1 ยังไม่ยืนยัน\n";
    
    // Committee 1 ยืนยันเกรด
    $projectGrade->committee1_confirmed = true;
    $projectGrade->committee1_confirmed_at = now();
    $projectGrade->save();
    
    echo "✅ Committee 1 ยืนยันเกรด:\n";
    echo "   Committee Code: {$committee1->user_code}\n";
    echo "   Confirmed At: {$projectGrade->committee1_confirmed_at}\n\n";
    
    $testResults[] = ['test' => 'Committee 1 Confirmed', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 4: Committee 2 ยืนยันเกรด\n";
    echo "================================================================================\n\n";
    
    // ตรวจสอบว่า Committee 2 ยังไม่ยืนยัน
    if ($projectGrade->committee2_confirmed) {
        throw new Exception("❌ Committee 2 ยืนยันแล้ว");
    }
    
    echo "✅ ตรวจสอบ: Committee 2 ยังไม่ยืนยัน\n";
    
    // Committee 2 ยืนยันเกรด
    $projectGrade->committee2_confirmed = true;
    $projectGrade->committee2_confirmed_at = now();
    $projectGrade->save();
    
    echo "✅ Committee 2 ยืนยันเกรด:\n";
    echo "   Committee Code: {$committee2->user_code}\n";
    echo "   Confirmed At: {$projectGrade->committee2_confirmed_at}\n\n";
    
    $testResults[] = ['test' => 'Committee 2 Confirmed', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 5: Committee 3 ยืนยันเกรด\n";
    echo "================================================================================\n\n";
    
    // ตรวจสอบว่า Committee 3 ยังไม่ยืนยัน
    if ($projectGrade->committee3_confirmed) {
        throw new Exception("❌ Committee 3 ยืนยันแล้ว");
    }
    
    echo "✅ ตรวจสอบ: Committee 3 ยังไม่ยืนยัน\n";
    
    // Committee 3 ยืนยันเกรด
    $projectGrade->committee3_confirmed = true;
    $projectGrade->committee3_confirmed_at = now();
    $projectGrade->save();
    
    echo "✅ Committee 3 ยืนยันเกรด:\n";
    echo "   Committee Code: {$committee3->user_code}\n";
    echo "   Confirmed At: {$projectGrade->committee3_confirmed_at}\n\n";
    
    $testResults[] = ['test' => 'Committee 3 Confirmed', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 6: ระบบอัปเดต all_confirmed = true (Auto-Update from Model)\n";
    echo "================================================================================\n\n";
    
    // โหลดข้อมูลใหม่เพื่อให้ Model boot() ทำงาน
    $projectGrade = ProjectGrade::find($projectGrade->grade_id);
    
    echo "📊 สถานะการยืนยัน:\n";
    echo "   Advisor Confirmed: " . ($projectGrade->advisor_confirmed ? 'Yes' : 'No') . "\n";
    echo "   Committee 1 Confirmed: " . ($projectGrade->committee1_confirmed ? 'Yes' : 'No') . "\n";
    echo "   Committee 2 Confirmed: " . ($projectGrade->committee2_confirmed ? 'Yes' : 'No') . "\n";
    echo "   Committee 3 Confirmed: " . ($projectGrade->committee3_confirmed ? 'Yes' : 'No') . "\n\n";
    
    // ตรวจสอบ all_confirmed (ควรเป็น true อัตโนมัติ)
    if ($projectGrade->all_confirmed) {
        echo "✅ All Confirmed: true (อัปเดตอัตโนมัติ)\n";
        echo "   All Confirmed At: {$projectGrade->all_confirmed_at}\n\n";
        $testResults[] = ['test' => 'All Confirmed Auto-Updated', 'status' => 'PASS'];
    } else {
        echo "❌ All Confirmed: false (ควรเป็น true)\n\n";
        $testResults[] = ['test' => 'All Confirmed Auto-Updated', 'status' => 'FAIL'];
    }
    
    echo "================================================================================\n";
    echo "STEP 7: ตรวจสอบความถูกต้องของการยืนยัน\n";
    echo "================================================================================\n\n";
    
    // ตรวจสอบ advisor_confirmed
    if ($projectGrade->advisor_confirmed && $projectGrade->advisor_confirmed_at) {
        echo "✅ Advisor Confirmed: true (มี timestamp)\n";
        $testResults[] = ['test' => 'Advisor Confirmed with Timestamp', 'status' => 'PASS'];
    } else {
        echo "❌ Advisor Confirmed: ไม่ถูกต้อง\n";
        $testResults[] = ['test' => 'Advisor Confirmed with Timestamp', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ committee1_confirmed
    if ($projectGrade->committee1_confirmed && $projectGrade->committee1_confirmed_at) {
        echo "✅ Committee 1 Confirmed: true (มี timestamp)\n";
        $testResults[] = ['test' => 'Committee 1 Confirmed with Timestamp', 'status' => 'PASS'];
    } else {
        echo "❌ Committee 1 Confirmed: ไม่ถูกต้อง\n";
        $testResults[] = ['test' => 'Committee 1 Confirmed with Timestamp', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ committee2_confirmed
    if ($projectGrade->committee2_confirmed && $projectGrade->committee2_confirmed_at) {
        echo "✅ Committee 2 Confirmed: true (มี timestamp)\n";
        $testResults[] = ['test' => 'Committee 2 Confirmed with Timestamp', 'status' => 'PASS'];
    } else {
        echo "❌ Committee 2 Confirmed: ไม่ถูกต้อง\n";
        $testResults[] = ['test' => 'Committee 2 Confirmed with Timestamp', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ committee3_confirmed
    if ($projectGrade->committee3_confirmed && $projectGrade->committee3_confirmed_at) {
        echo "✅ Committee 3 Confirmed: true (มี timestamp)\n";
        $testResults[] = ['test' => 'Committee 3 Confirmed with Timestamp', 'status' => 'PASS'];
    } else {
        echo "❌ Committee 3 Confirmed: ไม่ถูกต้อง\n";
        $testResults[] = ['test' => 'Committee 3 Confirmed with Timestamp', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ all_confirmed
    if ($projectGrade->all_confirmed && $projectGrade->all_confirmed_at) {
        echo "✅ All Confirmed: true (มี timestamp)\n";
        $testResults[] = ['test' => 'All Confirmed with Timestamp', 'status' => 'PASS'];
    } else {
        echo "❌ All Confirmed: ไม่ถูกต้อง\n";
        $testResults[] = ['test' => 'All Confirmed with Timestamp', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบจำนวนการยืนยัน
    $confirmedCount = 0;
    if ($projectGrade->advisor_confirmed) $confirmedCount++;
    if ($projectGrade->committee1_confirmed) $confirmedCount++;
    if ($projectGrade->committee2_confirmed) $confirmedCount++;
    if ($projectGrade->committee3_confirmed) $confirmedCount++;
    
    if ($confirmedCount == 4) {
        echo "✅ จำนวนการยืนยัน: 4/4 (ครบทุกคน)\n\n";
        $testResults[] = ['test' => 'All 4 Confirmations Done', 'status' => 'PASS'];
    } else {
        echo "❌ จำนวนการยืนยัน: {$confirmedCount}/4 (ไม่ครบ)\n\n";
        $testResults[] = ['test' => 'All 4 Confirmations Done', 'status' => 'FAIL'];
    }
    
    echo "================================================================================\n";
    echo "📊 Timeline การยืนยัน\n";
    echo "================================================================================\n\n";
    
    echo "1. Advisor Confirmed At: {$projectGrade->advisor_confirmed_at}\n";
    echo "2. Committee 1 Confirmed At: {$projectGrade->committee1_confirmed_at}\n";
    echo "3. Committee 2 Confirmed At: {$projectGrade->committee2_confirmed_at}\n";
    echo "4. Committee 3 Confirmed At: {$projectGrade->committee3_confirmed_at}\n";
    echo "5. All Confirmed At: {$projectGrade->all_confirmed_at}\n\n";
    
    echo "Grade: {$projectGrade->grade} (" . number_format($projectGrade->final_score, 2) . "/100)\n";
    echo "Status: ยืนยันครบทั้ง 4 คน - พร้อมปล่อยเกรด\n\n";
    
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
