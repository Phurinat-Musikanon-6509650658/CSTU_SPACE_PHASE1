<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Project;
use App\Models\User;
use App\Models\ProjectEvaluation;
use App\Models\ProjectGrade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

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
        $group = Group::with([
            'members.student', 
            'project.advisor', 
            'project.committee1', 
            'project.committee2', 
            'project.committee3',
            'latestProposal.lecturer'
        ])->findOrFail($id);

        // ดึงรายชื่ออาจารย์ทั้งหมด (Lecturer role = 8192)
        $lecturers = User::whereRaw('role & 8192 != 0')
            ->orderBy('firstname_user', 'asc')
            ->get();

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

        // ตรวจสอบว่าอาจารย์ไม่ซ้ำกัน
        $lecturers = array_filter([
            $request->advisor_code,
            $request->committee1_code,
            $request->committee2_code,
            $request->committee3_code
        ]);
        
        if (count($lecturers) !== count(array_unique($lecturers))) {
            return back()->with('error', 'ไม่สามารถเลือกอาจารย์คนเดียวกันในหลายตำแหน่งได้');
        }

        $group = Group::with('project')->findOrFail($id);

        if (!$group->project) {
            return back()->with('error', 'กลุ่มนี้ยังไม่มีโครงงาน');
        }

        DB::beginTransaction();
        try {
            // อัพเดต projects table (ไม่ต้อง set relationship IDs)
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

            // หมายเหตุ: ไม่อัพเดต group status เพราะ groups.status_group มี ENUM แยกต่างหาก
            // status_group ใช้สำหรับสถานะการจัดกลุ่ม (not_created, created, member_left, member_added, disbanded)
            // ส่วนสถานะการอนุมัติอยู่ใน projects.status_project แล้ว

            DB::commit();
            return back()->with('success', 'อัพเดทข้อมูลโครงงานและกลุ่มเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollback();
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

    // Export CSV - ยึด project table เป็นหลัก
    public function exportCsv(Request $request)
    {
        // ดึงข้อมูลจาก projects table
        $query = Project::with([
            'group.members.student',
            'advisor',
            'committee1',
            'committee2',
            'committee3'
        ]);

        // ฟิลเตอร์ตามเงื่อนไข
        if ($request->status) {
            $query->where('status_project', $request->status);
        }
        if ($request->year) {
            $query->whereHas('group', function($q) use ($request) {
                $q->where('year', $request->year);
            });
        }
        if ($request->semester) {
            $query->whereHas('group', function($q) use ($request) {
                $q->where('semester', $request->semester);
            });
        }

        $projects = $query->orderBy('project_code', 'asc')->get();

        // สร้าง CSV
        $filename = 'projects_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($projects) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($file, [
                'project_id',
                'project_code',
                'project_name',
                'student1',
                'student2',
                'advisor_code',
                'committee1_code',
                'committee2_code',
                'committee3_code',
                'exam_datetime'
            ]);

            // Data rows
            foreach ($projects as $index => $project) {
                $members = $project->group->members ?? collect();
                $member1 = $members->get(0);
                $member2 = $members->get(1);
                
                fputcsv($file, [
                    $index + 1,
                    $project->project_code ?? '-',
                    $project->project_name ?? '-',
                    $member1 ? "{$member1->student->firstname_std} {$member1->student->lastname_std}" : '-',
                    $member2 ? "{$member2->student->firstname_std} {$member2->student->lastname_std}" : '-',
                    $project->advisor_code ?? '-',
                    $project->committee1_code ?? '-',
                    $project->committee2_code ?? '-',
                    $project->committee3_code ?? '-',
                    $project->exam_datetime ? $project->exam_datetime->format('d.m.y, H:i - ') : '-',
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    // ====================================
    // Schedule & Committee Management
    // ====================================
    
    public function schedulesIndex(Request $request)
    {
        $query = Project::with(['group.members.student', 'advisor', 'committee1', 'committee2', 'committee3']);

        // Filter by semester/year
        if ($request->semester) {
            $query->whereHas('group', function($q) use ($request) {
                $q->where('semester', $request->semester);
            });
        }

        if ($request->year) {
            $query->whereHas('group', function($q) use ($request) {
                $q->where('year', $request->year);
            });
        }

        // Filter by exam status
        if ($request->has_exam) {
            if ($request->has_exam === '1') {
                $query->whereNotNull('exam_datetime');
            } else {
                $query->whereNull('exam_datetime');
            }
        }

        $projects = $query->orderBy('exam_datetime', 'asc')->paginate(20);

        return view('coordinator.schedules.index', compact('projects'));
    }

    public function scheduleEdit($projectId)
    {
        $project = Project::with(['group.members.student', 'advisor', 'committee1', 'committee2', 'committee3'])
            ->findOrFail($projectId);
        
        $lecturers = User::where('role', '&', 8192)->orderBy('firstname_user')->get();

        return view('coordinator.schedules.edit', compact('project', 'lecturers'));
    }

    public function scheduleUpdate(Request $request, $projectId)
    {
        $request->validate([
            'exam_datetime' => 'nullable|date',
            'advisor_code' => 'nullable|exists:user,user_code',
            'committee1_code' => 'nullable|exists:user,user_code',
            'committee2_code' => 'nullable|exists:user,user_code',
            'committee3_code' => 'nullable|exists:user,user_code',
        ]);

        $project = Project::findOrFail($projectId);
        
        // Check if exam_datetime changed (for notification)
        $examDateChanged = $project->exam_datetime != $request->exam_datetime && $request->exam_datetime != null;
        
        // Check if committee changed
        $committeeChanged = (
            $project->advisor_code != $request->advisor_code ||
            $project->committee1_code != $request->committee1_code ||
            $project->committee2_code != $request->committee2_code ||
            $project->committee3_code != $request->committee3_code
        );

        $project->update([
            'exam_datetime' => $request->exam_datetime,
            'advisor_code' => $request->advisor_code,
            'committee1_code' => $request->committee1_code,
            'committee2_code' => $request->committee2_code,
            'committee3_code' => $request->committee3_code,
        ]);

        // Set flash notifications
        if ($examDateChanged) {
            session()->flash('exam_schedule_updated', [
                'project_id' => $project->project_id,
                'exam_datetime' => $request->exam_datetime,
            ]);
        }
        
        if ($committeeChanged) {
            session()->flash('committee_updated', [
                'project_id' => $project->project_id,
            ]);
        }

        return redirect()->route('coordinator.schedules.index')
            ->with('success', 'อัพเดทตารางสอบและคณะกรรมการเรียบร้อยแล้ว');
    }

    // ====================================
    // Evaluation & Grading
    // ====================================
    
    public function evaluationsIndex(Request $request)
    {
        $query = Project::with(['group.members.student', 'evaluations.evaluator', 'grade']);

        // Filter
        if ($request->semester) {
            $query->whereHas('group', function($q) use ($request) {
                $q->where('semester', $request->semester);
            });
        }

        $projects = $query->orderBy('project_id', 'desc')->paginate(20);

        return view('coordinator.evaluations.index', compact('projects'));
    }

    public function viewScores($projectId)
    {
        $project = Project::with(['group.members.student', 'evaluations.evaluator', 'advisor', 'committee1', 'committee2', 'committee3'])
            ->findOrFail($projectId);

        return view('coordinator.evaluations.scores', compact('project'));
    }

    public function viewGrades($projectId)
    {
        $project = Project::with(['group.members.student', 'grade', 'evaluations.evaluator'])
            ->findOrFail($projectId);

        return view('coordinator.evaluations.grades', compact('project'));
    }

    /**
     * แสดงหน้าสรุปคะแนนทั้งหมด
     */
    public function scoreSummary(Request $request)
    {
        $query = Project::with([
            'group.members.student', 
            'grade', 
            'evaluations.evaluator',
            'advisor'
        ]);

        // Filter by semester/year
        if ($request->semester) {
            $query->whereHas('group', function($q) use ($request) {
                $q->where('semester', $request->semester);
            });
        }

        if ($request->year) {
            $query->whereHas('group', function($q) use ($request) {
                $q->where('year', $request->year);
            });
        }

        // Filter by grade released
        if ($request->has('grade_released')) {
            if ($request->grade_released === '1') {
                $query->whereHas('grade', function($q) {
                    $q->where('grade_released', true);
                });
            } elseif ($request->grade_released === '0') {
                $query->whereHas('grade', function($q) {
                    $q->where('grade_released', false);
                });
            }
        }

        $projects = $query->whereHas('grade')
            ->orderBy('project_id', 'desc')
            ->paginate(20);

        // Statistics
        $stats = [
            'total_projects' => Project::whereHas('grade')->count(),
            'grade_released' => Project::whereHas('grade', function($q) {
                $q->where('grade_released', true);
            })->count(),
            'pending_confirmation' => Project::whereHas('grade', function($q) {
                $q->where('all_confirmed', false);
            })->count(),
            'average_score' => Project::whereHas('grade')->join('project_grades', 'projects.project_id', '=', 'project_grades.project_id')->avg('final_score'),
        ];

        return view('coordinator.evaluations.summary', compact('projects', 'stats'));
    }

    /**
     * ปล่อยเกรดให้นักศึกษาดู
     */
    public function releaseGrade($projectId)
    {
        $project = Project::with('grade')->findOrFail($projectId);

        if (!$project->grade) {
            return redirect()->back()->with('error', 'ยังไม่มีเกรดสำหรับโครงงานนี้');
        }

        if (!$project->grade->all_confirmed) {
            return redirect()->back()->with('error', 'ยังมีอาจารย์บางท่านที่ยังไม่ยืนยันเกรด');
        }

        $project->grade->update([
            'grade_released' => true,
            'grade_released_at' => now()
        ]);
        
        // Flash notification for students
        session()->flash('grade_released', [
            'project_id' => $project->project_id,
            'final_grade' => $project->grade->final_grade,
        ]);

        return redirect()->back()->with('success', 'ปล่อยเกรดให้นักศึกษาดูเรียบร้อยแล้ว');
    }
}
