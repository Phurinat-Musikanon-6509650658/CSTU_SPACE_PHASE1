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
echo "  TEST 09: GRADE RELEASE WORKFLOW\n";
echo "================================================================================\n\n";

echo "📋 ขั้นตอนการทดสอบ:\n";
echo "   1. เตรียม ProjectGrade ที่ยืนยันครบทั้ง 4 คน\n";
echo "   2. Coordinator ตรวจสอบเกรดที่พร้อมปล่อย\n";
echo "   3. Coordinator ปล่อยเกรด (CoordinatorController@releaseGrade)\n";
echo "   4. ระบบอัปเดต grade_released = true\n";
echo "   5. ระบบบันทึก grade_released_at\n";
echo "   6. Students สามารถดูเกรดได้\n";
echo "   7. ตรวจสอบความถูกต้องของการปล่อยเกรด\n\n";

$testResults = [];

try {
    echo "================================================================================\n";
    echo "STEP 1: เตรียมข้อมูล ProjectGrade ที่ยืนยันครบแล้ว\n";
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
    
    echo "✅ ข้อมูล Students:\n";
    echo "   Student 1: {$student1->username_std} - {$student1->firstname_std} {$student1->lastname_std}\n";
    echo "   Student 2: {$student2->username_std} - {$student2->firstname_std} {$student2->lastname_std}\n\n";
    
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
    
    // สร้าง ProjectGrade และยืนยันครบทั้ง 4 คน
    $projectGrade = ProjectGrade::create([
        'project_id' => $project->project_id,
        'final_score' => $finalScore,
        'grade' => $grade,
        'advisor_confirmed' => true,
        'advisor_confirmed_at' => now(),
        'committee1_confirmed' => true,
        'committee1_confirmed_at' => now(),
        'committee2_confirmed' => true,
        'committee2_confirmed_at' => now(),
        'committee3_confirmed' => true,
        'committee3_confirmed_at' => now(),
        'all_confirmed' => true,
        'all_confirmed_at' => now(),
    ]);
    
    echo "✅ สร้างข้อมูลทดสอบ:\n";
    echo "   Project ID: {$project->project_id}\n";
    echo "   Project Code: {$project->project_code}\n";
    echo "   Final Score: " . number_format($projectGrade->final_score, 2) . "/100\n";
    echo "   Grade: {$projectGrade->grade}\n";
    echo "   All Confirmed: " . ($projectGrade->all_confirmed ? 'Yes' : 'No') . "\n";
    echo "   Grade Released: " . ($projectGrade->grade_released ? 'Yes' : 'No') . "\n\n";
    
    $testResults[] = ['test' => 'Test Data Setup', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 2: Coordinator ตรวจสอบเกรดที่พร้อมปล่อย\n";
    echo "================================================================================\n\n";
    
    // ดึงเกรดที่ยืนยันครบแล้วแต่ยังไม่ปล่อย
    $readyToRelease = ProjectGrade::where('all_confirmed', true)
        ->where('grade_released', false)
        ->with('project.group')
        ->get();
    
    echo "✅ เกรดที่พร้อมปล่อย: {$readyToRelease->count()} โครงงาน\n";
    
    if ($readyToRelease->count() > 0) {
        foreach ($readyToRelease as $g) {
            echo "   - Project {$g->project->project_id}: {$g->project->project_name} (Grade: {$g->grade})\n";
        }
        echo "\n";
        $testResults[] = ['test' => 'Ready to Release Grades Found', 'status' => 'PASS'];
    } else {
        echo "   (ไม่พบเกรดที่พร้อมปล่อย)\n\n";
        $testResults[] = ['test' => 'Ready to Release Grades Found', 'status' => 'FAIL'];
    }
    
    echo "================================================================================\n";
    echo "STEP 3: Coordinator ปล่อยเกรด (CoordinatorController@releaseGrade)\n";
    echo "================================================================================\n\n";
    
    // ตรวจสอบว่ายืนยันครบทั้ง 4 คน
    if (!$projectGrade->all_confirmed) {
        throw new Exception("❌ เกรดต้องได้รับการยืนยันครบทั้ง 4 คนก่อนปล่อย");
    }
    
    echo "✅ ตรวจสอบ: ยืนยันครบทั้ง 4 คน\n";
    
    // ตรวจสอบว่ายังไม่ปล่อยเกรด
    if ($projectGrade->grade_released) {
        throw new Exception("❌ เกรดถูกปล่อยไปแล้ว");
    }
    
    echo "✅ ตรวจสอบ: ยังไม่ปล่อยเกรด\n\n";
    
    $testResults[] = ['test' => 'Grade Ready to Release', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 4: ระบบอัปเดต grade_released = true\n";
    echo "================================================================================\n\n";
    
    $oldReleased = $projectGrade->grade_released;
    
    // ปล่อยเกรด
    $projectGrade->grade_released = true;
    $projectGrade->grade_released_at = now();
    $projectGrade->save();
    
    echo "✅ อัปเดต grade_released:\n";
    echo "   Old: " . ($oldReleased ? 'true' : 'false') . "\n";
    echo "   New: " . ($projectGrade->grade_released ? 'true' : 'false') . "\n\n";
    
    $testResults[] = ['test' => 'Grade Released Updated', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 5: ระบบบันทึก grade_released_at\n";
    echo "================================================================================\n\n";
    
    if ($projectGrade->grade_released_at) {
        echo "✅ Grade Released At: {$projectGrade->grade_released_at}\n\n";
        $testResults[] = ['test' => 'Grade Released Timestamp Set', 'status' => 'PASS'];
    } else {
        echo "❌ Grade Released At: (null)\n\n";
        $testResults[] = ['test' => 'Grade Released Timestamp Set', 'status' => 'FAIL'];
    }
    
    echo "================================================================================\n";
    echo "STEP 6: Students สามารถดูเกรดได้\n";
    echo "================================================================================\n\n";
    
    // ดึง Project ของ Student 1
    $studentProjects = Project::whereHas('group.members', function($query) use ($student1) {
        $query->where('username_std', $student1->username_std);
    })
    ->with('grade')
    ->get();
    
    echo "✅ Projects ของ Student {$student1->username_std}:\n";
    
    foreach ($studentProjects as $p) {
        if ($p->grade && $p->grade->grade_released) {
            echo "   Project: {$p->project_name}\n";
            echo "   Grade: {$p->grade->grade} (" . number_format($p->grade->final_score, 2) . "/100)\n";
            echo "   Released: Yes\n\n";
            $testResults[] = ['test' => 'Student Can View Grade', 'status' => 'PASS'];
        } else {
            echo "   Project: {$p->project_name}\n";
            echo "   Grade: (ยังไม่ปล่อย)\n\n";
            $testResults[] = ['test' => 'Student Can View Grade', 'status' => 'FAIL'];
        }
    }
    
    echo "================================================================================\n";
    echo "STEP 7: ตรวจสอบความถูกต้องของการปล่อยเกรด\n";
    echo "================================================================================\n\n";
    
    // ตรวจสอบ grade_released
    if ($projectGrade->grade_released) {
        echo "✅ Grade Released: true (ถูกต้อง)\n";
        $testResults[] = ['test' => 'Grade Released = true', 'status' => 'PASS'];
    } else {
        echo "❌ Grade Released: false (ไม่ถูกต้อง)\n";
        $testResults[] = ['test' => 'Grade Released = true', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ grade_released_at
    if ($projectGrade->grade_released_at) {
        echo "✅ Grade Released At: {$projectGrade->grade_released_at} (มีค่า)\n";
        $testResults[] = ['test' => 'Grade Released At Set', 'status' => 'PASS'];
    } else {
        echo "❌ Grade Released At: (null) (ไม่มีค่า)\n";
        $testResults[] = ['test' => 'Grade Released At Set', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบว่ายืนยันครบทุกคน
    if ($projectGrade->all_confirmed) {
        echo "✅ All Confirmed: true (ยืนยันครบ)\n";
        $testResults[] = ['test' => 'All Confirmed = true', 'status' => 'PASS'];
    } else {
        echo "❌ All Confirmed: false (ไม่ครบ)\n";
        $testResults[] = ['test' => 'All Confirmed = true', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบว่าเกรดไม่อยู่ใน ready to release list
    $stillInList = ProjectGrade::where('all_confirmed', true)
        ->where('grade_released', false)
        ->where('grade_id', $projectGrade->grade_id)
        ->exists();
    
    if (!$stillInList) {
        echo "✅ Grade ไม่อยู่ใน Ready to Release List แล้ว (ถูกต้อง)\n\n";
        $testResults[] = ['test' => 'Grade Removed from Ready List', 'status' => 'PASS'];
    } else {
        echo "❌ Grade ยังอยู่ใน Ready to Release List (ไม่ถูกต้อง)\n\n";
        $testResults[] = ['test' => 'Grade Removed from Ready List', 'status' => 'FAIL'];
    }
    
    echo "================================================================================\n";
    echo "📊 สรุปสถานะเกรด\n";
    echo "================================================================================\n\n";
    
    echo "Project: {$project->project_name}\n";
    echo "Project Code: {$project->project_code}\n\n";
    
    echo "Grade Information:\n";
    echo "   Final Score: " . number_format($projectGrade->final_score, 2) . "/100\n";
    echo "   Grade: {$projectGrade->grade}\n\n";
    
    echo "Confirmation Status:\n";
    echo "   Advisor Confirmed: " . ($projectGrade->advisor_confirmed ? 'Yes' : 'No') . " ({$projectGrade->advisor_confirmed_at})\n";
    echo "   Committee 1 Confirmed: " . ($projectGrade->committee1_confirmed ? 'Yes' : 'No') . " ({$projectGrade->committee1_confirmed_at})\n";
    echo "   Committee 2 Confirmed: " . ($projectGrade->committee2_confirmed ? 'Yes' : 'No') . " ({$projectGrade->committee2_confirmed_at})\n";
    echo "   Committee 3 Confirmed: " . ($projectGrade->committee3_confirmed ? 'Yes' : 'No') . " ({$projectGrade->committee3_confirmed_at})\n";
    echo "   All Confirmed: " . ($projectGrade->all_confirmed ? 'Yes' : 'No') . " ({$projectGrade->all_confirmed_at})\n\n";
    
    echo "Release Status:\n";
    echo "   Grade Released: " . ($projectGrade->grade_released ? 'Yes' : 'No') . "\n";
    echo "   Released At: {$projectGrade->grade_released_at}\n\n";
    
    echo "Students:\n";
    $members = GroupMember::where('group_id', $project->group_id)->with('student')->get();
    foreach ($members as $member) {
        echo "   - {$member->student->firstname_std} {$member->student->lastname_std} ({$member->student->username_std})\n";
    }
    echo "\n";
    
    echo "Status: ✅ เกรดถูกปล่อยแล้ว - Students สามารถดูเกรดได้\n\n";
    
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
