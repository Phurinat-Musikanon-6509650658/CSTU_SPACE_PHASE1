<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\PermissionHelper;
use App\Models\User;
use App\Models\ExamSchedule;
use App\Models\Project;

class ExamScheduleController extends Controller
{
    public function coordinatorExamScheduleIndex()
    {
        if (!PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin() && !PermissionHelper::isStaff()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $examSchedules = ExamSchedule::with([
            'project.advisorLecturer.user',
            'project.committeeLecturers.user',
            'project.group.members.student',
        ])->orderBy('ex_start_time', 'desc')->paginate(20);

        $lecturers = User::where('role', '&', 8192)->orderBy('firstname_user')->get();

        return view('coordinator.exam-schedules.index', compact('examSchedules', 'lecturers'));
    }

    public function coordinatorExamScheduleCalendar()
    {
        if (!PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin() && !PermissionHelper::isStaff()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $examSchedules = ExamSchedule::with('project')
            ->orderBy('ex_start_time', 'asc')
            ->get();

        $examsByDate = $examSchedules->groupBy(function($schedule) {
            return $schedule->ex_start_time->format('Y-m-d');
        });

        $locations = $examSchedules->pluck('location')->unique()->filter()->values();

        $totalExams    = $examSchedules->count();
        $upcomingExams = $examSchedules->filter(fn($s) => $s->ex_start_time->isFuture())->count();
        $todayExams    = $examSchedules->filter(fn($s) => $s->ex_start_time->isToday())->count();
        $pastExams     = $examSchedules->filter(fn($s) => $s->ex_start_time->isPast() && !$s->ex_start_time->isToday())->count();

        return view('coordinator.exam-schedules.calendar', compact(
            'examsByDate', 'locations', 'totalExams', 'upcomingExams', 'todayExams', 'pastExams'
        ));
    }

    public function coordinatorExamScheduleCreate()
    {
        if (!PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin() && !PermissionHelper::isStaff()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $projects = Project::with('examSchedule')->orderBy('project_id', 'desc')->get();

        return view('coordinator.exam-schedules.create', compact('projects'));
    }

    public function coordinatorExamScheduleStore(Request $request)
    {
        if (!PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin() && !PermissionHelper::isStaff()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $request->validate([
            'project_ids'   => 'required|array|min:1',
            'project_ids.*' => 'required|exists:projects,project_id',
            'ex_start_time' => 'required|date',
            'ex_end_time'   => 'required|date|after:ex_start_time',
            'location'      => 'nullable|string|max:255',
            'notes'         => 'nullable|string',
        ]);

        $createdCount = 0;
        foreach ($request->project_ids as $projectId) {
            if (!ExamSchedule::where('project_id', $projectId)->exists()) {
                ExamSchedule::create([
                    'project_id'    => $projectId,
                    'ex_start_time' => $request->ex_start_time,
                    'ex_end_time'   => $request->ex_end_time,
                    'location'      => $request->location,
                    'notes'         => $request->notes,
                ]);

                DB::table('projects')
                    ->where('project_id', $projectId)
                    ->update(['exam_datetime' => $request->ex_start_time]);

                $createdCount++;
            }
        }

        $message = $createdCount > 0
            ? "สร้างตารางสอบสำเร็จ {$createdCount} โครงงาน"
            : 'ไม่มีการสร้างตารางสอบ (โครงงานที่เลือกมีตารางสอบอยู่แล้ว)';

        return redirect()->route('coordinator.exam-schedules.index')
            ->with($createdCount > 0 ? 'success' : 'warning', $message);
    }

    public function coordinatorExamScheduleEdit($id)
    {
        if (!PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin() && !PermissionHelper::isStaff()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $examSchedule = ExamSchedule::findOrFail($id);
        $projects = Project::orderBy('project_id', 'desc')->get();

        return view('coordinator.exam-schedules.edit', compact('examSchedule', 'projects'));
    }

    public function coordinatorExamScheduleUpdate(Request $request, $id)
    {
        if (!PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin() && !PermissionHelper::isStaff()) {
            return redirect()->route('menu')->with('error', 'Unauthorized access');
        }

        $request->validate([
            'project_id'    => 'required|exists:projects,project_id',
            'ex_start_time' => 'required|date',
            'ex_end_time'   => 'required|date|after:ex_start_time',
            'location'      => 'nullable|string|max:255',
            'notes'         => 'nullable|string',
        ]);

        $examSchedule = ExamSchedule::findOrFail($id);

        if ($examSchedule->project_id != $request->project_id) {
            DB::table('projects')
                ->where('project_id', $examSchedule->project_id)
                ->update(['exam_datetime' => null]);
        }

        $examSchedule->update([
            'project_id'    => $request->project_id,
            'ex_start_time' => $request->ex_start_time,
            'ex_end_time'   => $request->ex_end_time,
            'location'      => $request->location,
            'notes'         => $request->notes,
        ]);

        DB::table('projects')
            ->where('project_id', $request->project_id)
            ->update(['exam_datetime' => $request->ex_start_time]);

        return redirect()->route('coordinator.exam-schedules.index')
            ->with('success', 'อัปเดตตารางสอบสำเร็จ');
    }

    public function coordinatorExamScheduleDestroy($id)
    {
        if (!PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin() && !PermissionHelper::isStaff()) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $examSchedule = ExamSchedule::findOrFail($id);

        DB::table('projects')
            ->where('project_id', $examSchedule->project_id)
            ->update(['exam_datetime' => null]);

        $examSchedule->delete();

        return response()->json(['success' => true, 'message' => 'ลบตารางสอบสำเร็จ']);
    }

    // ──────────────────────────────────────────
    // Staff (view-only)
    // ──────────────────────────────────────────

    public function staffExamSchedules()
    {
        if (!PermissionHelper::isStaff() && !PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $examSchedules = ExamSchedule::with('project')
            ->orderBy('ex_start_time', 'asc')
            ->paginate(20);

        return view('staff.exam-schedules.index', compact('examSchedules'));
    }

    public function staffExamSchedulesCalendar()
    {
        if (!PermissionHelper::isStaff() && !PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $query = ExamSchedule::with('project');

        if (request('status')) {
            $query->whereHas('project', fn($q) => $q->where('status', request('status')));
        }
        if (request('location')) {
            $query->where('location', 'LIKE', '%' . request('location') . '%');
        }
        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('project', fn($sq) => $sq->where('project_name', 'LIKE', "%{$search}%"))
                  ->orWhere('notes', 'LIKE', "%{$search}%");
            });
        }

        $schedulesByDate = $query->orderBy('ex_start_time', 'asc')->get()
            ->groupBy(fn($s) => $s->ex_start_time->format('Y-m-d'));

        return view('staff.exam-schedules.calendar', compact('schedulesByDate'));
    }

    public function staffExamScheduleIndex()
    {
        if (!PermissionHelper::isStaff() && !PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $examSchedules = ExamSchedule::with('project')
            ->orderBy('ex_start_time', 'desc')
            ->paginate(20);

        return view('staff.exam-schedules.manage', compact('examSchedules'));
    }

    public function staffExamScheduleCalendar()
    {
        if (!PermissionHelper::isStaff() && !PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $query = ExamSchedule::with('project');

        if (request('status')) {
            $query->whereHas('project', fn($q) => $q->where('status', request('status')));
        }
        if (request('location')) {
            $query->where('location', 'LIKE', '%' . request('location') . '%');
        }
        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('project', fn($sq) => $sq->where('project_name', 'LIKE', "%{$search}%"))
                  ->orWhere('notes', 'LIKE', "%{$search}%");
            });
        }

        $schedulesByDate = $query->orderBy('ex_start_time', 'asc')->get()
            ->groupBy(fn($s) => $s->ex_start_time->format('Y-m-d'));

        return view('coordinator.exam-schedules.calendar', compact('schedulesByDate'));
    }

    public function staffExamScheduleCreate()
    {
        if (!PermissionHelper::isStaff() && !PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $projects = Project::whereHas('advisorLecturer')
            ->whereIn('status_project', ['in_progress', 'late_submission', 'submitted'])
            ->orderBy('project_id', 'desc')
            ->get();

        return view('coordinator.exam-schedules.create', compact('projects'));
    }

    public function staffExamScheduleStore(Request $request)
    {
        if (!PermissionHelper::isStaff() && !PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $request->validate([
            'project_id'    => 'required|exists:projects,project_id',
            'ex_start_time' => 'required|date',
            'ex_end_time'   => 'required|date|after:ex_start_time',
            'location'      => 'required|string|max:255',
            'notes'         => 'nullable|string|max:1000',
        ]);

        ExamSchedule::create($request->only(['project_id', 'ex_start_time', 'ex_end_time', 'location', 'notes']));

        return redirect()->route('staff.exam-schedules.index')
            ->with('success', 'สร้างตารางสอบสำเร็จ');
    }

    public function staffExamScheduleEdit($id)
    {
        if (!PermissionHelper::isStaff() && !PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $examSchedule = ExamSchedule::with('project')->findOrFail($id);

        $projects = Project::whereHas('advisorLecturer')
            ->whereIn('status_project', ['in_progress', 'late_submission', 'submitted'])
            ->orderBy('project_id', 'desc')
            ->get();

        return view('coordinator.exam-schedules.edit', compact('examSchedule', 'projects'));
    }

    public function staffExamScheduleUpdate(Request $request, $id)
    {
        if (!PermissionHelper::isStaff() && !PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $request->validate([
            'project_id'    => 'required|exists:projects,project_id',
            'ex_start_time' => 'required|date',
            'ex_end_time'   => 'required|date|after:ex_start_time',
            'location'      => 'required|string|max:255',
            'notes'         => 'nullable|string|max:1000',
        ]);

        ExamSchedule::findOrFail($id)->update(
            $request->only(['project_id', 'ex_start_time', 'ex_end_time', 'location', 'notes'])
        );

        return redirect()->route('staff.exam-schedules.index')
            ->with('success', 'อัพเดทตารางสอบสำเร็จ');
    }

    public function staffExamScheduleDestroy($id)
    {
        if (!PermissionHelper::isStaff() && !PermissionHelper::isCoordinator() && !PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        ExamSchedule::findOrFail($id)->delete();

        return redirect()->route('staff.exam-schedules.index')
            ->with('success', 'ลบตารางสอบสำเร็จ');
    }
}
