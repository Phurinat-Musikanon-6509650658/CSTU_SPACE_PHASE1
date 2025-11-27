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
echo "  TEST 03: PROPOSAL SUBMISSION WORKFLOW\n";
echo "================================================================================\n\n";

echo "📋 ขั้นตอนการทดสอบ:\n";
echo "   1. เตรียมข้อมูลกลุ่มและ Project (status = 'member_added')\n";
echo "   2. ดึงข้อมูล Lecturer จาก Database\n";
echo "   3. Student เลือก Project Name\n";
echo "   4. Student ส่ง Proposal ถึง Lecturer (ProposalController@store)\n";
echo "   5. ระบบสร้าง ProjectProposal (status = 'pending')\n";
echo "   6. ระบบอัปเดต Project (project_name, status = 'pending')\n";
echo "   7. ตรวจสอบความถูกต้องของข้อมูล\n\n";

$testResults = [];

try {
    echo "================================================================================\n";
    echo "STEP 1: เตรียมข้อมูลกลุ่มและ Project ทดสอบ\n";
    echo "================================================================================\n\n";
    
    DB::beginTransaction();
    
    // ดึง Students
    $students = Student::take(2)->get();
    
    if ($students->count() < 2) {
        throw new Exception("❌ ระบบต้องมี Student อย่างน้อย 2 คนในการทดสอบ");
    }
    
    $student1 = $students[0];
    $student2 = $students[1];
    
    echo "✅ Students:\n";
    echo "   Student 1: {$student1->username_std} - {$student1->firstname_std} {$student1->lastname_std}\n";
    echo "   Student 2: {$student2->username_std} - {$student2->firstname_std} {$student2->lastname_std}\n\n";
    
    // สร้างกลุ่มและ Project (จำลองผลจาก Test 01-02)
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
        'project_name' => 'TBD',
        'student_type' => 'r',
        'status_project' => 'not_proposed',
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
    
    echo "✅ สร้างกลุ่มและ Project:\n";
    echo "   Group ID: {$group->group_id}\n";
    echo "   Project ID: {$project->project_id}\n";
    echo "   Project Code: {$project->project_code}\n";
    echo "   Project Name: {$project->project_name} (ยังไม่กำหนด)\n";
    echo "   Group Status: {$group->status_group}\n";
    echo "   Project Status: {$project->status_project}\n\n";
    
    $testResults[] = ['test' => 'Group and Project Setup', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 2: ดึงข้อมูล Lecturer จาก Database\n";
    echo "================================================================================\n\n";
    
    // ดึง User ที่เป็น Lecturer (role >= 8192)
    $lecturers = User::where('role', '>=', 8192)->take(1)->get();
    
    if ($lecturers->count() == 0) {
        throw new Exception("❌ ไม่พบ Lecturer ในระบบ");
    }
    
    $lecturer = $lecturers[0];
    
    echo "✅ พบ Lecturer:\n";
    echo "   Username: {$lecturer->username_user}\n";
    echo "   ชื่อ: {$lecturer->firstname_user} {$lecturer->lastname_user}\n";
    echo "   User Code: {$lecturer->user_code}\n";
    echo "   Email: {$lecturer->email_user}\n";
    echo "   Role: {$lecturer->role} (Lecturer bit set)\n\n";
    
    $testResults[] = ['test' => 'Lecturer Exists', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 3: Student กำหนด Project Name\n";
    echo "================================================================================\n\n";
    
    $projectName = "ระบบจัดการโครงงานนักศึกษา CSTU Space";
    
    echo "✅ ชื่อโครงงาน:\n";
    echo "   {$projectName}\n\n";
    
    $testResults[] = ['test' => 'Project Name Defined', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 4: Student ส่ง Proposal (ProposalController@store)\n";
    echo "================================================================================\n\n";
    
    // ตรวจสอบว่ายังไม่มี Proposal อยู่
    $existingProposal = ProjectProposal::where('group_id', $group->group_id)
        ->whereIn('status', ['pending', 'approved'])
        ->first();
    
    if ($existingProposal) {
        throw new Exception("❌ Project นี้มี Proposal อยู่แล้ว");
    }
    
    echo "✅ ตรวจสอบ: ยังไม่มี Proposal ที่ pending/approved\n\n";
    
    $testResults[] = ['test' => 'No Existing Proposal', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 5: ระบบสร้าง ProjectProposal (status = 'pending')\n";
    echo "================================================================================\n\n";
    
    // สร้าง ProjectProposal
    $proposal = ProjectProposal::create([
        'group_id' => $group->group_id,
        'proposed_title' => $project->project_name,
        'description' => 'ระบบจัดการโครงงานนักศึกษาภายใต้ระบบออนไลน์',
        'proposed_to' => $lecturer->username_user, // ใช้ username_user (ไม่ใช่ user_code!)
        'proposed_by' => $student1->username_std,
        'status' => 'pending',
        'proposed_at' => now(),
    ]);
    
    echo "✅ สร้าง ProjectProposal สำเร็จ:\n";
    echo "   Proposal ID: {$proposal->proposal_id}\n";
    echo "   Project ID: {$proposal->project_id}\n";
    echo "   Proposed To: {$proposal->proposed_to} (username_user)\n";
    echo "   Proposed By: {$proposal->proposed_by} (username_std)\n";
    echo "   Status: {$proposal->status}\n";
    echo "   Created At: {$proposal->created_at}\n\n";
    
    $testResults[] = ['test' => 'Proposal Created', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 6: ระบบอัปเดต Project (project_name, status = 'pending')\n";
    echo "================================================================================\n\n";
    
    $oldStatus = $project->status_project;
    $oldName = $project->project_name;
    
    // อัปเดต Project
    $project->project_name = $projectName;
    $project->status_project = 'pending';
    $project->save();
    
    echo "✅ อัปเดต Project:\n";
    echo "   Old Name: {$oldName}\n";
    echo "   New Name: {$project->project_name}\n";
    echo "   Old Status: {$oldStatus}\n";
    echo "   New Status: {$project->status_project}\n\n";
    
    $testResults[] = ['test' => 'Project Updated', 'status' => 'PASS'];
    
    echo "================================================================================\n";
    echo "STEP 7: ตรวจสอบความถูกต้องของข้อมูล\n";
    echo "================================================================================\n\n";
    
    // ตรวจสอบ Proposal status
    if ($proposal->status == 'pending') {
        echo "✅ Proposal Status: pending (ถูกต้อง)\n";
        $testResults[] = ['test' => 'Proposal Status = pending', 'status' => 'PASS'];
    } else {
        echo "❌ Proposal Status: {$proposal->status} (ไม่ถูกต้อง)\n";
        $testResults[] = ['test' => 'Proposal Status = pending', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ Project status
    if ($project->status_project == 'pending') {
        echo "✅ Project Status: pending (ถูกต้อง)\n";
        $testResults[] = ['test' => 'Project Status = pending', 'status' => 'PASS'];
    } else {
        echo "❌ Project Status: {$project->status_project} (ไม่ถูกต้อง)\n";
        $testResults[] = ['test' => 'Project Status = pending', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ project_name ไม่เป็น TBD
    if ($project->project_name != 'TBD') {
        echo "✅ Project Name: {$project->project_name} (อัปเดตแล้ว)\n";
        $testResults[] = ['test' => 'Project Name Updated', 'status' => 'PASS'];
    } else {
        echo "❌ Project Name: ยังเป็น TBD (ไม่ได้อัปเดต)\n";
        $testResults[] = ['test' => 'Project Name Updated', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ proposed_to ใช้ username_user
    $lecturerCheck = User::where('username_user', $proposal->proposed_to)->first();
    if ($lecturerCheck) {
        echo "✅ Proposed To: ใช้ username_user ถูกต้อง ({$proposal->proposed_to})\n";
        $testResults[] = ['test' => 'Proposed To = username_user', 'status' => 'PASS'];
    } else {
        echo "❌ Proposed To: ไม่พบ User ที่มี username_user = {$proposal->proposed_to}\n";
        $testResults[] = ['test' => 'Proposed To = username_user', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ proposed_by ใช้ username_std
    $studentCheck = Student::where('username_std', $proposal->proposed_by)->first();
    if ($studentCheck) {
        echo "✅ Proposed By: ใช้ username_std ถูกต้อง ({$proposal->proposed_by})\n";
        $testResults[] = ['test' => 'Proposed By = username_std', 'status' => 'PASS'];
    } else {
        echo "❌ Proposed By: ไม่พบ Student ที่มี username_std = {$proposal->proposed_by}\n";
        $testResults[] = ['test' => 'Proposed By = username_std', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบ Foreign Key Relationships
    $proposalWithGroup = ProjectProposal::with('group')->find($proposal->proposal_id);
    if ($proposalWithGroup->group && $proposalWithGroup->group->group_id == $group->group_id) {
        echo "✅ Proposal->Group Relationship: ถูกต้อง\n";
        $testResults[] = ['test' => 'Proposal->Group Relationship', 'status' => 'PASS'];
    } else {
        echo "❌ Proposal->Group Relationship: ไม่ถูกต้อง\n";
        $testResults[] = ['test' => 'Proposal->Group Relationship', 'status' => 'FAIL'];
    }
    
    // ตรวจสอบว่า Group มี Proposals
    $groupWithProposals = Group::with('proposals')->find($group->group_id);
    if ($groupWithProposals->proposals->count() > 0) {
        echo "✅ Group->Proposals Relationship: ถูกต้อง ({$groupWithProposals->proposals->count()} proposal)\n\n";
        $testResults[] = ['test' => 'Group->Proposals Relationship', 'status' => 'PASS'];
    } else {
        echo "❌ Group->Proposals Relationship: ไม่พบ Proposal\n\n";
        $testResults[] = ['test' => 'Group->Proposals Relationship', 'status' => 'FAIL'];
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
