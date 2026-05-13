<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Project;
use App\Models\ProjectLecturer;
use App\Models\User;
use App\Models\ProjectEvaluation;
use App\Helpers\PermissionHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class CoordinatorController extends Controller
{
    public function dashboard()
    {
        $user = Auth::guard('web')->user();

        $stats = [
            'total_groups'    => Group::count(),
            'pending_groups'  => Group::where('status_group', 'pending')->count(),
            'approved_groups' => Group::where('status_group', 'approved')->count(),
            'total_projects'  => Project::count(),
        ];

        $pendingGroups = Group::with(['members.student', 'project'])
            ->where('status_group', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('coordinator.dashboard', compact('stats', 'pendingGroups'));
    }

    public function groups(Request $request)
    {
        $query = Group::with(['members.student', 'project', 'latestProposal.lecturer']);

        if ($request->status)   $query->where('status_group', $request->status);
        if ($request->subject)  $query->where('subject_code', $request->subject);
        if ($request->semester) $query->where('semester', $request->semester);

        $groups = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('coordinator.groups.index', compact('groups'));
    }

    public function groupShow($id)
    {
        $group = Group::with([
            'members.student',
            'project.advisorLecturer.user',
            'project.committeeLecturers.user',
            'latestProposal.lecturer',
        ])->findOrFail($id);

        $lecturers = User::whereRaw('role & 8192 != 0')
            ->orderBy('firstname_user', 'asc')
            ->get();

        return view('coordinator.groups.show', compact('group', 'lecturers'));
    }

    public function approveGroup(Request $request, $id)
    {
        $user = Auth::guard('web')->user();

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
            $group->update(['status_group' => 'approved']);

            $memberCount  = $group->members->count();
            $advisorCode  = $request->advisor_code;
            $studentType  = $request->student_type;

            $projectCode = sprintf(
                '%02d-%d-%02d_%s-%s%d',
                $group->year % 100,
                $group->semester,
                $group->group_id,
                $advisorCode,
                $studentType,
                $memberCount
            );

            $project = Project::create([
                'group_id'       => $group->group_id,
                'project_name'   => $request->project_name,
                'project_code'   => $projectCode,
                'student_type'   => $studentType,
                'status_project' => 'in_progress',
            ]);

            $project->projectLecturers()->create([
                'user_code'       => $advisorCode,
                'relationship_id' => 1,
                'sort_order'      => 1,
            ]);

            DB::commit();
            return redirect()->route('coordinator.groups.show', $id)
                ->with('success', 'อนุมัติกลุ่มและสร้างโครงงานเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function updateProject(Request $request, $id)
    {
        $user = Auth::guard('web')->user();

        if (!$user->canEdit()) {
            return back()->with('error', 'คุณไม่มีสิทธิ์แก้ไขข้อมูล (Staff read-only)');
        }

        $request->validate([
            'project_name'    => 'nullable|string|max:255',
            'advisor_code'    => 'nullable|string|exists:user,user_code',
            'committee1_code' => 'nullable|string|exists:user,user_code',
            'committee2_code' => 'nullable|string|exists:user,user_code',
            'committee3_code' => 'nullable|string|exists:user,user_code',
            'exam_datetime'   => 'nullable|date',
            'project_type'    => 'nullable|string',
            'status_project'  => 'nullable|string',
        ]);

        $lecturers = array_filter([
            $request->advisor_code,
            $request->committee1_code,
            $request->committee2_code,
            $request->committee3_code,
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
            $group->project->update($request->only([
                'project_name', 'exam_datetime', 'project_type', 'status_project',
            ]));

            $group->project->syncLecturers(
                $request->advisor_code,
                $request->committee1_code,
                $request->committee2_code,
                $request->committee3_code,
            );

            DB::commit();
            return back()->with('success', 'อัพเดทข้อมูลโครงงานเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function projectsReview(Request $request)
    {
        if (!PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin() && !PermissionHelper::isStaff()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $query = Project::with([
            'group.members.student',
            'advisorLecturer.user',
            'committeeLecturers.user',
            'examSchedule',
        ]);

        if ($request->status) {
            $query->where('status_project', $request->status);
        }
        if ($request->year) {
            $query->whereHas('group', fn($q) => $q->where('year', $request->year));
        }
        if ($request->semester) {
            $query->whereHas('group', fn($q) => $q->where('semester', $request->semester));
        }

        $projects  = $query->orderBy('project_code', 'asc')->paginate(20);
        $statuses  = ['pending', 'in_progress', 'submitted', 'late_submission', 'approved'];
        $years     = [2566, 2567, 2568];
        $semesters = [1, 2, 3];

        return view('coordinator.projects.review', compact('projects', 'statuses', 'years', 'semesters'));
    }

    public function updateProjectReview(Request $request, $projectId)
    {
        $request->validate([
            'status_project' => 'required|in:pending,in_progress,submitted,late_submission,approved,rejected',
            'notes'          => 'nullable|string|max:500',
        ]);

        try {
            $project = Project::findOrFail($projectId);

            $user = Auth::guard('web')->user();
            if ($user && $user->isStaff()) {
                DB::table('project_activities')->insertOrIgnore([
                    'project_id' => $projectId,
                    'user_code'  => $user->user_code,
                    'action'     => 'status_changed',
                    'old_value'  => $project->status_project,
                    'new_value'  => $request->status_project,
                    'notes'      => $request->notes,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                \Log::info('Project status updated by staff', [
                    'project_id' => $projectId,
                    'staff_code' => $user->user_code,
                    'old_status' => $project->status_project,
                    'new_status' => $request->status_project,
                ]);
            }

            $project->update(['status_project' => $request->status_project]);

            return back()->with('success', 'อัพเดตสถานะโครงงาน: ' . $project->project_code . ' เรียบร้อยแล้ว');

        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function settings()
    {
        $currentYear     = 2568;
        $currentSemester = 1;
        return view('coordinator.settings', compact('currentYear', 'currentSemester'));
    }

    public function exportCsv(Request $request)
    {
        $query = Project::with([
            'group.members.student',
            'advisorLecturer.user',
            'committeeLecturers.user',
        ]);

        if ($request->status) {
            $query->where('status_project', $request->status);
        }
        if ($request->year) {
            $query->whereHas('group', fn($q) => $q->where('year', $request->year));
        }
        if ($request->semester) {
            $query->whereHas('group', fn($q) => $q->where('semester', $request->semester));
        }

        $projects = $query->orderBy('project_code', 'asc')->get();

        $filename = 'projects_' . date('Y-m-d_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($projects) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'project_id', 'project_code', 'project_name',
                'student1', 'student2',
                'advisor_code', 'committee1_code', 'committee2_code', 'committee3_code',
                'exam_datetime',
            ]);

            foreach ($projects as $index => $project) {
                $members  = $project->group->members ?? collect();
                $member1  = $members->get(0);
                $member2  = $members->get(1);
                $coms     = $project->committeeLecturers;

                fputcsv($file, [
                    $index + 1,
                    $project->project_code ?? '-',
                    $project->project_name ?? '-',
                    $member1 ? "{$member1->student->firstname_std} {$member1->student->lastname_std}" : '-',
                    $member2 ? "{$member2->student->firstname_std} {$member2->student->lastname_std}" : '-',
                    $project->advisor_code ?? '-',
                    $coms->get(0)?->user_code ?? '-',
                    $coms->get(1)?->user_code ?? '-',
                    $coms->get(2)?->user_code ?? '-',
                    $project->exam_datetime ? $project->exam_datetime->format('d.m.y, H:i') : '-',
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    // ──────────────────────────────────────────
    // Schedule & Committee
    // ──────────────────────────────────────────

    public function schedulesIndex(Request $request)
    {
        if (!PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin() && !PermissionHelper::isStaff()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $query = Project::with([
            'group.members.student',
            'advisorLecturer.user',
            'committeeLecturers.user',
        ]);

        if ($request->filled('semester')) {
            $query->whereHas('group', fn($q) => $q->where('semester', $request->semester));
        }
        if ($request->filled('year')) {
            $query->whereHas('group', fn($q) => $q->where('year', $request->year));
        }
        if ($request->filled('course_code')) {
            $query->whereHas('group', fn($q) => $q->where('subject_code', $request->course_code));
        }
        if ($request->filled('has_exam')) {
            $request->has_exam === '1'
                ? $query->whereNotNull('exam_datetime')
                : $query->whereNull('exam_datetime');
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('project_code', 'like', "%{$s}%")
                ->orWhere('project_name', 'like', "%{$s}%")
            );
        }

        // Stats from filtered query (before pagination)
        $statsQuery   = clone $query;
        $totalCount   = $statsQuery->count();
        $withExam     = (clone $query)->whereNotNull('exam_datetime')->count();
        $withoutExam  = $totalCount - $withExam;

        $projects = $query->orderBy('exam_datetime', 'asc')
            ->orderBy('project_id', 'asc')
            ->paginate(20)
            ->appends($request->query());

        return view('coordinator.schedules.index', compact(
            'projects', 'totalCount', 'withExam', 'withoutExam'
        ));
    }

    public function scheduleEdit($projectId)
    {
        if (!PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin() && !PermissionHelper::isStaff()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $project = Project::with([
            'group.members.student',
            'advisorLecturer.user',
            'committeeLecturers.user',
            'coAdvisorInternal.user',
            'coAdvisorExternal.user',
        ])->findOrFail($projectId)->refresh();

        $lecturers = User::whereRaw('(role & ?) != 0', [8192])
            ->orderBy('firstname_user')->get();

        return view('coordinator.schedules.edit', compact('project', 'lecturers'));
    }

    public function scheduleUpdate(Request $request, $projectId)
    {
        if (!PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin() && !PermissionHelper::isStaff()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $request->validate([
            'exam_datetime'    => 'nullable|date',
            'exam_end_time'    => 'nullable|date',
            'advisor_code'     => 'nullable|exists:user,user_code',
            'committee1_code'  => 'nullable|exists:user,user_code',
            'committee2_code'  => 'nullable|exists:user,user_code',
            'committee3_code'  => 'nullable|exists:user,user_code',
            'coadv_int_code'   => 'nullable|exists:user,user_code',
            'coadv_ext_code'   => 'nullable|exists:user,user_code',
        ]);

        $project = Project::with([
            'advisorLecturer', 'committeeLecturers',
            'coAdvisorInternal', 'coAdvisorExternal',
        ])->findOrFail($projectId);

        // Check for exam datetime conflict (same datetime used by another project)
        $conflictWarning = null;
        if ($request->filled('exam_datetime')) {
            $conflictProjects = \App\Models\Project::where('project_id', '!=', $projectId)
                ->where('exam_datetime', $request->exam_datetime)
                ->pluck('project_code')
                ->toArray();
            if (!empty($conflictProjects)) {
                $conflictWarning = 'วันเวลาสอบ ' . \Carbon\Carbon::parse($request->exam_datetime)->format('d/m/Y H:i') .
                    ' ถูกใช้โดยโครงงาน: ' . implode(', ', $conflictProjects) . ' แล้ว';
            }
        }

        $examDateChanged = $project->exam_datetime != $request->exam_datetime && $request->exam_datetime;
        $committeeChanged = (
            $project->advisor_code    != $request->advisor_code    ||
            $project->committee1_code != $request->committee1_code ||
            $project->committee2_code != $request->committee2_code ||
            $project->committee3_code != $request->committee3_code ||
            ($project->coAdvisorInternal?->user_code) != $request->coadv_int_code ||
            ($project->coAdvisorExternal?->user_code) != $request->coadv_ext_code
        );

        $project->update([
            'exam_datetime' => $request->exam_datetime ?: null,
            'exam_end_time' => $request->exam_end_time ?: null,
        ]);

        $project->syncLecturers(
            $request->advisor_code,
            $request->committee1_code,
            $request->committee2_code,
            $request->committee3_code,
        );

        $project->syncCoAdvisors(
            $request->coadv_int_code ?: null,
            $request->coadv_ext_code ?: null,
        );

        $user = Auth::guard('web')->user();
        if ($user && PermissionHelper::isStaff() && ($examDateChanged || $committeeChanged)) {
            \Log::info('Schedule updated by staff', [
                'project_id'       => $projectId,
                'staff_code'       => $user->user_code,
                'exam_date_changed' => $examDateChanged,
                'committee_changed' => $committeeChanged,
            ]);
        }

        if ($examDateChanged) {
            session()->flash('exam_schedule_updated', [
                'project_id'    => $project->project_id,
                'exam_datetime' => $request->exam_datetime,
            ]);
        }

        $redirect = redirect()->route('coordinator.schedules.index')
            ->with('success', 'อัพเดทตารางสอบและคณะกรรมการเรียบร้อยแล้ว');

        if ($conflictWarning) {
            $redirect = $redirect->with('warning', $conflictWarning);
        }

        return $redirect;
    }

    public function scheduleImportForm()
    {
        $lecturers = DB::table('user')
            ->whereRaw('(role & ?) != 0', [8192 | 16384 | 32768])
            ->select('user_code', 'firstname_user', 'lastname_user')
            ->orderBy('user_code')
            ->get();

        return view('coordinator.schedules.import', compact('lecturers'));
    }

    public function scheduleImportTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="schedule_import_template.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['project_code', 'exam_datetime', 'advisor_code', 'committee1_code', 'committee2_code', 'committee3_code']);
            fputcsv($file, ['68-1-01_kdc-r1', '2025-05-20 09:00', 'SCH', 'JDO', 'SMY', '']);
            fputcsv($file, ['68-1-02_abc-r2', '2025-05-20 10:00', 'SCH', 'KWT', 'JDO', '']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function scheduleImportPreview(Request $request)
    {
        $request->validate(['file' => 'required|mimes:csv,txt|max:2048']);

        $path   = $request->file('file')->getRealPath();
        $rows   = array_map(fn($line) => str_getcsv($line), file($path));
        $header = array_shift($rows);
        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        $validCodes = DB::table('user')
            ->whereNotNull('user_code')
            ->pluck('user_code')
            ->map(fn($c) => strtolower(trim($c)))
            ->flip()
            ->toArray();

        $projectMap = Project::with(['group.members.student'])->get()->keyBy('project_code');

        $preview = [];

        foreach ($rows as $i => $row) {
            if (count(array_filter($row)) === 0) continue;

            $data = [];
            foreach ($header as $col => $key) {
                $data[$key] = trim($row[$col] ?? '');
            }

            $projCode = $data['project_code'] ?? '';
            $examDt   = $data['exam_datetime'] ?? '';
            $advisor  = $data['advisor_code'] ?? '';
            $comm1    = $data['committee1_code'] ?? '';
            $comm2    = $data['committee2_code'] ?? '';
            $comm3    = $data['committee3_code'] ?? '';

            $errors  = [];
            $project = $projectMap->get($projCode);

            if (!$project) {
                $errors[] = "ไม่พบโครงงาน '{$projCode}' ในระบบ";
            }

            $parsedDt = null;
            if ($examDt) {
                try {
                    $parsedDt = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $examDt);
                } catch (\Exception $e) {
                    $errors[] = "รูปแบบวันเวลาไม่ถูกต้อง '{$examDt}' (ใช้ YYYY-MM-DD HH:MM)";
                }
            }

            foreach (['advisor_code' => $advisor, 'committee1_code' => $comm1, 'committee2_code' => $comm2, 'committee3_code' => $comm3] as $field => $code) {
                if ($code && !isset($validCodes[strtolower($code)])) {
                    $errors[] = "ไม่พบ user_code '{$code}' ในระบบ";
                }
            }

            $members = $project
                ? $project->group->members->map(fn($m) => trim(($m->student->firstname_std ?? '') . ' ' . ($m->student->lastname_std ?? '')))->join(', ')
                : '-';

            $preview[] = [
                'line'            => $i + 2,
                'project_code'    => $projCode,
                'project_name'    => $project?->project_name ?? '-',
                'members'         => $members,
                'exam_datetime'   => $examDt,
                'advisor_code'    => $advisor,
                'committee1_code' => $comm1,
                'committee2_code' => $comm2,
                'committee3_code' => $comm3,
                'errors'          => $errors,
                'valid'           => empty($errors),
            ];
        }

        session(['schedule_import_preview' => $preview]);

        return view('coordinator.schedules.import', compact('preview'));
    }

    public function scheduleImportConfirm(Request $request)
    {
        $preview = session('schedule_import_preview', []);

        if (empty($preview)) {
            return redirect()->route('coordinator.schedules.import.form')
                ->with('error', 'ไม่พบข้อมูล Preview กรุณาอัปโหลดไฟล์ใหม่');
        }

        $imported = 0;
        $skipped  = 0;

        foreach ($preview as $row) {
            if (!$row['valid']) { $skipped++; continue; }

            $project = Project::where('project_code', $row['project_code'])->first();
            if (!$project) { $skipped++; continue; }

            if ($row['exam_datetime']) {
                $project->update([
                    'exam_datetime' => \Carbon\Carbon::createFromFormat('Y-m-d H:i', $row['exam_datetime']),
                ]);
            }

            $project->syncLecturers(
                $row['advisor_code']    ?: null,
                $row['committee1_code'] ?: null,
                $row['committee2_code'] ?: null,
                $row['committee3_code'] ?: null,
            );

            $imported++;
        }

        session()->forget('schedule_import_preview');

        return redirect()->route('coordinator.schedules.index')
            ->with('success', "Import สำเร็จ {$imported} โครงงาน" . ($skipped ? ", ข้าม {$skipped} แถว (มีข้อผิดพลาด)" : ''));
    }

    // ──────────────────────────────────────────
    // Evaluation & Grading
    // ──────────────────────────────────────────

    public function evaluationsIndex(Request $request)
    {
        $query = Project::with(['group.members.student', 'evaluations.evaluator']);

        if ($request->semester) {
            $query->whereHas('group', fn($q) => $q->where('semester', $request->semester));
        }

        $projects = $query->orderBy('project_id', 'desc')->paginate(20);

        return view('coordinator.evaluations.index', compact('projects'));
    }

    public function viewScores($projectId)
    {
        $project = Project::with([
            'group.members.student',
            'evaluations.evaluator',
            'advisorLecturer.user',
            'committeeLecturers.user',
        ])->findOrFail($projectId);

        return view('coordinator.evaluations.scores', compact('project'));
    }
}
