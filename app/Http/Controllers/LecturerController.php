<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectEvaluation;
use App\Models\ProjectGrade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LecturerController extends Controller
{
    /**
     * แสดงรายการโครงงานที่ต้องประเมิน
     */
    public function evaluationsIndex()
    {
        $user = Auth::guard('web')->user();
        $userCode = $user->user_code;

        // ดึงโครงงานที่อาจารย์เป็น advisor หรือ committee
        $projects = Project::with(['group.members.student', 'evaluations' => function($q) use ($userCode) {
                $q->where('evaluator_code', $userCode);
            }, 'grade'])
            ->where(function($query) use ($userCode) {
                $query->where('advisor_code', $userCode)
                      ->orWhere('committee1_code', $userCode)
                      ->orWhere('committee2_code', $userCode)
                      ->orWhere('committee3_code', $userCode);
            })
            ->whereNotNull('exam_datetime') // มีตารางสอบแล้ว
            ->orderBy('exam_datetime', 'asc')
            ->paginate(20);

        return view('lecturer.evaluations.index', compact('projects'));
    }

    /**
     * แสดงฟอร์มให้คะแนน
     */
    public function evaluateForm($projectId)
    {
        $user = Auth::guard('web')->user();
        $userCode = $user->user_code;

        $project = Project::with(['group.members.student', 'advisor', 'committee1', 'committee2', 'committee3'])
            ->findOrFail($projectId);

        // ตรวจสอบว่าอาจารย์คนนี้มีสิทธิ์ประเมินหรือไม่
        $role = null;
        if ($project->advisor_code === $userCode) {
            $role = 'advisor';
        } elseif ($project->committee1_code === $userCode) {
            $role = 'committee1';
        } elseif ($project->committee2_code === $userCode) {
            $role = 'committee2';
        } elseif ($project->committee3_code === $userCode) {
            $role = 'committee3';
        }

        if (!$role) {
            return redirect()->route('lecturer.evaluations.index')
                ->with('error', 'คุณไม่มีสิทธิ์ประเมินโครงงานนี้');
        }

        // ดึงคะแนนเดิม (ถ้ามี)
        $evaluation = ProjectEvaluation::where('project_id', $projectId)
            ->where('evaluator_code', $userCode)
            ->where('evaluator_role', $role)
            ->first();

        return view('lecturer.evaluations.form', compact('project', 'role', 'evaluation'));
    }

    /**
     * บันทึกคะแนน
     */
    public function submitEvaluation(Request $request, $projectId)
    {
        $request->validate([
            'document_score' => 'required|numeric|min:0|max:30',
            'presentation_score' => 'required|numeric|min:0|max:70',
            'comments' => 'nullable|string|max:1000'
        ]);

        $user = Auth::guard('web')->user();
        $userCode = $user->user_code;

        $project = Project::findOrFail($projectId);

        // ตรวจสอบสิทธิ์
        $role = null;
        if ($project->advisor_code === $userCode) {
            $role = 'advisor';
        } elseif ($project->committee1_code === $userCode) {
            $role = 'committee1';
        } elseif ($project->committee2_code === $userCode) {
            $role = 'committee2';
        } elseif ($project->committee3_code === $userCode) {
            $role = 'committee3';
        }

        if (!$role) {
            return redirect()->route('lecturer.evaluations.index')
                ->with('error', 'คุณไม่มีสิทธิ์ประเมินโครงงานนี้');
        }

        // บันทึกคะแนน (update or create)
        ProjectEvaluation::updateOrCreate(
            [
                'project_id' => $projectId,
                'evaluator_code' => $userCode,
                'evaluator_role' => $role
            ],
            [
                'document_score' => $request->document_score,
                'presentation_score' => $request->presentation_score,
                'comments' => $request->comments,
                'submitted_at' => now()
            ]
        );

        // คำนวณเกรด (ถ้ามีคะแนนครบ)
        $this->calculateGradeIfReady($project);

        return redirect()->route('lecturer.evaluations.index')
            ->with('success', 'บันทึกคะแนนเรียบร้อยแล้ว');
    }

    /**
     * ดูเกรดและยืนยัน
     */
    public function viewGrade($projectId)
    {
        $user = Auth::guard('web')->user();
        $userCode = $user->user_code;

        $project = Project::with(['group.members.student', 'evaluations.evaluator', 'grade', 'advisor', 'committee1', 'committee2', 'committee3'])
            ->findOrFail($projectId);

        // ตรวจสอบสิทธิ์
        $role = null;
        if ($project->advisor_code === $userCode) {
            $role = 'advisor';
        } elseif ($project->committee1_code === $userCode) {
            $role = 'committee1';
        } elseif ($project->committee2_code === $userCode) {
            $role = 'committee2';
        } elseif ($project->committee3_code === $userCode) {
            $role = 'committee3';
        }

        if (!$role) {
            return redirect()->route('lecturer.evaluations.index')
                ->with('error', 'คุณไม่มีสิทธิ์ดูข้อมูลโครงงานนี้');
        }

        return view('lecturer.evaluations.grade', compact('project', 'role'));
    }

    /**
     * ยืนยันเกรด
     */
    public function confirmGrade(Request $request, $projectId)
    {
        $user = Auth::guard('web')->user();
        $userCode = $user->user_code;

        $project = Project::with('grade')->findOrFail($projectId);

        if (!$project->grade) {
            return redirect()->back()->with('error', 'ยังไม่มีเกรดให้ยืนยัน');
        }

        // ตรวจสอบสิทธิ์
        $role = null;
        if ($project->advisor_code === $userCode) {
            $role = 'advisor';
        } elseif ($project->committee1_code === $userCode) {
            $role = 'committee1';
        } elseif ($project->committee2_code === $userCode) {
            $role = 'committee2';
        } elseif ($project->committee3_code === $userCode) {
            $role = 'committee3';
        }

        if (!$role) {
            return redirect()->route('lecturer.evaluations.index')
                ->with('error', 'คุณไม่มีสิทธิ์ยืนยันเกรดโครงงานนี้');
        }

        // ยืนยันเกรด
        $project->grade->update([
            $role.'_confirmed' => true,
            $role.'_confirmed_at' => now()
        ]);

        return redirect()->route('lecturer.evaluations.index')
            ->with('success', 'ยืนยันเกรดเรียบร้อยแล้ว');
    }

    /**
     * คำนวณเกรดถ้ามีคะแนนครบ
     */
    private function calculateGradeIfReady(Project $project)
    {
        $expectedCount = 0;
        if ($project->advisor_code) $expectedCount++;
        if ($project->committee1_code) $expectedCount++;
        if ($project->committee2_code) $expectedCount++;
        if ($project->committee3_code) $expectedCount++;

        $evaluationsCount = ProjectEvaluation::where('project_id', $project->project_id)->count();

        if ($evaluationsCount >= $expectedCount && $expectedCount > 0) {
            // คำนวณคะแนนเฉลี่ย
            $avgScore = ProjectEvaluation::where('project_id', $project->project_id)->avg('total_score');
            $grade = ProjectGrade::calculateGrade($avgScore);

            // สร้างหรืออัพเดทเกรด
            ProjectGrade::updateOrCreate(
                ['project_id' => $project->project_id],
                [
                    'final_score' => $avgScore,
                    'grade' => $grade
                ]
            );
        }
    }
}
