<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class SubjectController extends Controller
{
    public function index()
    {
        $all = Subject::orderBy('year', 'desc')
            ->orderBy('semester', 'desc')
            ->orderBy('subject_code', 'asc')
            ->get();

        // Precompute group counts: key = "subject_code-year-semester"
        $gcRaw = DB::table('groups')
            ->select('subject_code', 'year', 'semester', DB::raw('count(*) as cnt'))
            ->groupBy('subject_code', 'year', 'semester')
            ->get()
            ->mapWithKeys(fn($r) => ["{$r->subject_code}-{$r->year}-{$r->semester}" => (int)$r->cnt]);

        // Group by year-semester, newest first
        $groups = $all->groupBy(fn($s) => $s->year . '-' . $s->semester)
            ->map(function ($subs, $key) use ($gcRaw) {
                [$year, $semester] = explode('-', $key);

                // Active = at least one subject has close_date in the future or null
                $isActive = $subs->filter(fn($s) =>
                    !$s->close_date || $s->close_date->isFuture()
                )->isNotEmpty();

                // Group counts per subject
                $subjectCounts = $subs->mapWithKeys(fn($s) => [
                    $s->subject_code => $gcRaw->get("{$s->subject_code}-{$s->year}-{$s->semester}", 0)
                ]);

                return [
                    'year'          => (int) $year,
                    'semester'      => (int) $semester,
                    'subjects'      => $subs,
                    'is_active'     => $isActive,
                    'subject_counts'=> $subjectCounts,
                    'total_groups'  => $subjectCounts->sum(),
                ];
            })
            ->values();

        return view('admin.subjects.index', compact('groups'));
    }

    public function create()
    {
        $year     = (int) request('year',     intval(date('Y')) + 543);
        $semester = (int) request('semester', 1);
        return view('admin.subjects.create', compact('year', 'semester'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_code' => [
                'required', 'string', 'max:50',
                Rule::unique('subjects')->where(fn($q) => $q
                    ->where('year', $request->year)
                    ->where('semester', $request->semester)
                ),
            ],
            'subject_name'          => 'required|string|max:255',
            'description'           => 'nullable|string',
            'semester'              => 'required|integer|in:1,2',
            'year'                  => 'required|integer|min:2560|max:2650',
            'is_enabled'            => 'boolean',
            'open_date'             => 'nullable|date',
            'close_date'            => 'nullable|date|after:open_date',
            'access_open_date'      => 'nullable|date',
            'access_close_date'     => 'nullable|date|after:access_open_date',
            'evaluation_open_date'  => 'nullable|date',
            'evaluation_close_date' => 'nullable|date|after:evaluation_open_date',
            'grade_edit_open_date'  => 'nullable|date',
            'grade_edit_close_date' => 'nullable|date|after:grade_edit_open_date',
        ], [
            'subject_code.unique' => 'รหัสวิชานี้มีอยู่แล้วในปีการศึกษาและเทอมนี้',
        ]);

        $validated['is_enabled'] = $request->boolean('is_enabled');
        Subject::create($validated);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'สร้างรายวิชา ' . $request->subject_name . ' ปี ' . $request->year . '/' . $request->semester . ' สำเร็จ');
    }

    public function edit(Subject $subject)
    {
        return view('admin.subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'subject_code' => [
                'required', 'string', 'max:50',
                Rule::unique('subjects')
                    ->where(fn($q) => $q
                        ->where('year', $request->year)
                        ->where('semester', $request->semester)
                    )
                    ->ignore($subject->subject_id, 'subject_id'),
            ],
            'subject_name'          => 'required|string|max:255',
            'description'           => 'nullable|string',
            'semester'              => 'required|integer|in:1,2',
            'year'                  => 'required|integer|min:2560|max:2650',
            'is_enabled'            => 'boolean',
            'open_date'             => 'nullable|date',
            'close_date'            => 'nullable|date|after:open_date',
            'evaluation_open_date'  => 'nullable|date',
            'evaluation_close_date' => 'nullable|date|after:evaluation_open_date',
            'grade_edit_open_date'  => 'nullable|date',
            'grade_edit_close_date' => 'nullable|date|after:grade_edit_open_date',
        ], [
            'subject_code.unique' => 'รหัสวิชานี้มีอยู่แล้วในปีการศึกษาและเทอมนี้',
        ]);

        $validated['is_enabled'] = $request->boolean('is_enabled');
        $subject->update($validated);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'อัพเดตรายวิชา ' . $subject->subject_name . ' สำเร็จ');
    }

    /** อัปเดตช่วงเวลาทุกวิชาในเทอมพร้อมกัน */
    public function bulkUpdateTerm(Request $request)
    {
        $request->validate([
            'year'                  => 'required|integer',
            'semester'              => 'required|integer|in:1,2',
            'open_date'             => 'nullable|date',
            'close_date'            => 'nullable|date',
            'evaluation_open_date'  => 'nullable|date',
            'evaluation_close_date' => 'nullable|date',
            'grade_edit_open_date'  => 'nullable|date',
            'grade_edit_close_date' => 'nullable|date',
        ]);

        $fields = collect([
            'open_date', 'close_date',
            'evaluation_open_date', 'evaluation_close_date',
            'grade_edit_open_date', 'grade_edit_close_date',
        ])->mapWithKeys(fn($f) => [$f => $request->filled($f) ? Carbon::parse($request->$f) : null])->all();

        $count = Subject::where('year', $request->year)
            ->where('semester', $request->semester)
            ->update($fields);

        return redirect()->route('admin.subjects.index')
            ->with('success', "อัปเดตช่วงเวลาเทอม {$request->year}/{$request->semester} สำเร็จ ({$count} รายวิชา)");
    }

    /** เปิดเทอมใหม่: duplicate CS303+CS403 จากเทอมล่าสุด พร้อม open/close date ใหม่ */
    public function openNewTerm(Request $request)
    {
        $request->validate([
            'year'       => 'required|integer|min:2560|max:2650',
            'semester'   => 'required|integer|in:1,2',
            'open_date'  => 'required|date',
            'close_date' => 'required|date|after:open_date',
        ]);

        $year     = (int) $request->year;
        $semester = (int) $request->semester;

        // Check not already exists
        $existing = Subject::where('year', $year)->where('semester', $semester)->count();
        if ($existing > 0) {
            return back()->with('error', "ปีการศึกษา {$year} เทอม {$semester} มีรายวิชาอยู่แล้ว");
        }

        // Get latest subjects as template (newest year+semester)
        $template = Subject::orderBy('year', 'desc')->orderBy('semester', 'desc')->get()
            ->groupBy(fn($s) => $s->year . '-' . $s->semester)
            ->first(); // first = newest group

        if (!$template) {
            return back()->with('error', 'ไม่พบรายวิชาต้นแบบ');
        }

        $created = 0;
        foreach ($template as $src) {
            Subject::create([
                'subject_code' => $src->subject_code,
                'subject_name' => $src->subject_name,
                'description'  => $src->description,
                'semester'     => $semester,
                'year'         => $year,
                'is_enabled'   => true,
                'open_date'    => $request->open_date,
                'close_date'   => $request->close_date,
            ]);
            $created++;
        }

        return redirect()->route('admin.subjects.index')
            ->with('success', "เปิดเทอมใหม่ ปีการศึกษา {$year} เทอม {$semester} สำเร็จ ({$created} รายวิชา) — กรุณาตั้งค่าช่วงเวลาเพิ่มเติมในแต่ละวิชา");
    }

    public function toggle(Subject $subject)
    {
        $subject->update(['is_enabled' => !$subject->is_enabled]);
        $status = $subject->is_enabled ? 'เปิด' : 'ปิด';

        return response()->json([
            'success'    => true,
            'is_enabled' => $subject->is_enabled,
            'message'    => $status . 'ใช้งานรายวิชา ' . $subject->subject_name . ' สำเร็จ',
        ]);
    }

    public function destroy(Subject $subject)
    {
        $name = $subject->subject_name;
        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', 'ลบรายวิชา ' . $name . ' สำเร็จ');
    }
}
