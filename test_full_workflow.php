<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Project;
use App\Models\ProjectProposal;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "========================================\n";
echo "  FULL WORKFLOW TEST - CSTU SPACE\n";
echo "========================================\n\n";

try {
    DB::beginTransaction();
    
    // =============================================
    // STEP 1: Student สร้างกลุ่ม
    // =============================================
    echo "STEP 1: Student สร้างกลุ่ม\n";
    echo "----------------------------------------\n";
    
    $student1 = Student::where('username_std', '6509650757')->first();
    $student2 = Student::where('username_std', '6509611676')->first();
    
    if (!$student1 || !$student2) {
        throw new Exception("ไม่พบ Students ในระบบ");
    }
    
    echo "✓ Student 1: {$student1->username_std} - {$student1->firstname_std} {$student1->lastname_std}\n";
    echo "✓ Student 2: {$student2->username_std} - {$student2->firstname_std} {$student2->lastname_std}\n";
    
    // สร้างกลุ่ม
    $group = Group::create([
        'subject_code' => 'CS403',
        'year' => 2568,
        'semester' => 1,
        'status_group' => 'created',
    ]);
    
    echo "✓ สร้างกลุ่มสำเร็จ: Group ID = {$group->group_id}\n";
    
    // เพิ่มสมาชิก
    GroupMember::create([
        'group_id' => $group->group_id,
        'username_std' => $student1->username_std,
        'role' => 'leader',
        'joined_at' => now(),
    ]);
    
    GroupMember::create([
        'group_id' => $group->group_id,
        'username_std' => $student2->username_std,
        'role' => 'member',
        'joined_at' => now(),
    ]);
    
    echo "✓ เพิ่มสมาชิก 2 คนสำเร็จ\n\n";
    
    // =============================================
    // STEP 2: สร้าง Project (auto-create)
    // =============================================
    echo "STEP 2: ระบบสร้าง Project อัตโนมัติ\n";
    echo "----------------------------------------\n";
    
    $memberCount = $group->members()->count();
    $projectCode = sprintf(
        '%02d-%d-%02d_TBD-r%d',
        $group->year % 100,
        $group->semester,
        $group->group_id,
        $memberCount
    );
    
    $project = Project::create([
        'group_id' => $group->group_id,
        'project_code' => $projectCode,
        'student_type' => 'r',
        'status_project' => 'not_proposed',
    ]);
    
    echo "✓ สร้าง Project สำเร็จ\n";
    echo "  - Project ID: {$project->project_id}\n";
    echo "  - Project Code: {$project->project_code}\n";
    echo "  - Status: {$project->status_project}\n\n";
    
    // =============================================
    // STEP 3: Student เสนอหัวข้อโครงงาน
    // =============================================
    echo "STEP 3: Student เสนอหัวข้อโครงงาน\n";
    echo "----------------------------------------\n";
    
    $lecturer = User::where('user_code', 'ddp')->first(); // เด่นดวง
    
    if (!$lecturer) {
        throw new Exception("ไม่พบ Lecturer ในระบบ");
    }
    
    echo "✓ เสนอหัวข้อไปยัง: {$lecturer->user_code} - {$lecturer->firstname_user} {$lecturer->lastname_user}\n";
    
    $proposal = ProjectProposal::create([
        'group_id' => $group->group_id,
        'proposed_title' => 'ระบบจัดการโครงงานนักศึกษา',
        'description' => 'พัฒนาระบบจัดการโครงงานสำหรับนักศึกษาคณะวิทยาการคอมพิวเตอร์',
        'proposed_to' => $lecturer->username_user,
        'proposed_by' => $student1->username_std,
        'status' => 'pending',
        'proposed_at' => now(),
    ]);
    
    echo "✓ สร้าง Proposal สำเร็จ\n";
    echo "  - Proposal ID: {$proposal->proposal_id}\n";
    echo "  - Title: {$proposal->proposed_title}\n";
    echo "  - Status: {$proposal->status}\n\n";
    
    // =============================================
    // STEP 4: Lecturer อนุมัติหัวข้อ (advisor_code auto-fill)
    // =============================================
    echo "STEP 4: Lecturer อนุมัติหัวข้อ\n";
    echo "----------------------------------------\n";
    
    $proposal->update([
        'status' => 'approved',
        'responded_at' => now()
    ]);
    
    $project->update([
        'status_project' => 'approved',
        'project_name' => $proposal->proposed_title,
        'advisor_code' => $lecturer->user_code, // ✨ AUTO-FILL
    ]);
    
    // Update project_code
    $newProjectCode = sprintf(
        '%02d-%d-%02d_%s-r%d',
        $group->year % 100,
        $group->semester,
        $group->group_id,
        $lecturer->user_code,
        $memberCount
    );
    
    $project->update(['project_code' => $newProjectCode]);
    
    echo "✓ Lecturer อนุมัติสำเร็จ!\n";
    echo "  - Advisor Code: {$project->advisor_code} ✨ (Auto-filled)\n";
    echo "  - Project Code (Updated): {$project->project_code}\n";
    echo "  - Status: {$project->status_project}\n\n";
    
    // =============================================
    // STEP 5: Coordinator ดู Dashboard
    // =============================================
    echo "STEP 5: Coordinator ดู Dashboard\n";
    echo "----------------------------------------\n";
    
    $stats = [
        'total_groups' => Group::count(),
        'pending_groups' => Group::where('status_group', 'pending')->count(),
        'approved_groups' => Group::where('status_group', 'created')->count(),
        'total_projects' => Project::count(),
    ];
    
    echo "✓ Dashboard Statistics:\n";
    echo "  - กลุ่มทั้งหมด: {$stats['total_groups']}\n";
    echo "  - รออนุมัติ: {$stats['pending_groups']}\n";
    echo "  - โครงงานทั้งหมด: {$stats['total_projects']}\n\n";
    
    // =============================================
    // STEP 6: Coordinator เลือก Committee และกำหนดวันสอบ
    // =============================================
    echo "STEP 6: Coordinator จัดการโครงงาน\n";
    echo "----------------------------------------\n";
    
    $committee1 = User::where('user_code', 'scw')->first(); // เสาวลักษณ์
    $committee2 = User::where('user_code', 'ADV')->first(); // อาจารย์ที่ปรึกษา
    
    if (!$committee1 || !$committee2) {
        echo "⚠ Warning: ไม่พบ Committee บางคน ใช้ที่มีอยู่\n";
        $committee1 = $committee1 ?: User::whereRaw('role & 8192 != 0')->where('user_code', '!=', $lecturer->user_code)->first();
        $committee2 = $committee2 ?: User::whereRaw('role & 8192 != 0')->whereNotIn('user_code', [$lecturer->user_code, $committee1->user_code ?? ''])->first();
    }
    
    echo "✓ เลือก Committee:\n";
    if ($committee1) echo "  - Committee 1: {$committee1->user_code} - {$committee1->firstname_user}\n";
    if ($committee2) echo "  - Committee 2: {$committee2->user_code} - {$committee2->firstname_user}\n";
    
    // กำหนด Committee และวันสอบ
    $examDatetime = now()->addDays(30)->setTime(14, 0);
    
    $project->update([
        'committee1_code' => $committee1->user_code ?? null,
        'committee2_code' => $committee2->user_code ?? null,
        'exam_datetime' => $examDatetime,
        'status_project' => 'in_progress',
    ]);
    
    echo "✓ บันทึกข้อมูลสำเร็จ!\n";
    echo "  - Exam Date: {$examDatetime->format('d/m/Y H:i')}\n";
    echo "  - Status: {$project->status_project}\n\n";
    
    // =============================================
    // FINAL: แสดงผลลัพธ์สุดท้าย
    // =============================================
    echo "========================================\n";
    echo "  📊 FINAL RESULT\n";
    echo "========================================\n\n";
    
    $finalProject = Project::with(['advisor', 'committee1', 'committee2', 'committee3'])->find($project->project_id);
    
    echo "📋 Project Information:\n";
    echo "  - Project ID: {$finalProject->project_id}\n";
    echo "  - Project Code: {$finalProject->project_code}\n";
    echo "  - Project Name: {$finalProject->project_name}\n";
    echo "  - Status: {$finalProject->status_project}\n\n";
    
    echo "👥 Project Team:\n";
    echo "  - AdvId: " . ($finalProject->advisor_code ?: '-') . "\n";
    echo "  - Comm1: " . ($finalProject->committee1_code ?: '-') . "\n";
    echo "  - Comm2: " . ($finalProject->committee2_code ?: '-') . "\n";
    echo "  - Comm3: " . ($finalProject->committee3_code ?: '-') . "\n\n";
    
    echo "📅 Exam Schedule:\n";
    echo "  - Date/Time: " . ($finalProject->exam_datetime ? $finalProject->exam_datetime->format('d/m/Y H:i') : '-') . "\n\n";
    
    echo "🎓 Students:\n";
    foreach ($group->members as $member) {
        $student = $member->student;
        echo "  - {$student->username_std} - {$student->firstname_std} {$student->lastname_std} ({$member->role})\n";
    }
    
    DB::commit();
    
    echo "\n========================================\n";
    echo "  ✅ TEST COMPLETED SUCCESSFULLY!\n";
    echo "========================================\n\n";
    
    echo "🔗 URLs to check:\n";
    echo "  - Coordinator Dashboard: http://127.0.0.1:8000/coordinator/dashboard\n";
    echo "  - Groups List: http://127.0.0.1:8000/coordinator/groups\n";
    echo "  - Group Detail: http://127.0.0.1:8000/coordinator/groups/{$group->group_id}\n\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n\n";
}
