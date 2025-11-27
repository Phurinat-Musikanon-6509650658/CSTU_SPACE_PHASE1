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
echo "  TEST 07: GRADE CALCULATION WORKFLOW\n";
echo "================================================================================\n\n";

echo "📋 ขั้นตอนการทดสอบ:\n";
echo "   1. เตรียม Project ที่มีการประเมินครบ 4 คน\n";
echo "   2. ระบบตรวจสอบว่ามีการประเมินครบหรือไม่\n";
echo "   3. ระบบคำนวณคะแนนเฉลี่ย (final_score)\n";
echo "   4. ระบบแปลงคะแนนเป็นเกรด (A, B+, B, C+, C, D+, D, F)\n";
echo "   5. ระบบสร้าง ProjectGrade\n";
echo "   6. ตรวจสอบความถูกต้องของเกรด\n\n";

$testResults = [];

try {
    echo "================================================================================\n";
    echo "STEP 1: เตรียมข้อมูล Project พร้อมการประเมินครบ 4 คน\n";
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
    $eval1 = ProjectEvaluation::create([
        'project_id' => $project->project_id,
        'evaluator_code' => $advisor->user_code,
        'evaluator_role' => 'advisor',
        'document_score' => 28.0,
        'presentation_score' => 67.0,
        'comments' => "งานดีมาก",
        'submitted_at' => now(),
    ]);
    
    $eval2 = ProjectEvaluation::create([
        'project_id' => $project->project_id,
        'evaluator_code' => $committee1->user_code,
        'evaluator_role' => 'committee1',
        'document_score' => 26.5,
        'presentation_score' => 64.0,
        'comments' => "ดีมาก",
        'submitted_at' => now(),
    ]);
    
    $eval3 = ProjectEvaluation::create([
        'project_id' => $project->project_id,
        'evaluator_code' => $committee2->user_code,
        'evaluator_role' => 'committee2',
        'document_score' => 27.0,
        'presentation_score' => 66.0,
        'comments' => "เยี่ยม",
        'submitted_at' => now(),
    ]);
    
    $eval4 = ProjectEvaluation::create([
        'project_id' => $project->project_id,
        'evaluator_code' => $committee3->user_code,
        'evaluator_role' => 'committee3',
        'document_score' => 25.5,
        'presentation_score' => 62.5,
        'comments' => "ดีครับ",
        'submitted_at' => now(),
    ]);
    
    echo "✅ สร้างข้อมูลทดสอบ:\n";
    echo "   Project ID: {$project->project_id}\n";
    echo "   Evaluation 1 (Advisor): {$eval1->total_score}/100\n";
    echo "   Evaluation 2 (Committee1): {$eval2->total_score}/100\n";
    echo "   Evaluation 3 (Committee2): {$eval3->total_score}/100\n";
    echo "   Evaluation 4 (Committee3): {$eval4->total_score}/100\n\n";
    
    $testResults[] = ['test' => 'Test Data Setup', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 2: ระบบตรวจสอบว่ามีการประเมินครบหรือไม่\n";
    echo "================================================================================\n\n";
    
    // นับจำนวนการประเมิน
    $evaluationCount = ProjectEvaluation::where('project_id', $project->project_id)->count();
    
    echo "✅ จำนวนการประเมิน: {$evaluationCount} คน\n";
    
    if ($evaluationCount == 4) {
        echo "✅ การประเมินครบถ้วน (4/4)\n\n";
        $testResults[] = ['test' => 'All Evaluations Complete', 'status' => 'PASS'];
    } else {
        echo "❌ การประเมินไม่ครบ ({$evaluationCount}/4)\n\n";
        $testResults[] = ['test' => 'All Evaluations Complete', 'status' => 'FAIL'];
        throw new Exception("❌ ต้องมีการประเมินครบ 4 คนก่อนคำนวณเกรด");
    }
    
    echo "================================================================================\n";
    echo "STEP 3: ระบบคำนวณคะแนนเฉลี่ย (final_score)\n";
    echo "================================================================================\n\n";
    
    // ดึงคะแนนทั้งหมด
    $evaluations = ProjectEvaluation::where('project_id', $project->project_id)->get();
    
    echo "📊 คะแนนจากผู้ประเมินแต่ละคน:\n";
    foreach ($evaluations as $eval) {
        $evaluator = User::where('user_code', $eval->evaluator_code)->first();
        echo "   {$eval->evaluator_role}: {$evaluator->firstname_user} = {$eval->total_score}/100\n";
    }
    echo "\n";
    
    // คำนวณคะแนนเฉลี่ย
    $finalScore = $evaluations->avg('total_score');
    
    echo "✅ คะแนนเฉลี่ย (Final Score):\n";
    echo "   ({$eval1->total_score} + {$eval2->total_score} + {$eval3->total_score} + {$eval4->total_score}) / 4\n";
    echo "   = " . number_format($finalScore, 2) . "/100\n\n";
    
    $testResults[] = ['test' => 'Final Score Calculated', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 4: ระบบแปลงคะแนนเป็นเกรด\n";
    echo "================================================================================\n\n";
    
    // ใช้ static method จาก ProjectGrade Model
    $grade = ProjectGrade::calculateGrade($finalScore);
    
    echo "✅ เกรดจากคะแนน " . number_format($finalScore, 2) . ":\n";
    echo "   Grade: {$grade}\n\n";
    
    // ตรวจสอบเกรดตาม Scale
    echo "📋 Grade Scale:\n";
    echo "   A  : 80-100\n";
    echo "   B+ : 75-79\n";
    echo "   B  : 70-74\n";
    echo "   C+ : 65-69\n";
    echo "   C  : 60-64\n";
    echo "   D+ : 55-59\n";
    echo "   D  : 50-54\n";
    echo "   F  : 0-49\n\n";
    
    // ตรวจสอบว่าเกรดถูกต้องตามช่วงคะแนน
    $expectedGrade = '';
    if ($finalScore >= 80) $expectedGrade = 'A';
    elseif ($finalScore >= 75) $expectedGrade = 'B+';
    elseif ($finalScore >= 70) $expectedGrade = 'B';
    elseif ($finalScore >= 65) $expectedGrade = 'C+';
    elseif ($finalScore >= 60) $expectedGrade = 'C';
    elseif ($finalScore >= 55) $expectedGrade = 'D+';
    elseif ($finalScore >= 50) $expectedGrade = 'D';
    else $expectedGrade = 'F';
    
    if ($grade == $expectedGrade) {
        echo "✅ เกรดถูกต้อง: {$grade} (คะแนน " . number_format($finalScore, 2) . ")\n\n";
        $testResults[] = ['test' => 'Grade Correct', 'status' => 'PASS'];
    } else {
        echo "❌ เกรดไม่ถูกต้อง: Expected {$expectedGrade}, Got {$grade}\n\n";
        $testResults[] = ['test' => 'Grade Correct', 'status' => 'FAIL'];
    }
    
    echo "================================================================================\n";
    echo "STEP 5: ระบบสร้าง ProjectGrade\n";
    echo "================================================================================\n\n";
    
    // ตรวจสอบว่ายังไม่มี ProjectGrade
    $existingGrade = ProjectGrade::where('project_id', $project->project_id)->first();
    
    if ($existingGrade) {
        throw new Exception("❌ Project นี้มีเกรดอยู่แล้ว");
    }
    
    echo "✅ ตรวจสอบ: ยังไม่มีเกรด\n\n";
    
    // สร้าง ProjectGrade
    $projectGrade = ProjectGrade::create([
        'project_id' => $project->project_id,
        'final_score' => $finalScore,
        'grade' => $grade,
    ]);
    
    echo "✅ สร้าง ProjectGrade สำเร็จ:\n";
    echo "   Grade ID: {$projectGrade->grade_id}\n";
    echo "   Project ID: {$projectGrade->project_id}\n";
    echo "   Final Score: {$projectGrade->final_score}\n";
    echo "   Grade: {$projectGrade->grade}\n";
    echo "   Created At: {$projectGrade->created_at}\n\n";
    
    $testResults[] = ['test' => 'ProjectGrade Created', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 6: ตรวจสอบความถูกต้องของข้อมูล\n";
    echo "================================================================================\n\n";
    
    // ตรวจสอบ final_score (เปรียบเทียบทศนิยม 2 ตำแหน่ง เพราะ database เป็น decimal(5,2))
    if (abs($projectGrade->final_score - $finalScore) < 0.01) {
        echo "✅ Final Score: " . number_format($projectGrade->final_score, 2) . " (ถูกต้อง)\n";
        $testResults[] = ['test' => 'Final Score Match', 'status' => 'PASS'];
    } else {
        echo "❌ Final Score: ไม่ตรงกัน (Expected: " . number_format($finalScore, 2) . ", Got: " . number_format($projectGrade->final_score, 2) . ")\n";
        $testResults[] = ['test' => 'Final Score Match', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ grade
    if ($projectGrade->grade == $grade) {
        echo "✅ Grade: {$projectGrade->grade} (ถูกต้อง)\n";
        $testResults[] = ['test' => 'Grade Match', 'status' => 'PASS'];
    } else {
        echo "❌ Grade: ไม่ถูกต้อง\n";
        $testResults[] = ['test' => 'Grade Match', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบสถานะการยืนยัน (ควรยังไม่มี)
    if (!$projectGrade->advisor_confirmed && !$projectGrade->committee1_confirmed && 
        !$projectGrade->committee2_confirmed && !$projectGrade->committee3_confirmed) {
        echo "✅ Confirmation Status: ยังไม่มีการยืนยัน (ถูกต้อง)\n";
        $testResults[] = ['test' => 'No Confirmations Yet', 'status' => 'PASS'];
    } else {
        echo "❌ Confirmation Status: มีการยืนยันแล้ว (ไม่ถูกต้อง)\n";
        $testResults[] = ['test' => 'No Confirmations Yet', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ all_confirmed
    if (!$projectGrade->all_confirmed) {
        echo "✅ All Confirmed: ยังไม่ครบ (ถูกต้อง)\n";
        $testResults[] = ['test' => 'All Confirmed = false', 'status' => 'PASS'];
    } else {
        echo "❌ All Confirmed: ครบแล้ว (ไม่ถูกต้อง)\n";
        $testResults[] = ['test' => 'All Confirmed = false', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ grade_released
    if (!$projectGrade->grade_released) {
        echo "✅ Grade Released: ยังไม่ปล่อย (ถูกต้อง)\n";
        $testResults[] = ['test' => 'Grade Released = false', 'status' => 'PASS'];
    } else {
        echo "❌ Grade Released: ปล่อยแล้ว (ไม่ถูกต้อง)\n";
        $testResults[] = ['test' => 'Grade Released = false', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ Relationship: ProjectGrade->Project
    $gradeWithProject = ProjectGrade::with('project')->find($projectGrade->grade_id);
    if ($gradeWithProject->project && $gradeWithProject->project->project_id == $project->project_id) {
        echo "✅ ProjectGrade->Project Relationship: ถูกต้อง\n\n";
        $testResults[] = ['test' => 'ProjectGrade->Project Relationship', 'status' => 'PASS'];
    } else {
        echo "❌ ProjectGrade->Project Relationship: ไม่ถูกต้อง\n\n";
        $testResults[] = ['test' => 'ProjectGrade->Project Relationship', 'status' => 'FAIL'];
    }
    
    echo "================================================================================\n";
    echo "📊 สรุปการคำนวณเกรด\n";
    echo "================================================================================\n\n";
    
    echo "Project: {$project->project_name}\n";
    echo "Evaluations:\n";
    foreach ($evaluations as $eval) {
        $evaluator = User::where('user_code', $eval->evaluator_code)->first();
        echo "   - {$eval->evaluator_role}: {$evaluator->firstname_user} = {$eval->total_score}/100\n";
    }
    echo "\nFinal Score: " . number_format($projectGrade->final_score, 2) . "/100\n";
    echo "Grade: {$projectGrade->grade}\n";
    echo "Status: รอการยืนยันจากผู้ประเมินทั้ง 4 คน\n\n";
    
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
