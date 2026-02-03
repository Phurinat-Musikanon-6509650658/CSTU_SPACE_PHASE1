<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectEvaluation;
use App\Models\ProjectGrade;
use App\Models\ProjectProposal;
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
        $newReports = Project::where('advisor_code', $userCode)
            ->whereNotNull('submission_file')
            ->where('submitted_at', '>=', $timeThreshold)
            ->count();

        // 3. กลุ่มที่เพิ่งสร้างและเสนอมาหาตนเอง
        $recentGroups = ProjectProposal::where('proposed_to', $username)
            ->where('proposed_at', '>=', $timeThreshold)
            ->count();
        
        // 4. Check for exam schedule assignments (within last 30 minutes)
        $examScheduled = Project::where(function($q) use ($userCode) {
                $q->where('advisor_code', $userCode)
                  ->orWhere('committee1_code', $userCode)
                  ->orWhere('committee2_code', $userCode)
                  ->orWhere('committee3_code', $userCode);
            })
            ->whereNotNull('exam_datetime')
            ->where('updated_at', '>=', now()->subMinutes(30))
            ->with('group')
            ->get();
        
        if ($examScheduled->isNotEmpty()) {
            $schedules = $examScheduled->map(function($project) use ($userCode) {
                $role = 'กรรมการ';
                if ($project->advisor_code == $userCode) $role = 'อาจารย์ที่ปรึกษา';
                
                return [
                    'project_name' => $project->project_name_th,
                    'exam_datetime' => \Carbon\Carbon::parse($project->exam_datetime)->locale('th')->translatedFormat('d F Y เวลา H:i น.'),
                    'role' => $role
                ];
            })->toArray();
            
            session()->flash('exam_scheduled', $schedules);
        }
        
        // 5. Check for pending grade confirmations
        $pendingGrades = ProjectGrade::whereHas('project', function($q) use ($userCode) {
                $q->where(function($query) use ($userCode) {
                    $query->where('advisor_code', $userCode)
                          ->orWhere('committee1_code', $userCode)
                          ->orWhere('committee2_code', $userCode)
                          ->orWhere('committee3_code', $userCode);
                });
            })
            ->where(function($q) use ($userCode) {
                // Check which role and if not confirmed
                $q->whereHas('project', function($query) use ($userCode) {
                    $query->where('advisor_code', $userCode);
                })->where('advisor_confirmed', false)
                ->orWhere(function($q2) use ($userCode) {
                    $q2->whereHas('project', function($query) use ($userCode) {
                        $query->where('committee1_code', $userCode);
                    })->where('committee1_confirmed', false);
                })
                ->orWhere(function($q3) use ($userCode) {
                    $q3->whereHas('project', function($query) use ($userCode) {
                        $query->where('committee2_code', $userCode);
                    })->where('committee2_confirmed', false);
                })
                ->orWhere(function($q4) use ($userCode) {
                    $q4->whereHas('project', function($query) use ($userCode) {
                        $query->where('committee3_code', $userCode);
                    })->where('committee3_confirmed', false);
                });
            })
            ->where('created_at', '>=', now()->subMinutes(30))
            ->count();
        
        if ($pendingGrades > 0) {
            session()->flash('pending_grade_confirmation', $pendingGrades);
        }

        // สถิติรวม
        $stats = [
            'pending_proposals' => ProjectProposal::where('proposed_to', $username)
                ->where('status', 'pending')
                ->count(),
            'approved_proposals' => ProjectProposal::where('proposed_to', $username)
                ->where('status', 'approved')
                ->count(),
            'my_projects' => Project::where('advisor_code', $userCode)->count(),
            'pending_evaluations' => Project::where(function($q) use ($userCode) {
                    $q->where('advisor_code', $userCode)
                      ->orWhere('committee1_code', $userCode)
                      ->orWhere('committee2_code', $userCode)
                      ->orWhere('committee3_code', $userCode);
                })
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
        $projects = Project::with(['group.members.student', 'advisor', 'group.latestProposal'])
            ->where('advisor_code', $userCode)
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
     * แสดงหน้ายืนยันเกรด
     */
    public function gradeConfirmationIndex()
    {
        $user = Auth::guard('web')->user();
        $userCode = $user->user_code;

        // ดึงโครงงานที่มีเกรดแล้วแต่ยังไม่ยืนยัน
        $projects = Project::with(['group.members.student', 'grade', 'evaluations' => function($q) use ($userCode) {
                $q->where('evaluator_code', $userCode);
            }])
            ->whereHas('grade') // มีเกรดแล้ว
            ->where(function($query) use ($userCode) {
                $query->where(function($q) use ($userCode) {
                    // เป็น advisor และยังไม่ยืนยัน
                    $q->where('advisor_code', $userCode)
                      ->whereHas('grade', function($gq) {
                          $gq->where('advisor_confirmed', false);
                      });
                })
                ->orWhere(function($q) use ($userCode) {
                    // เป็น committee1 และยังไม่ยืนยัน
                    $q->where('committee1_code', $userCode)
                      ->whereHas('grade', function($gq) {
                          $gq->where('committee1_confirmed', false);
                      });
                })
                ->orWhere(function($q) use ($userCode) {
                    // เป็น committee2 และยังไม่ยืนยัน
                    $q->where('committee2_code', $userCode)
                      ->whereHas('grade', function($gq) {
                          $gq->where('committee2_confirmed', false);
                      });
                })
                ->orWhere(function($q) use ($userCode) {
                    // เป็น committee3 และยังไม่ยืนยัน
                    $q->where('committee3_code', $userCode)
                      ->whereHas('grade', function($gq) {
                          $gq->where('committee3_confirmed', false);
                      });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // โครงงานที่ยืนยันแล้ว
        $confirmedProjects = Project::with(['group.members.student', 'grade'])
            ->whereHas('grade')
            ->where(function($query) use ($userCode) {
                $query->where(function($q) use ($userCode) {
                    $q->where('advisor_code', $userCode)
                      ->whereHas('grade', function($gq) {
                          $gq->where('advisor_confirmed', true);
                      });
                })
                ->orWhere(function($q) use ($userCode) {
                    $q->where('committee1_code', $userCode)
                      ->whereHas('grade', function($gq) {
                          $gq->where('committee1_confirmed', true);
                      });
                })
                ->orWhere(function($q) use ($userCode) {
                    $q->where('committee2_code', $userCode)
                      ->whereHas('grade', function($gq) {
                          $gq->where('committee2_confirmed', true);
                      });
                })
                ->orWhere(function($q) use ($userCode) {
                    $q->where('committee3_code', $userCode)
                      ->whereHas('grade', function($gq) {
                          $gq->where('committee3_confirmed', true);
                      });
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('lecturer.grades.confirmation', compact('projects', 'confirmedProjects'));
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
