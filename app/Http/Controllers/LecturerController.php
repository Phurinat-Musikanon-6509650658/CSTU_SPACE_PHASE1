<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectEvaluation;
use App\Models\ProjectProposal;
use App\Models\GroupMember;
use App\Models\Subject;
use App\Helpers\SubjectTimingHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LecturerController extends Controller
{
    /**
     * แสดง Dashboard/Menu สำหรับอาจารย์ พร้อมการแจ้งเตือน
     */
    public function dashboard()
    {
        $user = Auth::guard('web')->user();
        $userCode = $user->user_code;
        $username = $user->username_user;

        // ตรวจสอบการแจ้งเตือนต่างๆ (ภายใน 5 นาที)
        $timeThreshold = now()->subMinutes(5);

        // 1. ข้อเสนอโครงงานใหม่ที่รอพิจารณา
        $newProposals = ProjectProposal::where('proposed_to', $username)
            ->where('status', 'pending')
            ->where('proposed_at', '>=', $timeThreshold)
            ->count();

        // 2. รายงานที่ส่งมาใหม่ (โครงงานที่ตนเองเป็น advisor)
        $newReports = Project::whereHas('advisorLecturer', fn($q) => $q->where('user_code', $userCode))
            ->whereNotNull('submission_file')
            ->where('submitted_at', '>=', $timeThreshold)
            ->count();

        // 3. กลุ่มที่เพิ่งสร้างและเสนอมาหาตนเอง
        $recentGroups = ProjectProposal::where('proposed_to', $username)
            ->where('proposed_at', '>=', $timeThreshold)
            ->count();

        // 4. Check for exam schedule assignments (within last 30 minutes)
        $examScheduled = Project::whereHas('projectLecturers', fn($q) => $q->where('user_code', $userCode))
            ->whereNotNull('exam_datetime')
            ->where('updated_at', '>=', now()->subMinutes(30))
            ->with(['group', 'advisorLecturer'])
            ->get();

        if ($examScheduled->isNotEmpty()) {
            $schedules = $examScheduled->map(function($project) use ($userCode) {
                $role = 'กรรมการ';
                if ($project->advisorLecturer?->user_code == $userCode) $role = 'อาจารย์ที่ปรึกษา';
                
                return [
                    'project_name' => $project->project_name_th,
                    'exam_datetime' => \Carbon\Carbon::parse($project->exam_datetime)->locale('th')->translatedFormat('d F Y เวลา H:i น.'),
                    'role' => $role
                ];
            })->toArray();
            
            session()->flash('exam_scheduled', $schedules);
        }
        
        // สถิติรวม
        $stats = [
            'pending_proposals' => ProjectProposal::where('proposed_to', $username)
                ->where('status', 'pending')
                ->count(),
            'approved_proposals' => ProjectProposal::where('proposed_to', $username)
                ->where('status', 'approved')
                ->count(),
            'my_projects' => Project::whereHas('advisorLecturer', fn($q) => $q->where('user_code', $userCode))->count(),
            'pending_evaluations' => Project::whereHas('projectLecturers', fn($q) => $q->where('user_code', $userCode))
                ->whereNotNull('exam_datetime')
                ->whereDoesntHave('evaluations', function($q) use ($userCode) {
                    $q->where('evaluator_code', $userCode);
                })
                ->count(),
        ];

        // ส่งข้อมูลไปยัง View
        return view('lecturer.dashboard', compact('stats', 'newProposals', 'newReports', 'recentGroups'));
    }

    /**
     * แสดงรายการโครงงานของอาจารย์
     */
    public function myProjects()
    {
        $user = Auth::guard('web')->user();
        $userCode = $user->user_code;

        // ดึงโครงงานที่เป็น advisor
        $projects = Project::with(['group.members.student', 'advisorLecturer.user', 'group.latestProposal'])
            ->whereHas('advisorLecturer', fn($q) => $q->where('user_code', $userCode))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('lecturer.projects.index', compact('projects'));
    }

    /**
     * แสดงรายการโครงงานที่ต้องประเมิน
     */
    public function evaluationsIndex()
    {
        $user = Auth::guard('web')->user();
        $userCode = $user->user_code;

        // ดึงโครงงานที่อาจารย์เป็น advisor หรือ committee
        $projects = Project::with([
                'group.members.student',
                'advisorLecturer.user',
                'committeeLecturers.user',
                'evaluations' => fn($q) => $q->where('evaluator_code', $userCode),
            ])
            ->whereHas('projectLecturers', fn($q) => $q->where('user_code', $userCode))
            ->whereNotNull('exam_datetime') // มีตารางสอบแล้ว
            ->orderBy('exam_datetime', 'asc')
            ->paginate(20);

        return view('lecturer.evaluations.index', compact('projects'));
    }

    /**
     * แสดงฟอร์มให้คะแนน
     */
    public function evaluateForm(Request $request, $projectId)
    {
        $user = Auth::guard('web')->user();
        $userCode = $user->user_code;

        $project = Project::with(['group.members.student', 'advisorLecturer.user', 'committeeLecturers.user', 'projectLecturers', 'evaluations'])
            ->findOrFail($projectId);

        $role = $project->getEvaluatorRole($userCode);

        if (!$role) {
            return redirect()->route('lecturer.evaluations.index')
                ->with('error', 'คุณไม่มีสิทธิ์ประเมินโครงงานนี้');
        }

        // ดึงการประเมินครั้งแรก (ถ้ามี) เพื่อแสดงสถานะว่าเคยประเมินหรือยัง
        $evaluation = $project->evaluations
            ->where('evaluator_code', $userCode)
            ->where('evaluator_role', $role)
            ->first();

        // ตรวจสอบช่วงเวลาประเมิน/แก้ไขคะแนนจาก Subject
        $subject = Subject::where('subject_code', $project->group->subject_code ?? '')
            ->where('year',     $project->group->year)
            ->where('semester', $project->group->semester)
            ->first();
        $canSubmitNew = !$subject || $subject->canEvaluateNow();
        $canEditGrade = !$subject || $subject->canEditGradeNow();
        $subjectLockMessage = null;
        if ($subject) {
            if (!$evaluation && !$canSubmitNew) {
                $subjectLockMessage = SubjectTimingHelper::getEvaluationLockMessage($subject);
            } elseif ($evaluation && !$canEditGrade) {
                $subjectLockMessage = SubjectTimingHelper::getGradeEditLockMessage($subject);
            }
        }

        return view('lecturer.evaluations.form', compact(
            'project', 'role', 'evaluation',
            'canSubmitNew', 'canEditGrade', 'subjectLockMessage'
        ));
    }

    /**
     * Export ใบประเมินสำหรับลงลายเซ็น
     */
    public function exportEvaluation(Request $request, $projectId)
    {
        $user = Auth::guard('web')->user();
        $userCode = $user->user_code;

        $project = Project::with(['group.members.student', 'advisorLecturer.user', 'committeeLecturers.user', 'projectLecturers', 'evaluations'])
            ->findOrFail($projectId);

        $role = $project->getEvaluatorRole($userCode);

        if (!$role) {
            return redirect()->route('lecturer.evaluations.index')
                ->with('error', 'คุณไม่มีสิทธิ์ประเมินโครงงานนี้');
        }

        $students = $project->group->members->pluck('student')->take(2);

        $scores = [];
        foreach ($students as $idx => $student) {
            $eval = $project->evaluations
                ->where('student_id', $student->student_id)
                ->where('evaluator_code', $userCode)
                ->where('evaluator_role', $role)
                ->first();
            $scores[$idx] = [
                'part1'  => $eval ? ($eval->part1_score  ?? '-') : '-',
                'part2'  => $eval ? ($eval->part2_score  ?? '-') : '-',
                'part3a' => $eval ? ($eval->part3a_score ?? '-') : '-',
                'part3b' => $eval ? ($eval->part3b_score ?? '-') : '-',
                'part3c' => $eval ? ($eval->part3c_score ?? '-') : '-',
            ];
        }

        return view('lecturer.evaluations.export', compact('project', 'role', 'students', 'scores', 'user'));
    }

    /**
     * บันทึกคะแนน (สำหรับ 2 นักศึกษา)
     */
    public function submitEvaluation(Request $request, $projectId)
    {
        $user = Auth::guard('web')->user();
        $userCode = $user->user_code;
        $project = Project::with(['group.members.student', 'projectLecturers', 'evaluations'])->findOrFail($projectId);

        $role = $project->getEvaluatorRole($userCode);

        if (!$role) {
            return redirect()->route('lecturer.evaluations.index')
                ->with('error', 'คุณไม่มีสิทธิ์ประเมินโครงงานนี้');
        }

        // ตรวจสอบช่วงเวลาจาก Subject
        $subject = Subject::where('subject_code', $project->group->subject_code ?? '')
            ->where('year',     $project->group->year)
            ->where('semester', $project->group->semester)
            ->first();
        if ($subject) {
            $existingEval = $project->evaluations
                ->where('evaluator_code', $userCode)
                ->where('evaluator_role', $role)
                ->first();
            if ($existingEval && !$subject->canEditGradeNow()) {
                return redirect()->back()
                    ->with('error', SubjectTimingHelper::getGradeEditLockMessage($subject) ?? 'ปิดการแก้ไขคะแนนแล้ว');
            }
            if (!$existingEval && !$subject->canEvaluateNow()) {
                return redirect()->back()
                    ->with('error', SubjectTimingHelper::getEvaluationLockMessage($subject) ?? 'ปิดการประเมินคะแนนแล้ว');
            }
        }

        // ตรวจสอบ validation สำหรับ 2 นักศึกษา
        $students = $project->group->members->pluck('student')->take(2);
        
        foreach ($students as $index => $student) {
            $name = $student->firstname_std . ' ' . $student->lastname_std;

            $part2  = (float)$request->input("student_{$index}_part2_score", 0);
            $part3a = (float)$request->input("student_{$index}_part3a_score", 0);
            $part3b = (float)$request->input("student_{$index}_part3b_score", 0);
            $part3c = (float)$request->input("student_{$index}_part3c_score", 0);

            if ($part2 < 0 || $part2 > 30) {
                return redirect()->back()
                    ->with('error', "คะแนนส่วนที่ 2 สำหรับ {$name} ต้อง 0-30")
                    ->withInput();
            }
            if ($part3a < 0 || $part3a > 20) {
                return redirect()->back()
                    ->with('error', "คะแนน 3.1 สำหรับ {$name} ต้อง 0-20")
                    ->withInput();
            }
            if ($part3b < 0 || $part3b > 20) {
                return redirect()->back()
                    ->with('error', "คะแนน 3.2 สำหรับ {$name} ต้อง 0-20")
                    ->withInput();
            }
            if ($part3c < 0 || $part3c > 20) {
                return redirect()->back()
                    ->with('error', "คะแนน 3.3 สำหรับ {$name} ต้อง 0-20")
                    ->withInput();
            }

            if ($role === 'advisor') {
                $part1 = (float)$request->input("student_{$index}_part1_score", 0);
                if ($part1 < 0 || $part1 > 10) {
                    return redirect()->back()
                        ->with('error', "คะแนนส่วนที่ 1 สำหรับ {$name} ต้อง 0-10")
                        ->withInput();
                }
            }
        }

        // บันทึกคะแนนสำหรับแต่ละนักศึกษา
        $successCount = 0;
        foreach ($students as $index => $student) {
            $evalData = [
                'part2_score'  => (float)$request->input("student_{$index}_part2_score", 0),
                'part3a_score' => (float)$request->input("student_{$index}_part3a_score", 0),
                'part3b_score' => (float)$request->input("student_{$index}_part3b_score", 0),
                'part3c_score' => (float)$request->input("student_{$index}_part3c_score", 0),
                'submitted_at' => now()
            ];

            if ($role === 'advisor') {
                $evalData['part1_score'] = (float)$request->input("student_{$index}_part1_score", 0);
            }

            ProjectEvaluation::updateOrCreate(
                [
                    'project_id' => $projectId,
                    'student_id' => $student->student_id,
                    'evaluator_code' => $userCode,
                    'evaluator_role' => $role
                ],
                $evalData
            );

            $successCount++;
        }

        return redirect()->route('lecturer.evaluations.index')
            ->with('success', "บันทึกคะแนนเรียบร้อยแล้ว ({$successCount} นักศึกษา)");
    }
}
