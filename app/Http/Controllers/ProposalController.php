<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectProposal;
use App\Models\Group;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProposalController extends Controller
{
    // แสดงฟอร์มเสนอหัวข้อ (สำหรับ group leader)
    public function create($groupId)
    {
        $group = Group::with(['members.student', 'latestProposal'])->findOrFail($groupId);
        
        // ตรวจสอบว่าเป็นหัวหน้ากลุ่ม (สมาชิกคนแรก)
        $firstMember = $group->members()->orderBy('groupmem_id', 'asc')->first();
        if (!$firstMember || $firstMember->username_std !== Auth::guard('student')->user()->username_std) {
            return redirect()->route('student.menu')
                ->with('error', 'เฉพาะหัวหน้ากลุ่มเท่านั้นที่สามารถเสนอหัวข้อได้');
        }
        
        // ตรวจสอบว่าสามารถเสนอหัวข้อได้หรือไม่
        if (!$group->canProposeProject()) {
            $message = 'ไม่สามารถเสนอหัวข้อได้ในขณะนี้';
            
            if ($group->hasPendingInvitation()) {
                $message = 'ต้องรอให้สมาชิกที่ถูกเชิญตอบรับหรือปฏิเสธก่อนถึงจะสามารถเสนอหัวข้อได้';
            }
            
            return redirect()->route('student.menu')->with('error', $message);
        }
        
        // ดึงรายชื่อ lecturers
        $lecturers = User::where('role', 8192)->get();
        
        return view('student.proposals.create', compact('group', 'lecturers'));
    }
    
    // บันทึกข้อเสนอ
    public function store(Request $request, $groupId)
    {
        $group = Group::with(['members', 'project'])->findOrFail($groupId);
        
        // ตรวจสอบสิทธิ์
        $firstMember = $group->members()->orderBy('groupmem_id', 'asc')->first();
        if (!$firstMember || $firstMember->username_std !== Auth::guard('student')->user()->username_std) {
            return redirect()->route('student.menu')
                ->with('error', 'ไม่มีสิทธิ์เสนอหัวข้อ');
        }
        
        // ตรวจสอบว่าสามารถเสนอได้หรือไม่
        if (!$group->canProposeProject()) {
            return redirect()->route('student.menu')
                ->with('error', 'ต้องรอให้สมาชิกตอบรับคำเชิญก่อนถึงจะเสนอหัวข้อได้');
        }
        
        $request->validate([
            'proposed_title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'proposed_to' => 'required|exists:user,username_user'
        ]);
        
        // ตรวจสอบว่า lecturer มี role lecturer จริงหรือไม่
        $lecturer = User::where('username_user', $request->proposed_to)
            ->where('role', 8192)
            ->first();
            
        if (!$lecturer) {
            return back()->with('error', 'ผู้ใช้ที่เลือกไม่ใช่อาจารย์');
        }
        
        DB::beginTransaction();
        try {
            // สร้างข้อเสนอ
            ProjectProposal::create([
                'group_id' => $groupId,
                'proposed_title' => $request->proposed_title,
                'description' => $request->description,
                'proposed_to' => $request->proposed_to,
                'proposed_by' => Auth::guard('student')->user()->username_std,
                'status' => 'pending'
            ]);
            
            // อัพเดต project status เป็น pending
            if ($group->project) {
                $group->project->update([
                    'status_project' => 'pending',
                    'project_name' => $request->proposed_title // เก็บชื่อโครงงานที่เสนอไว้ด้วย
                ]);
            }
            
            DB::commit();
            return redirect()->route('groups.show', $groupId)
                ->with('success', 'ส่งข้อเสนอหัวข้อสำเร็จ รอการพิจารณาจากอาจารย์');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
    
    // แสดงรายการข้อเสนอของ lecturer
    public function lecturerIndex()
    {
        $username = Auth::guard('web')->user()->username_user;
        
        $proposals = ProjectProposal::with(['group.members.student', 'student'])
            ->where('proposed_to', $username)
            ->orderBy('status', 'asc') // pending ก่อน
            ->orderBy('proposed_at', 'desc')
            ->get();
            
        return view('lecturer.proposals.index', compact('proposals'));
    }
    
    // แสดงรายละเอียดข้อเสนอ
    public function show($proposalId)
    {
        $proposal = ProjectProposal::with(['group.members.student', 'student', 'lecturer'])
            ->findOrFail($proposalId);
        
        // ตรวจสอบสิทธิ์
        $user = Auth::guard('web')->user();
        if ($proposal->proposed_to !== $user->username_user) {
            return redirect()->route('lecturer.proposals.index')
                ->with('error', 'ไม่มีสิทธิ์ดูข้อเสนอนี้');
        }
        
        return view('lecturer.proposals.show', compact('proposal'));
    }
    
    // อนุมัติข้อเสนอ
    public function approve($proposalId)
    {
        $proposal = ProjectProposal::with(['group.project'])->findOrFail($proposalId);
        
        // ตรวจสอบสิทธิ์
        $user = Auth::guard('web')->user();
        if ($proposal->proposed_to !== $user->username_user) {
            return back()->with('error', 'ไม่มีสิทธิ์ดำเนินการ');
        }
        
        if ($proposal->status !== 'pending') {
            return back()->with('error', 'ข้อเสนอนี้ได้รับการตอบกลับแล้ว');
        }
        
        DB::beginTransaction();
        try {
            // อัพเดทสถานะข้อเสนอ
            $proposal->update([
                'status' => 'approved',
                'responded_at' => now()
            ]);
            
            // อัพเดต project status เป็น approved
            if ($proposal->group->project) {
                $proposal->group->project->update([
                    'status_project' => 'approved',
                    'advisor_code' => $user->user_code // บันทึก lecturer ที่อนุมัติเป็นที่ปรึกษา (ใช้ user_code)
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('lecturer.proposals.index')
                ->with('success', 'อนุมัติข้อเสนอสำเร็จ');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
    
    // ปฏิเสธข้อเสนอ
    public function reject(Request $request, $proposalId)
    {
        $proposal = ProjectProposal::with(['group.project'])->findOrFail($proposalId);
        
        // ตรวจสอบสิทธิ์
        $user = Auth::guard('web')->user();
        if ($proposal->proposed_to !== $user->username_user) {
            return back()->with('error', 'ไม่มีสิทธิ์ดำเนินการ');
        }
        
        if ($proposal->status !== 'pending') {
            return back()->with('error', 'ข้อเสนอนี้ได้รับการตอบกลับแล้ว');
        }
        
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);
        
        DB::beginTransaction();
        try {
            // อัพเดต proposal status
            $proposal->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'responded_at' => now()
            ]);
            
            // อัพเดต project status เป็น rejected
            if ($proposal->group->project) {
                $proposal->group->project->update([
                    'status_project' => 'rejected'
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('lecturer.proposals.index')
                ->with('success', 'ปฏิเสธข้อเสนอแล้ว');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
    
    // Coordinator - ดูข้อเสนอทั้งหมดในระบบ
    public function coordinatorIndex()
    {
        $proposals = ProjectProposal::with(['group.members.student', 'student', 'lecturer'])
            ->orderBy('status', 'asc') // pending ก่อน
            ->orderBy('proposed_at', 'desc')
            ->get();
            
        return view('coordinator.proposals.index', compact('proposals'));
    }
}
