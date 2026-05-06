<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Group;
use App\Models\GroupInvitation;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function menu()
    {
        $student = Auth::guard('student')->user();
        
        if (!$student) {
            return redirect()->route('login')->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        // ดึงข้อมูลกลุ่มของ student พร้อม proposal ล่าสุด
        $myGroup = $student->groups()->with(['members.student', 'latestProposal.lecturer'])->first();
        
        // ดึงคำเชิญที่ค้างอยู่
        $pendingInvitations = $student->pendingInvitations()
            ->with(['group', 'inviter'])
            ->orderBy('created_at', 'desc')
            ->get();

        // เช็คว่าเป็นหัวหน้ากลุ่มหรือไม่
        $isGroupLeader = false;
        if ($myGroup) {
            $firstMember = $myGroup->members()->orderBy('groupmem_id', 'asc')->first();
            $isGroupLeader = $firstMember && $firstMember->username_std === $student->username_std;
            
            // ตรวจสอบสมาชิกที่เพิ่งตอบรับคำเชิญ (ภายใน 5 นาที) - สำหรับหัวหน้ากลุ่ม
            if ($isGroupLeader) {
                $recentMembers = $myGroup->members()
                    ->where('username_std', '!=', $student->username_std)
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->with('student')
                    ->get();
                
                if ($recentMembers->isNotEmpty()) {
                    $memberNames = $recentMembers->map(function($member) {
                        return $member->student 
                            ? "{$member->student->firstname_std} {$member->student->lastname_std}" 
                            : $member->username_std;
                    })->join(', ');
                    
                    session()->flash('member_accepted', "🎉 {$memberNames} ได้เข้าร่วมกลุ่มแล้ว!");
                }
            }
            
            // Check for exam schedule notification
            if ($myGroup->project && $myGroup->project->exam_datetime) {
                $examDate = \Carbon\Carbon::parse($myGroup->project->exam_datetime);
                $updatedAt = \Carbon\Carbon::parse($myGroup->project->updated_at);
                
                // Show notification if exam was scheduled within last 30 minutes
                if ($updatedAt->greaterThan(now()->subMinutes(30))) {
                    session()->flash('exam_scheduled', [
                        'project_name' => $myGroup->project->project_name_th,
                        'exam_datetime' => $examDate->locale('th')->translatedFormat('d F Y เวลา H:i น.')
                    ]);
                }
            }
            
            // ตรวจสอบสถานะ proposal สำหรับสมาชิกทุกคน
            if ($myGroup->latestProposal) {
                $proposal = $myGroup->latestProposal;
                
                if ($proposal->status === 'approved' && $proposal->responded_at) {
                    $lecturerName = $proposal->lecturer 
                        ? "{$proposal->lecturer->firstname_user} {$proposal->lecturer->lastname_user}" 
                        : 'อาจารย์';
                    
                    $message = "🎉 {$lecturerName} ได้ตอบรับเป็นอาจารย์ที่ปรึกษาโครงงาน \"{$proposal->proposed_title}\" เรียบร้อยแล้ว!";
                    session()->flash('proposal_approved', $message);
                } 
                elseif ($proposal->status === 'rejected' && $proposal->responded_at) {
                    $lecturerName = $proposal->lecturer 
                        ? "{$proposal->lecturer->firstname_user} {$proposal->lecturer->lastname_user}" 
                        : 'อาจารย์';
                    
                    $reason = $proposal->rejection_reason ? " เหตุผล: {$proposal->rejection_reason}" : '';
                    $message = "❌ {$lecturerName} ได้ปฏิเสธข้อเสนอโครงงาน \"{$proposal->proposed_title}\"{$reason}";
                    session()->flash('proposal_rejected', $message);
                }
            }
            
            // ตรวจสอบว่ามีการส่งเล่มรายงานล่าสุด (ภายใน 5 นาที) สำหรับสมาชิกที่ไม่ใช่หัวหน้า
            if (!$isGroupLeader && $myGroup->project && $myGroup->project->submission_file) {
                $submittedAt = \Carbon\Carbon::parse($myGroup->project->submitted_at);
                if ($submittedAt->diffInMinutes(now()) <= 5) {
                    $submitter = Student::where('username_std', $myGroup->project->submitted_by)->first();
                    $submitterName = $submitter 
                        ? "{$submitter->firstname_std} {$submitter->lastname_std}" 
                        : 'หัวหน้ากลุ่ม';
                    
                    session()->flash('report_submitted', "📄 {$submitterName} ได้ส่งเล่มรายงานโครงงานเรียบร้อยแล้ว!");
                }
            }
        }

        return view('student.menu', compact('student', 'myGroup', 'pendingInvitations', 'isGroupLeader'));
    }

    public function dashboard()
    {
        return $this->menu(); // redirect to menu as requested
    }

}
