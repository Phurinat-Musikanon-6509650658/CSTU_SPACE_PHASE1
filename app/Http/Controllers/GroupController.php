<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupInvitation;
use App\Models\Student;
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

        return view('student.group.create', compact('students'));
    }

    public function store(Request $request)
    {
        $student = Auth::guard('student')->user();

        // ตรวจสอบว่า student มีกลุ่มแล้วหรือยัง
        if ($student->hasGroup()) {
            return redirect()->route('student.menu')->with('error', 'คุณมีกลุ่มแล้ว');
        }

        $request->validate([
            'project_name' => 'required|string|max:255',
            'project_code' => 'required|string|max:50|unique:groups,project_code',
            'subject_code' => 'required|string|max:50',
            'year' => 'required|integer|min:2020|max:2030',
            'semester' => 'required|integer|in:1,2',
            'description' => 'nullable|string|max:1000',
            'invite_username' => 'nullable|string|exists:student,username_std',
        ]);

        DB::beginTransaction();
        try {
            // สร้างกลุ่ม
            $group = Group::create([
                'project_name' => $request->project_name,
                'project_code' => $request->project_code,
                'subject_code' => $request->subject_code,
                'year' => $request->year,
                'semester' => $request->semester,
                'description' => $request->description,
                'status_group' => 'active'
            ]);

            // เพิ่มผู้สร้างเป็นสมาชิกกลุ่ม
            GroupMember::create([
                'group_id' => $group->group_id,
                'username_std' => $student->username_std
            ]);

            // ส่งคำเชิญหากมีการระบุสมาชิกคนที่ 2
            if ($request->invite_username) {
                $invitee = Student::where('username_std', $request->invite_username)->first();
                
                if ($invitee && $invitee->canJoinGroup()) {
                    GroupInvitation::create([
                        'group_id' => $group->group_id,
                        'inviter_username' => $student->username_std,
                        'invitee_username' => $request->invite_username,
                        'message' => 'เชิญเข้าร่วมกลุ่มโครงงาน: ' . $request->project_name,
                        'status' => 'pending'
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('student.menu')->with('success', 'สร้างกลุ่มสำเร็จ');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function show(Group $group)
    {
        $group->load(['members.student', 'invitations.invitee', 'invitations.inviter']);
        return view('student.group.show', compact('group'));
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
                // ถ้าไม่มีสมาชิกเหลือ ให้ลบกลุ่มและคำเชิญทั้งหมด
                GroupInvitation::where('group_id', $group->group_id)->delete();
                $group->delete();
                $message = 'คุณได้ออกจากกลุ่มเรียบร้อยแล้ว และกลุ่มได้ถูกลบเนื่องจากไม่มีสมาชิกเหลือ';
            } else {
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
