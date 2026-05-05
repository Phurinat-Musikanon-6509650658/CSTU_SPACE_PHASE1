<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectEvaluation;
use App\Models\ProjectGrade;
use App\Models\StudentGrade;
use App\Models\ProjectProposal;
use App\Models\GroupMember;
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
    public function evaluateForm(Request $request, $projectId)
    {
        $user = Auth::guard('web')->user();
        $userCode = $user->user_code;

        $project = Project::with(['group.members.student', 'advisor', 'committee1', 'committee2', 'committee3', 'evaluations'])
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

        // ดึงการประเมินครั้งแรก (ถ้ามี) เพื่อแสดงสถานะว่าเคยประเมินหรือยัง
        $evaluation = $project->evaluations
            ->where('evaluator_code', $userCode)
            ->where('evaluator_role', $role)
            ->first();

        return view('lecturer.evaluations.form', compact('project', 'role', 'evaluation'));
    }

    /**
     * บันทึกคะแนน (สำหรับ 2 นักศึกษา)
     */
    public function submitEvaluation(Request $request, $projectId)
    {
        $user = Auth::guard('web')->user();
        $userCode = $user->user_code;
        $project = Project::with('group.members.student')->findOrFail($projectId);

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

        // คำนวณเกรดถ้าคนครบแล้ว
        $this->calculateGradeIfReady($project);

        return redirect()->route('lecturer.evaluations.index')
            ->with('success', "บันทึกคะแนนเรียบร้อยแล้ว ({$successCount} นักศึกษา)");
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
     * คำนวณเกรดต่อนักศึกษา ถ้ามีคะแนนครบ
     */
    private function calculateGradeIfReady(Project $project)
    {
        // นับจำนวนที่ต้องให้คะแนน
        $requiredRoles = ['advisor'];
        if ($project->committee1_code) $requiredRoles[] = 'committee1';
        if ($project->committee2_code) $requiredRoles[] = 'committee2';
        if ($project->committee3_code) $requiredRoles[] = 'committee3';

        // ได้นักศึกษาทั้งหมดในโครงงาน ผ่าน relationship
        $groupMembers = GroupMember::where('group_id', $project->group_id)
            ->with('student')
            ->get();

        // สำหรับแต่ละนักศึกษา
        foreach ($groupMembers as $member) {
            $studentId = $member->student->student_id;
            
            // ตรวจสอบว่าคนที่ต้องส่งครบแล้วหรือสำหรับนักศึกษาคนนี้
            $submittedRoles = ProjectEvaluation::where('project_id', $project->project_id)
                ->where('student_id', $studentId)
                ->whereNotNull('submitted_at')
                ->pluck('evaluator_role')
                ->toArray();

            // ถ้าครบแล้ว ให้คำนวณเกรด
            if (count(array_intersect($requiredRoles, $submittedRoles)) === count($requiredRoles)) {
                $studentGrade = StudentGrade::firstOrCreate([
                    'project_id' => $project->project_id,
                    'student_id' => $studentId
                ]);

                // คำนวณ final score
                // Part 1: จาก Advisor เท่านั้น (0-10)
                $advisorEval = ProjectEvaluation::where('project_id', $project->project_id)
                    ->where('student_id', $studentId)
                    ->where('evaluator_role', 'advisor')
                    ->first();

                $part1 = $advisorEval ? ($advisorEval->part1_score ?? 0) : 0;

                // Part 2+3 Average: จากทุก evaluator (Advisor + Committee 1,2,3)
                $allEvals = ProjectEvaluation::where('project_id', $project->project_id)
                    ->where('student_id', $studentId)
                    ->whereIn('evaluator_role', ['advisor', 'committee1', 'committee2', 'committee3'])
                    ->get();

                $part2Avg = $allEvals->count() > 0 ? $allEvals->avg('part2_score') : 0;
                $part3Avg = $allEvals->count() > 0 ? $allEvals->avg('part3_score') : 0;

                // Final Score = Part 1 (Advisor only) + Average(Part 2) + Average(Part 3)
                $finalScore = $part1 + $part2Avg + $part3Avg;
                $gradeTitle = StudentGrade::calculateGrade($finalScore);

                // อัพเดตเกรด
                $studentGrade->update([
                    'final_score' => $finalScore,
                    'grade' => $gradeTitle
                ]);
            }
        }
    }
}
