<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupInvitation;
use App\Models\Student;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GroupController extends Controller
{
    public function create()
    {
        $student = Auth::guard('student')->user();
        
        // ตรวจสอบว่า student มีกลุ่มแล้วหรือยัง
        if ($student->hasGroup()) {
            return redirect()->route('student.menu')->with('error', 'คุณมีกลุ่มแล้ว');
        }

        // ดึงรายชื่อ student ทั้งหมดยกเว้นตัวเอง
        $students = Student::where('username_std', '!=', $student->username_std)
            ->whereDoesntHave('groups') // student ที่ยังไม่มีกลุ่ม
            ->get();

        // หาหมายเลขกลุ่มถัดไป (หาช่องว่างก่อน ถ้าไม่มีค่อยใช้เลขใหม่)
        $existingGroupIds = Group::pluck('group_id')->sort()->values()->toArray();
        $nextGroupNumber = null;
        
        // หาเลขที่ว่าง (ถูกลบไปแล้ว)
        for ($i = 1; $i <= count($existingGroupIds) + 1; $i++) {
            if (!in_array($i, $existingGroupIds)) {
                $nextGroupNumber = $i;
                break;
            }
        }
        
        // ถ้ายังไม่ได้เลข (กรณีไม่มีกลุ่มเลย)
        if ($nextGroupNumber === null) {
            $nextGroupNumber = 1;
        }

        // ดึงข้อมูล course_code, semester, year จาก student
        $courseCode = $student->course_code ?? 'CS303';
        $semester = $student->semester ?? 2;
        $year = $student->year ?? 2568;

        return view('student.group.create', compact('students', 'nextGroupNumber', 'courseCode', 'semester', 'year'));
    }

    public function store(Request $request)
    {
        $student = Auth::guard('student')->user();

        // ตรวจสอบว่า student มีกลุ่มแล้วหรือยัง
        if ($student->hasGroup()) {
            return redirect()->route('student.menu')->with('error', 'คุณมีกลุ่มแล้ว');
        }

        $request->validate([
            'subject_code' => 'required|string|in:CS303,CS403',
            'year' => 'required|integer',
            'semester' => 'required|integer|in:1,2',
            'invite_username' => 'nullable|string|exists:student,username_std',
        ]);

        DB::beginTransaction();
        try {
            // ล็อคตาราง groups เพื่อป้องกัน race condition (หลายคนสร้างพร้อมกัน)
            // ใช้ lockForUpdate() เพื่อให้คนอื่นรอจนกว่าจะสร้างเสร็จ
            $existingGroupIds = Group::lockForUpdate()->pluck('group_id')->sort()->values()->toArray();
            
            $nextGroupId = null;
            
            // หาเลขที่ว่าง (ถูกลบไปแล้ว)
            for ($i = 1; $i <= count($existingGroupIds) + 1; $i++) {
                if (!in_array($i, $existingGroupIds)) {
                    $nextGroupId = $i;
                    break;
                }
            }
            
            // ถ้ายังไม่ได้เลข (กรณีไม่มีกลุ่มเลย)
            if ($nextGroupId === null) {
                $nextGroupId = 1;
            }
            
            // สร้างกลุ่มโดยระบุ group_id (ใครมาก่อนได้เลขนั้นก่อน)
            // ดึงข้อมูล course_code, semester, year จาก student (ล็อกตามนักศึกษา)
            $group = Group::create([
                'group_id' => $nextGroupId,
                'subject_code' => $student->course_code ?? 'CS303', // ใช้รหัสวิชาจาก student
                'year' => $student->year ?? 2568, // ใช้ปีจาก student
                'semester' => $student->semester ?? 2, // ใช้เทอมจาก student
                'status_group' => 'created'
            ]);

            // เพิ่มผู้สร้างเป็นสมาชิกกลุ่ม
            GroupMember::create([
                'group_id' => $group->group_id,
                'username_std' => $student->username_std
            ]);

            // สร้าง Project record (ยังไม่มีข้อมูลเต็ม)
            // project_code format: 68-1-01_TBD-r2 (year-semester-groupid_advisor-type+count)
            $memberCount = 1; // ตอนสร้างมีแค่คนเดียว
            $studentType = $student->student_type ?? 'r'; // ใช้จาก student ที่ login
            
            $projectCode = sprintf(
                '%02d-%d-%02d_TBD-%s%d',
                $group->year % 100,
                $group->semester,
                $group->group_id,
                $studentType,
                $memberCount
            );
            
            Project::create([
                'group_id' => $group->group_id,
                'project_code' => $projectCode,
                'status_project' => 'not_proposed',
                'student_type' => $studentType
            ]);

            // ส่งคำเชิญหากมีการระบุสมาชิกคนที่ 2
            if ($request->invite_username) {
                $invitee = Student::where('username_std', $request->invite_username)->first();
                
                if ($invitee && $invitee->canJoinGroup()) {
                    GroupInvitation::create([
                        'group_id' => $group->group_id,
                        'inviter_username' => $student->username_std,
                        'invitee_username' => $request->invite_username,
                        'message' => 'เชิญเข้าร่วมกลุ่มโครงงาน วิชา ' . $group->subject_code,
                        'status' => 'pending'
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('student.menu')->with('success', 'สร้างกลุ่มสำเร็จ');

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            // กรณี duplicate key (เผื่อมีปัญหา race condition)
            if ($e->getCode() == 23000) {
                return back()->with('error', 'มีคนสร้างกลุ่มพร้อมกันกับคุณ กรุณาลองใหม่อีกครั้ง');
            }
            return back()->with('error', 'เกิดข้อผิดพลาดในการสร้างกลุ่ม: ' . $e->getMessage());
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function show(Group $group)
    {
        $student = Auth::guard('student')->user();
        
        // ตรวจสอบว่า student อยู่ในกลุ่มนี้หรือไม่
        $isMember = $group->members->contains('username_std', $student->username_std);
        
        if (!$isMember) {
            return redirect()->route('student.menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงข้อมูลกลุ่มนี้');
        }
        
        $group->load([
            'members.student', 
            'invitations.invitee', 
            'invitations.inviter', 
            'latestProposal.lecturer',
            'project.advisor',
            'project.committee1',
            'project.committee2',
            'project.committee3'
        ]);
        
        // ตรวจสอบสถานะ proposal ล่าสุดและแจ้งเตือน
        if ($group->latestProposal) {
            $proposal = $group->latestProposal;
            
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
        
        return view('student.groups.show', compact('group'));
    }

    // API สำหรับค้นหา student
    public function searchStudents(Request $request)
    {
        $search = $request->get('search');
        $currentStudent = Auth::guard('student')->user();

        $students = Student::where('username_std', '!=', $currentStudent->username_std)
            ->whereDoesntHave('groups')
            ->where(function($query) use ($search) {
                $query->where('firstname_std', 'like', "%{$search}%")
                      ->orWhere('lastname_std', 'like', "%{$search}%")
                      ->orWhere('username_std', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get(['username_std', 'firstname_std', 'lastname_std']);

        return response()->json($students);
    }

    public function leaveGroup(Request $request)
    {
        $student = Auth::guard('student')->user();
        
        // ตรวจสอบว่า student มีกลุ่มหรือไม่
        if (!$student->hasGroup()) {
            return redirect()->route('student.menu')->with('error', 'คุณไม่ได้อยู่ในกลุ่มใดๆ');
        }

        DB::beginTransaction();
        try {
            // ค้นหา group member ของ student
            $groupMember = GroupMember::where('username_std', $student->username_std)->first();
            
            if (!$groupMember) {
                DB::rollback();
                return redirect()->route('student.menu')->with('error', 'ไม่พบข้อมูลสมาชิกในกลุ่ม');
            }
            
            $group = $groupMember->group;
            
            // ลบ student ออกจากกลุ่ม
            $groupMember->delete();
            
            // ตรวจสอบว่าในกลุ่มยังมีสมาชิกเหลืออยู่หรือไม่
            $remainingMembers = GroupMember::where('group_id', $group->group_id)->count();
            
            if ($remainingMembers === 0) {
                // ถ้าไม่มีสมาชิกเหลือ ให้ยุบกลุ่มและลบข้อมูลที่เกี่ยวข้อง
                
                // อัพเดตสถานะกลุ่มเป็น disbanded
                $group->update(['status_group' => 'disbanded']);
                
                // ลบคำเชิญทั้งหมด
                GroupInvitation::where('group_id', $group->group_id)->delete();
                
                // ลบโครงงาน (ถ้ายังไม่ได้รับการอนุมัติ)
                if ($group->project && $group->project->status_project !== 'approved') {
                    $group->project->delete();
                }
                
                // ลบ proposals ทั้งหมด
                $group->proposals()->delete();
                
                // ลบกลุ่ม
                $group->delete();
                
                $message = 'คุณได้ออกจากกลุ่มเรียบร้อยแล้ว และกลุ่มได้ถูกยุบเนื่องจากไม่มีสมาชิกเหลือ';
            } else {
                // อัพเดตสถานะกลุ่มเป็น member_left
                $group->update(['status_group' => 'member_left']);
                
                $message = 'คุณได้ออกจากกลุ่มเรียบร้อยแล้ว';
            }
            
            DB::commit();
            return redirect()->route('student.menu')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error leaving group: ' . $e->getMessage());
            return redirect()->route('student.menu')->with('error', 'เกิดข้อผิดพลาดในการออกจากกลุ่ม: ' . $e->getMessage());
        }
    }
}
