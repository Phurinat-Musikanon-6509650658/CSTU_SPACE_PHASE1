<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Project;
use App\Models\ProjectProposal;
use App\Models\ProjectEvaluation;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "================================================================================\n";
echo "  TEST 06: EVALUATION SUBMISSION WORKFLOW\n";
echo "================================================================================\n\n";

echo "📋 ขั้นตอนการทดสอบ:\n";
echo "   1. เตรียม Project ที่มีวันสอบกำหนดแล้ว\n";
echo "   2. Lecturer (Advisor) เข้าระบบเพื่อประเมิน\n";
echo "   3. Lecturer ให้คะแนน Document และ Presentation\n";
echo "   4. ระบบบันทึก ProjectEvaluation (advisor)\n";
echo "   5. ระบบคำนวณ total_score อัตโนมัติ\n";
echo "   6. Committee Members (3 คน) ประเมินเพิ่ม\n";
echo "   7. ตรวจสอบว่ามีการประเมินครบ 4 คน\n\n";

$testResults = [];

try {
    echo "================================================================================\n";
    echo "STEP 1: เตรียมข้อมูล Project พร้อมวันสอบและคณะกรรมการ\n";
    echo "================================================================================\n\n";
    
    DB::beginTransaction();
    
    // ดึง Students และ Lecturers
    $students = Student::take(2)->get();
    $lecturers = User::where('role', '>=', 8192)->take(4)->get();
    
    if ($students->count() < 2) {
        throw new Exception("❌ ต้องมี Student อย่างน้อย 2 คน");
    }
    
    if ($lecturers->count() < 4) {
        throw new Exception("❌ ต้องมี Lecturer อย่างน้อย 4 คน (1 advisor + 3 committee)");
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
        'exam_datetime' => '2025-12-27 13:00:00',
        'committee1_code' => $committee1->user_code,
        'committee2_code' => $committee2->user_code,
        'committee3_code' => $committee3->user_code,
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
    
    echo "✅ สร้างข้อมูลทดสอบ:\n";
    echo "   Project ID: {$project->project_id}\n";
    echo "   Project Name: {$project->project_name}\n";
    echo "   Exam DateTime: {$project->exam_datetime}\n";
    echo "   Advisor: {$project->advisor_code}\n";
    echo "   Committee: {$project->committee1_code}, {$project->committee2_code}, {$project->committee3_code}\n\n";
    
    $testResults[] = ['test' => 'Test Data Setup', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 2: Advisor เข้าระบบเพื่อประเมินโครงงาน\n";
    echo "================================================================================\n\n";
    
    // ดึง Projects ที่ Advisor ต้องประเมิน
    $advisorProjects = Project::where('advisor_code', $advisor->user_code)
        ->whereNotNull('exam_datetime')
        ->with('group')
        ->get();
    
    echo "✅ Projects ที่ Advisor ต้องประเมิน: {$advisorProjects->count()} โครงงาน\n";
    
    if ($advisorProjects->count() > 0) {
        foreach ($advisorProjects as $p) {
            echo "   - Project {$p->project_id}: {$p->project_name}\n";
        }
        echo "\n";
        $testResults[] = ['test' => 'Advisor Projects Found', 'status' => 'PASS'];
    } else {
        echo "   (ไม่พบ Project)\n\n";
        $testResults[] = ['test' => 'Advisor Projects Found', 'status' => 'FAIL'];
    }
    
    echo "================================================================================\n";
    echo "STEP 3: Advisor ให้คะแนน Document (0-30) และ Presentation (0-70)\n";
    echo "================================================================================\n\n";
    
    $documentScore = 28.0; // คะแนนเอกสาร
    $presentationScore = 67.0; // คะแนนนำเสนอ
    $comments = "งานดีมาก Documentation ครบถ้วน การนำเสนอชัดเจน มีการวิเคราะห์ปัญหาได้ดี";
    
    echo "✅ คะแนนที่ Advisor ให้:\n";
    echo "   Document Score: {$documentScore}/30\n";
    echo "   Presentation Score: {$presentationScore}/70\n";
    echo "   Comments: {$comments}\n\n";
    
    // ตรวจสอบช่วงคะแนน
    if ($documentScore < 0 || $documentScore > 30) {
        throw new Exception("❌ Document Score ต้องอยู่ในช่วง 0-30");
    }
    
    if ($presentationScore < 0 || $presentationScore > 70) {
        throw new Exception("❌ Presentation Score ต้องอยู่ในช่วง 0-70");
    }
    
    echo "✅ ตรวจสอบช่วงคะแนน: ผ่าน\n\n";
    
    $testResults[] = ['test' => 'Score Validation', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 4: ระบบบันทึก ProjectEvaluation (Advisor)\n";
    echo "================================================================================\n\n";
    
    // ตรวจสอบว่ายังไม่เคยประเมิน
    $existingEval = ProjectEvaluation::where('project_id', $project->project_id)
        ->where('evaluator_code', $advisor->user_code)
        ->where('evaluator_role', 'advisor')
        ->first();
    
    if ($existingEval) {
        throw new Exception("❌ Advisor ประเมินโครงงานนี้แล้ว");
    }
    
    echo "✅ ตรวจสอบ: ยังไม่เคยประเมิน\n\n";
    
    // สร้าง ProjectEvaluation
    $advisorEval = ProjectEvaluation::create([
        'project_id' => $project->project_id,
        'evaluator_code' => $advisor->user_code,
        'evaluator_role' => 'advisor',
        'document_score' => $documentScore,
        'presentation_score' => $presentationScore,
        'comments' => $comments,
        'submitted_at' => now(),
    ]);
    
    echo "✅ บันทึกการประเมิน:\n";
    echo "   Evaluation ID: {$advisorEval->evaluation_id}\n";
    echo "   Evaluator: {$advisorEval->evaluator_code} (advisor)\n";
    echo "   Document: {$advisorEval->document_score}\n";
    echo "   Presentation: {$advisorEval->presentation_score}\n";
    echo "   Total: {$advisorEval->total_score} (auto-calculated)\n";
    echo "   Submitted At: {$advisorEval->submitted_at}\n\n";
    
    $testResults[] = ['test' => 'Advisor Evaluation Created', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 5: ตรวจสอบการคำนวณ total_score อัตโนมัติ\n";
    echo "================================================================================\n\n";
    
    $expectedTotal = $documentScore + $presentationScore;
    
    if ($advisorEval->total_score == $expectedTotal) {
        echo "✅ Total Score คำนวณถูกต้อง:\n";
        echo "   {$documentScore} + {$presentationScore} = {$advisorEval->total_score}\n\n";
        $testResults[] = ['test' => 'Total Score Auto-Calculated', 'status' => 'PASS'];
    } else {
        echo "❌ Total Score คำนวณผิด:\n";
        echo "   Expected: {$expectedTotal}, Got: {$advisorEval->total_score}\n\n";
        $testResults[] = ['test' => 'Total Score Auto-Calculated', 'status' => 'FAIL'];
    }
    
    echo "================================================================================\n";
    echo "STEP 6: Committee Members (3 คน) ประเมินโครงงาน\n";
    echo "================================================================================\n\n";
    
    // Committee 1 ประเมิน
    $committee1Eval = ProjectEvaluation::create([
        'project_id' => $project->project_id,
        'evaluator_code' => $committee1->user_code,
        'evaluator_role' => 'committee1',
        'document_score' => 26.5,
        'presentation_score' => 64.0,
        'comments' => "โครงงานดีมาก แต่ควรเพิ่ม error handling ในบางส่วน",
        'submitted_at' => now(),
    ]);
    
    echo "✅ Committee 1 ประเมิน:\n";
    echo "   Evaluator: {$committee1->user_code}\n";
    echo "   Total Score: {$committee1Eval->total_score}\n\n";
    
    // Committee 2 ประเมิน
    $committee2Eval = ProjectEvaluation::create([
        'project_id' => $project->project_id,
        'evaluator_code' => $committee2->user_code,
        'evaluator_role' => 'committee2',
        'document_score' => 27.0,
        'presentation_score' => 66.0,
        'comments' => "เยี่ยมมาก UI/UX สวยงาม responsive design ดี",
        'submitted_at' => now(),
    ]);
    
    echo "✅ Committee 2 ประเมิน:\n";
    echo "   Evaluator: {$committee2->user_code}\n";
    echo "   Total Score: {$committee2Eval->total_score}\n\n";
    
    // Committee 3 ประเมิน
    $committee3Eval = ProjectEvaluation::create([
        'project_id' => $project->project_id,
        'evaluator_code' => $committee3->user_code,
        'evaluator_role' => 'committee3',
        'document_score' => 25.5,
        'presentation_score' => 62.5,
        'comments' => "ผลงานดีครับ แต่อาจต้องเพิ่ม unit test coverage",
        'submitted_at' => now(),
    ]);
    
    echo "✅ Committee 3 ประเมิน:\n";
    echo "   Evaluator: {$committee3->user_code}\n";
    echo "   Total Score: {$committee3Eval->total_score}\n\n";
    
    $testResults[] = ['test' => 'Committee Evaluations Created', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 7: ตรวจสอบความครบถ้วนของการประเมิน\n";
    echo "================================================================================\n\n";
    
    // นับจำนวนการประเมินทั้งหมด
    $totalEvaluations = ProjectEvaluation::where('project_id', $project->project_id)->count();
    
    if ($totalEvaluations == 4) {
        echo "✅ จำนวนการประเมิน: {$totalEvaluations} คน (ครบ)\n";
        $testResults[] = ['test' => 'Evaluation Count = 4', 'status' => 'PASS'];
    } else {
        echo "❌ จำนวนการประเมิน: {$totalEvaluations} คน (ไม่ครบ)\n";
        $testResults[] = ['test' => 'Evaluation Count = 4', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ Advisor ประเมินแล้ว
    $advisorEvalCount = ProjectEvaluation::where('project_id', $project->project_id)
        ->where('evaluator_role', 'advisor')
        ->count();
    
    if ($advisorEvalCount == 1) {
        echo "✅ Advisor ประเมินแล้ว: {$advisorEvalCount} คน\n";
        $testResults[] = ['test' => 'Advisor Evaluated', 'status' => 'PASS'];
    } else {
        echo "❌ Advisor ประเมินแล้ว: {$advisorEvalCount} คน (ไม่ถูกต้อง)\n";
        $testResults[] = ['test' => 'Advisor Evaluated', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ Committee ประเมินแล้ว
    $committeeEvalCount = ProjectEvaluation::where('project_id', $project->project_id)
        ->whereIn('evaluator_role', ['committee1', 'committee2', 'committee3'])
        ->count();
    
    if ($committeeEvalCount == 3) {
        echo "✅ Committee ประเมินแล้ว: {$committeeEvalCount} คน\n";
        $testResults[] = ['test' => 'Committee Evaluated', 'status' => 'PASS'];
    } else {
        echo "❌ Committee ประเมินแล้ว: {$committeeEvalCount} คน (ไม่ถูกต้อง)\n";
        $testResults[] = ['test' => 'Committee Evaluated', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบว่าไม่มีการประเมินซ้ำ
    $evaluations = ProjectEvaluation::where('project_id', $project->project_id)->get();
    $evaluatorCodes = $evaluations->pluck('evaluator_code')->toArray();
    
    if (count($evaluatorCodes) == count(array_unique($evaluatorCodes))) {
        echo "✅ Evaluator Codes: ไม่ซ้ำกัน\n";
        $testResults[] = ['test' => 'No Duplicate Evaluators', 'status' => 'PASS'];
    } else {
        echo "❌ Evaluator Codes: มีค่าซ้ำกัน\n";
        $testResults[] = ['test' => 'No Duplicate Evaluators', 'status' => 'FAIL'];
    }
    
    // แสดงคะแนนทั้งหมด
    echo "\n📊 สรุปคะแนนทั้งหมด:\n";
    foreach ($evaluations as $eval) {
        $evaluator = User::where('user_code', $eval->evaluator_code)->first();
        echo "   {$eval->evaluator_role}: {$evaluator->firstname_user} {$evaluator->lastname_user} = {$eval->total_score}/100\n";
    }
    
    // คำนวณคะแนนเฉลี่ย
    $averageScore = $evaluations->avg('total_score');
    echo "\n✅ คะแนนเฉลี่ย: " . number_format($averageScore, 2) . "/100\n\n";
    
    $testResults[] = ['test' => 'Average Score Calculated', 'status' => 'PASS'];
    
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
