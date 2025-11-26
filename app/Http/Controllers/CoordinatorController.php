<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CoordinatorController extends Controller
{
    // หน้าแดชบอร์ดหลัก
    public function dashboard()
    {
        $user = Auth::guard('web')->user();
        
        // สถิติรวม
        $stats = [
            'total_groups' => Group::count(),
            'pending_groups' => Group::where('status_group', 'pending')->count(),
            'approved_groups' => Group::where('status_group', 'approved')->count(),
            'total_projects' => Project::count(),
        ];

        // กลุ่มที่รอการอนุมัติ
        $pendingGroups = Group::with(['members.student', 'project'])
            ->where('status_group', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('coordinator.dashboard', compact('stats', 'pendingGroups'));
    }

    // หน้าจัดการกลุ่มทั้งหมด
    public function groups(Request $request)
    {
        $query = Group::with(['members.student', 'project', 'latestProposal.lecturer']);

        // ฟิลเตอร์ตามสถานะ
        if ($request->status) {
            $query->where('status_group', $request->status);
        }

        // ฟิลเตอร์ตามรหัสวิชา
        if ($request->subject) {
            $query->where('subject_code', $request->subject);
        }

        // ฟิลเตอร์ตามเทอม
        if ($request->semester) {
            $query->where('semester', $request->semester);
        }

        $groups = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('coordinator.groups.index', compact('groups'));
    }

    // หน้ารายละเอียดกลุ่ม
    public function groupShow($id)
    {
        $group = Group::with(['members.student', 'project.advisor', 'project.committee1', 'project.committee2', 'project.committee3', 'latestProposal.lecturer'])
            ->findOrFail($id);

        // ดึงรายชื่ออาจารย์ทั้งหมด (Lecturer role = 8192)
        $lecturers = User::where('role', 8192)->get();

        return view('coordinator.groups.show', compact('group', 'lecturers'));
    }

    // อนุมัติกลุ่มและสร้าง Project
    public function approveGroup(Request $request, $id)
    {
        $user = Auth::guard('web')->user();
        
        // ป้องกัน Staff แก้ไขข้อมูล
        if (!$user->canEdit()) {
            return redirect()->route('coordinator.groups.show', $id)
                ->with('error', 'คุณไม่มีสิทธิ์อนุมัติกลุ่ม (Staff read-only)');
        }

        $request->validate([
            'project_name' => 'required|string|max:255',
            'advisor_code' => 'required|string|exists:user,user_code',
            'student_type' => 'required|in:r,s',
        ]);

        $group = Group::findOrFail($id);

        DB::beginTransaction();
        try {
            // อัพเดทสถานะกลุ่ม
            $group->update(['status_group' => 'approved']);

            // สร้าง Project
            $memberCount = $group->members->count();
            $advisorCode = $request->advisor_code;
            $studentType = $request->student_type;

            // สร้าง project_code: 68-1-01_kdc-r1
            $projectCode = sprintf(
                '%02d-%d-%02d_%s-%s%d',
                $group->year % 100, // 2568 -> 68
                $group->semester,
                $group->group_id,
                $advisorCode,
                $studentType,
                $memberCount
            );

            Project::create([
                'group_id' => $group->group_id,
                'project_name' => $request->project_name,
                'project_code' => $projectCode,
                'advisor_code' => $request->advisor_code,
                'student_type' => $studentType,
                'status_project' => 'in_progress',
            ]);

            DB::commit();
            return redirect()->route('coordinator.groups.show', $id)
                ->with('success', 'อนุมัติกลุ่มและสร้างโครงงานเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    // อัพเดทข้อมูล Project
    public function updateProject(Request $request, $id)
    {
        $user = Auth::guard('web')->user();
        
        // ป้องกัน Staff แก้ไขข้อมูล
        if (!$user->canEdit()) {
            return back()->with('error', 'คุณไม่มีสิทธิ์แก้ไขข้อมูล (Staff read-only)');
        }

        $request->validate([
            'project_name' => 'nullable|string|max:255',
            'advisor_code' => 'nullable|string|exists:user,user_code',
            'committee1_code' => 'nullable|string|exists:user,user_code',
            'committee2_code' => 'nullable|string|exists:user,user_code',
            'committee3_code' => 'nullable|string|exists:user,user_code',
            'exam_datetime' => 'nullable|date',
            'project_type' => 'nullable|string',
            'status_project' => 'nullable|string',
        ]);

        $group = Group::with('project')->findOrFail($id);

        if (!$group->project) {
            return back()->with('error', 'กลุ่มนี้ยังไม่มีโครงงาน');
        }

        try {
            $group->project->update($request->only([
                'project_name',
                'advisor_code',
                'committee1_code',
                'committee2_code',
                'committee3_code',
                'exam_datetime',
                'project_type',
                'status_project'
            ]));

            return back()->with('success', 'อัพเดทข้อมูลโครงงานเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    // จัดการปีการศึกษาและเทอม
    public function settings()
    {
        // ดึงค่าปัจจุบันจากฐานข้อมูล (อาจจะสร้างตาราง settings แยก)
        $currentYear = 2568;
        $currentSemester = 1;

        return view('coordinator.settings', compact('currentYear', 'currentSemester'));
    }
}
