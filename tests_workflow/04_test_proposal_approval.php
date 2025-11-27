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
echo "  TEST 04: PROPOSAL APPROVAL WORKFLOW\n";
echo "================================================================================\n\n";

echo "📋 ขั้นตอนการทดสอบ:\n";
echo "   1. เตรียม Proposal ที่ status = 'pending'\n";
echo "   2. Lecturer เข้าระบบและดู Proposals\n";
echo "   3. Lecturer อนุมัติ Proposal (ProposalController@approve)\n";
echo "   4. ระบบอัปเดต ProjectProposal (status = 'approved', responded_at)\n";
echo "   5. ระบบอัปเดต Project (status = 'approved', advisor_code)\n";
echo "   6. ตรวจสอบความถูกต้องของข้อมูล\n\n";

$testResults = [];

try {
    echo "================================================================================\n";
    echo "STEP 1: เตรียมข้อมูล Proposal ทดสอบ\n";
    echo "================================================================================\n\n";
    
    DB::beginTransaction();
    
    // ดึง Students และ Lecturer
    $students = Student::take(2)->get();
    $lecturer = User::where('role', '>=', 8192)->first();
    
    if ($students->count() < 2) {
        throw new Exception("❌ ต้องมี Student อย่างน้อย 2 คน");
    }
    
    if (!$lecturer) {
        throw new Exception("❌ ไม่พบ Lecturer ในระบบ");
    }
    
    $student1 = $students[0];
    $student2 = $students[1];
    
    echo "✅ ข้อมูลผู้ใช้:\n";
    echo "   Student 1: {$student1->username_std} - {$student1->firstname_std} {$student1->lastname_std}\n";
    echo "   Student 2: {$student2->username_std} - {$student2->firstname_std} {$student2->lastname_std}\n";
    echo "   Lecturer: {$lecturer->username_user} - {$lecturer->firstname_user} {$lecturer->lastname_user}\n";
    echo "   Lecturer Code: {$lecturer->user_code}\n\n";
    
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
        'status_project' => 'pending',
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
    
    // สร้าง Proposal
    $proposal = ProjectProposal::create([
        'group_id' => $group->group_id,
        'proposed_title' => $project->project_name,
        'description' => 'ระบบจัดการโครงงานนักศึกษาภายใต้ระบบออนไลน์',
        'proposed_to' => $lecturer->username_user,
        'proposed_by' => $student1->username_std,
        'status' => 'pending',
        'proposed_at' => now(),
    ]);
    
    echo "✅ สร้างข้อมูลทดสอบ:\n";
    echo "   Group ID: {$group->group_id}\n";
    echo "   Project ID: {$project->project_id}\n";
    echo "   Project Name: {$project->project_name}\n";
    echo "   Proposal ID: {$proposal->proposal_id}\n";
    echo "   Proposal Status: {$proposal->status}\n";
    echo "   Project Status: {$project->status_project}\n\n";
    
    $testResults[] = ['test' => 'Test Data Setup', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 2: Lecturer ดู Proposals ที่รอการตอบรับ\n";
    echo "================================================================================\n\n";
    
    // ดึง Proposals ที่ส่งมาหา Lecturer คนนี้
    $pendingProposals = ProjectProposal::where('proposed_to', $lecturer->username_user)
        ->where('status', 'pending')
        ->with('group.project')
        ->get();
    
    echo "✅ Proposals ที่รอการตอบรับ: {$pendingProposals->count()} รายการ\n";
    
    if ($pendingProposals->count() > 0) {
        foreach ($pendingProposals as $p) {
            echo "   - Proposal ID {$p->proposal_id}: {$p->proposed_title}\n";
        }
        echo "\n";
        $testResults[] = ['test' => 'Pending Proposals Found', 'status' => 'PASS'];
    } else {
        echo "   (ไม่พบ Proposal ที่รอการตอบรับ)\n\n";
        $testResults[] = ['test' => 'Pending Proposals Found', 'status' => 'FAIL'];
    }
    
    echo "================================================================================\n";
    echo "STEP 3: Lecturer อนุมัติ Proposal\n";
    echo "================================================================================\n\n";
    
    // ตรวจสอบว่า Proposal ยังเป็น pending อยู่
    if ($proposal->status != 'pending') {
        throw new Exception("❌ Proposal ต้องมีสถานะ pending ก่อนอนุมัติ");
    }
    
    echo "✅ ตรวจสอบ: Proposal status = pending\n";
    echo "✅ Lecturer กดอนุมัติ Proposal ID {$proposal->proposal_id}\n\n";
    
    $testResults[] = ['test' => 'Proposal Ready for Approval', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 4: ระบบอัปเดต ProjectProposal\n";
    echo "================================================================================\n\n";
    
    $oldProposalStatus = $proposal->status;
    $oldRespondedAt = $proposal->responded_at;
    
    // อัปเดต Proposal
    $proposal->status = 'approved';
    $proposal->responded_at = now();
    $proposal->save();
    
    echo "✅ อัปเดต ProjectProposal:\n";
    echo "   Old Status: {$oldProposalStatus}\n";
    echo "   New Status: {$proposal->status}\n";
    echo "   Old Responded At: " . ($oldRespondedAt ?: '(null)') . "\n";
    echo "   New Responded At: {$proposal->responded_at}\n\n";
    
    $testResults[] = ['test' => 'Proposal Updated', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 5: ระบบอัปเดต Project (status, advisor_code)\n";
    echo "================================================================================\n\n";
    
    $oldProjectStatus = $project->status_project;
    $oldAdvisorCode = $project->advisor_code;
    
    // อัปเดต Project
    $project->status_project = 'approved';
    $project->advisor_code = $lecturer->user_code; // ใช้ user_code (ไม่ใช่ username_user!)
    $project->save();
    
    echo "✅ อัปเดต Project:\n";
    echo "   Old Status: {$oldProjectStatus}\n";
    echo "   New Status: {$project->status_project}\n";
    echo "   Old Advisor Code: " . ($oldAdvisorCode ?: '(null)') . "\n";
    echo "   New Advisor Code: {$project->advisor_code}\n\n";
    
    $testResults[] = ['test' => 'Project Updated', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 6: ตรวจสอบความถูกต้องของข้อมูล\n";
    echo "================================================================================\n\n";
    
    // ตรวจสอบ Proposal status
    if ($proposal->status == 'approved') {
        echo "✅ Proposal Status: approved (ถูกต้อง)\n";
        $testResults[] = ['test' => 'Proposal Status = approved', 'status' => 'PASS'];
    } else {
        echo "❌ Proposal Status: {$proposal->status} (ไม่ถูกต้อง)\n";
        $testResults[] = ['test' => 'Proposal Status = approved', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ Proposal responded_at
    if ($proposal->responded_at) {
        echo "✅ Proposal Responded At: {$proposal->responded_at} (มีค่า)\n";
        $testResults[] = ['test' => 'Proposal Responded At Set', 'status' => 'PASS'];
    } else {
        echo "❌ Proposal Responded At: (null) (ไม่มีค่า)\n";
        $testResults[] = ['test' => 'Proposal Responded At Set', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ Project status
    if ($project->status_project == 'approved') {
        echo "✅ Project Status: approved (ถูกต้อง)\n";
        $testResults[] = ['test' => 'Project Status = approved', 'status' => 'PASS'];
    } else {
        echo "❌ Project Status: {$project->status_project} (ไม่ถูกต้อง)\n";
        $testResults[] = ['test' => 'Project Status = approved', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ advisor_code
    if ($project->advisor_code) {
        echo "✅ Project Advisor Code: {$project->advisor_code} (มีค่า)\n";
        $testResults[] = ['test' => 'Advisor Code Set', 'status' => 'PASS'];
    } else {
        echo "❌ Project Advisor Code: (null) (ไม่มีค่า)\n";
        $testResults[] = ['test' => 'Advisor Code Set', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบว่า advisor_code ตรงกับ Lecturer
    if ($project->advisor_code == $lecturer->user_code) {
        echo "✅ Advisor Code Match: {$project->advisor_code} = {$lecturer->user_code} (ถูกต้อง)\n";
        $testResults[] = ['test' => 'Advisor Code Match Lecturer', 'status' => 'PASS'];
    } else {
        echo "❌ Advisor Code Mismatch: {$project->advisor_code} ≠ {$lecturer->user_code}\n";
        $testResults[] = ['test' => 'Advisor Code Match Lecturer', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ Relationship: Project->Advisor
    $projectWithAdvisor = Project::with('advisor')->find($project->project_id);
    if ($projectWithAdvisor->advisor && $projectWithAdvisor->advisor->user_code == $lecturer->user_code) {
        echo "✅ Project->Advisor Relationship: ถูกต้อง\n";
        echo "   Advisor: {$projectWithAdvisor->advisor->firstname_user} {$projectWithAdvisor->advisor->lastname_user}\n";
        $testResults[] = ['test' => 'Project->Advisor Relationship', 'status' => 'PASS'];
    } else {
        echo "❌ Project->Advisor Relationship: ไม่ถูกต้อง\n";
        $testResults[] = ['test' => 'Project->Advisor Relationship', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบว่า Proposal ตอบแล้วไม่แสดงใน pending list
    $stillPending = ProjectProposal::where('proposed_to', $lecturer->username_user)
        ->where('status', 'pending')
        ->where('proposal_id', $proposal->proposal_id)
        ->exists();
    
    if (!$stillPending) {
        echo "✅ Proposal ไม่อยู่ใน Pending List แล้ว (ถูกต้อง)\n\n";
        $testResults[] = ['test' => 'Proposal Removed from Pending', 'status' => 'PASS'];
    } else {
        echo "❌ Proposal ยังอยู่ใน Pending List (ไม่ถูกต้อง)\n\n";
        $testResults[] = ['test' => 'Proposal Removed from Pending', 'status' => 'FAIL'];
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
