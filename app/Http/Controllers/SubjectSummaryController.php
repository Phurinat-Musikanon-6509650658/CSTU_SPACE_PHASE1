<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Group;
use App\Models\Project;
use App\Models\Student;
use App\Models\User;
use App\Helpers\SimpleXlsx;
use App\Helpers\XlsxParser;

class SubjectSummaryController extends Controller
{
    // ──────────────────────────────────────────────────────────
    // Main view
    // ──────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $year     = (int) ($request->year     ?? 2568);
        $semester = (int) ($request->semester ?? 2);

        $cs303 = $this->getProjects($year, $semester, 'CS303');
        $cs403 = $this->getProjects($year, $semester, 'CS403');

        $years     = DB::table('groups')->distinct()->orderBy('year', 'desc')->pluck('year')->toArray();
        $semesters = [1, 2];

        return view('coordinator.subject-summary.index',
            compact('cs303', 'cs403', 'year', 'semester', 'years', 'semesters'));
    }

    // ──────────────────────────────────────────────────────────
    // Export (csv / xlsx / pdf-view)
    // ──────────────────────────────────────────────────────────

    public function export(Request $request)
    {
        $format   = $request->format   ?? 'csv';
        $year     = (int) ($request->year     ?? 2568);
        $semester = (int) ($request->semester ?? 2);
        $subject  = $request->subject ?? 'all';

        if ($subject === 'all') {
            $projects = $this->getProjects($year, $semester, 'CS303')
                ->concat($this->getProjects($year, $semester, 'CS403'));
        } else {
            $projects = $this->getProjects($year, $semester, $subject);
        }

        $label = $subject === 'all' ? 'ALL' : $subject;
        $fname = "subject_summary_{$label}_{$year}-{$semester}_" . date('Ymd');

        return match ($format) {
            'xlsx' => $this->buildXlsx($projects, $year, $semester, $subject, $fname),
            'pdf'  => $this->buildPdfView($projects, $year, $semester, $subject),
            default => $this->buildCsv($projects, $fname),
        };
    }

    // ──────────────────────────────────────────────────────────
    // Template download
    // ──────────────────────────────────────────────────────────

    public function template()
    {
        $path = public_path('templates/project_import_template.xlsx');
        if (!file_exists($path)) {
            abort(404, 'ไม่พบไฟล์ template');
        }
        return response()->download($path, 'project_import_template.xlsx');
    }

    // ──────────────────────────────────────────────────────────
    // Import – form
    // ──────────────────────────────────────────────────────────

    public function importForm()
    {
        return view('coordinator.subject-summary.import');
    }

    // ──────────────────────────────────────────────────────────
    // Import – preview
    // ──────────────────────────────────────────────────────────

    public function importPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ], [
            'file.required' => 'กรุณาเลือกไฟล์ก่อน',
            'file.mimes'    => 'รองรับเฉพาะไฟล์ .xlsx / .xls เท่านั้น',
            'file.max'      => 'ขนาดไฟล์ต้องไม่เกิน 10 MB',
        ]);

        try {
            // Template has 5 header rows (rows 1-5), data starts at row 6
            $rawRows = XlsxParser::parse($request->file('file'), skipRows: 5);
        } catch (\Exception $e) {
            return back()->with('error', 'ไม่สามารถอ่านไฟล์: ' . $e->getMessage());
        }

        // Valid user codes from DB
        $validCodes = User::whereRaw('role & 8192 != 0')->pluck('user_code')->toArray();

        $preview      = [];
        $seenCodes    = [];
        $seenStudents = [];

        foreach ($rawRows as $i => $row) {
            if ($this->isBlankRow($row)) continue;

            $item        = $this->mapRow($row, $i + 6);
            $errors      = $this->validateRow($item, $validCodes);
            $isDuplicate = $this->checkDbDuplicate($item);
            $inFileConflict = in_array($item['project_code'], $seenCodes)
                           || ($item['m1_id'] && in_array($item['m1_id'], $seenStudents));

            if ($inFileConflict) {
                $errors[] = 'ข้อมูลซ้ำกับแถวอื่นในไฟล์เดียวกัน';
            }

            $item['status']      = empty($errors) ? ($isDuplicate ? 'duplicate' : 'ok') : 'error';
            $item['errors']      = $errors;
            $item['is_db_dup']   = $isDuplicate;
            $preview[]           = $item;

            if (empty($errors)) {
                if ($item['project_code']) $seenCodes[]    = $item['project_code'];
                if ($item['m1_id'])        $seenStudents[] = $item['m1_id'];
                if ($item['m2_id'])        $seenStudents[] = $item['m2_id'];
            }
        }

        $ok  = collect($preview)->where('status', 'ok')->count();
        $dup = collect($preview)->where('status', 'duplicate')->count();
        $err = collect($preview)->where('status', 'error')->count();

        session(['import_preview' => $preview]);

        return view('coordinator.subject-summary.import',
            compact('preview', 'ok', 'dup', 'err'));
    }

    // ──────────────────────────────────────────────────────────
    // Import – confirm
    // ──────────────────────────────────────────────────────────

    public function importConfirm(Request $request)
    {
        $preview = session('import_preview', []);
        if (empty($preview)) {
            return redirect()->route('coordinator.subject-summary.import.form')
                ->with('error', 'ไม่พบข้อมูล preview กรุณาอัปโหลดใหม่');
        }

        $toProcess = array_filter($preview,
            fn($r) => in_array($r['status'], ['ok', 'duplicate']));

        $created = 0;
        $updated = 0;

        DB::beginTransaction();
        try {
            foreach ($toProcess as $item) {
                if ($item['status'] === 'duplicate') {
                    $this->updateFromImport($item);
                    $updated++;
                } else {
                    $this->createFromImport($item);
                    $created++;
                }
            }
            DB::commit();
            session()->forget('import_preview');

            return redirect()->route('coordinator.subject-summary.index')
                ->with('success', "นำเข้าสำเร็จ: สร้างใหม่ {$created} รายการ, อัปเดต {$updated} รายการ");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────
    // Private helpers – data query
    // ──────────────────────────────────────────────────────────

    private function getProjects(int $year, int $semester, string $subject)
    {
        return Project::with([
            'group',
            'group.members.student',
            'advisorLecturer.user',
            'committeeLecturers.user',
            'coAdvisors.user',
        ])
        ->whereHas('group', fn($q) =>
            $q->where('year', $year)
              ->where('semester', $semester)
              ->where('subject_code', $subject))
        ->orderBy('exam_datetime', 'asc')
        ->orderBy('project_code', 'asc')
        ->get();
    }

    // ──────────────────────────────────────────────────────────
    // Private helpers – export
    // ──────────────────────────────────────────────────────────

    private function buildCsv($projects, string $fname)
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fname}.csv\"",
        ];

        $callback = function () use ($projects) {
            $f = fopen('php://output', 'w');
            fprintf($f, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM

            fputcsv($f, [
                'No.', 'รหัสโครงงาน', 'รหัสวิชา', 'ชื่อโครงงาน', 'ประเภท',
                'นักศึกษา 1', 'รหัส 1', 'นักศึกษา 2', 'รหัส 2',
                'ที่ปรึกษา', 'กรรมการ 1', 'กรรมการ 2', 'กรรมการ 3',
                'วันสอบ', 'เวลาเริ่ม', 'เวลาจบ', 'ห้อง', 'สถานะ',
            ]);

            foreach ($projects as $no => $p) {
                $m1  = $p->first_member;
                $m2  = $p->second_member;
                $edt = $p->exam_datetime;

                fputcsv($f, [
                    $no + 1,
                    $p->project_code,
                    $p->group->subject_code ?? '',
                    $p->project_name,
                    $p->project_type,
                    $m1 ? $m1->firstname_std . ' ' . $m1->lastname_std : '',
                    $m1?->username_std ?? '',
                    $m2 ? $m2->firstname_std . ' ' . $m2->lastname_std : '',
                    $m2?->username_std ?? '',
                    $p->advisorLecturer?->user_code ?? '',
                    $p->committeeLecturers->get(0)?->user_code ?? '',
                    $p->committeeLecturers->get(1)?->user_code ?? '',
                    $p->committeeLecturers->get(2)?->user_code ?? '',
                    $edt?->format('Y-m-d') ?? '',
                    $edt?->format('H:i')   ?? '',
                    $p->exam_end_time?->format('H:i') ?? '',
                    '',
                    $p->status_project,
                ]);
            }
            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buildXlsx($projects, int $year, int $semester, string $subject, string $fname)
    {
        $xlsx = new SimpleXlsx();
        $xlsx->setTitle('สรุปรายวิชา');

        // Column widths: No, Code, Subject, Name, Type, M1Name, M1ID, M2Name, M2ID,
        //                Adv, C1, C2, C3, Date, Start, End, Room, Status
        $widths = [5, 22, 10, 40, 8, 26, 14, 26, 14, 8, 8, 8, 8, 12, 8, 8, 14, 14];
        foreach ($widths as $i => $w) {
            $xlsx->setColWidth($i, $w);
        }

        $cols = count($widths);
        $lastCol = chr(64 + $cols); // 'R' for 18 cols

        // Row 1 – title (style 2 = navy)
        $subjectLabel = $subject === 'all' ? 'ทุกวิชา' : $subject;
        $titleRow = array_fill(0, $cols, null);
        $titleRow[0] = "สรุปข้อมูลโครงงาน {$subjectLabel}  |  เทอม {$semester}  ปีการศึกษา {$year}";
        $xlsx->addMerge("A1:{$lastCol}1");
        $xlsx->addRow($titleRow, 2, 22);

        // Row 2 – headers (style 1 = blue)
        $xlsx->addRow([
            'No.', 'รหัสโครงงาน', 'รหัสวิชา', 'ชื่อโครงงาน', 'ประเภท',
            'ชื่อ นศ.1', 'รหัส นศ.1', 'ชื่อ นศ.2', 'รหัส นศ.2',
            'ทปษ.', 'กก.1', 'กก.2', 'กก.3',
            'วันสอบ', 'เริ่ม', 'จบ', 'ห้อง', 'สถานะ',
        ], 1, 18);

        // Data rows
        foreach ($projects as $no => $p) {
            $m1  = $p->first_member;
            $m2  = $p->second_member;
            $edt = $p->exam_datetime;
            $style = ($no % 2 === 0) ? 3 : 4;

            $xlsx->addRow([
                $no + 1,
                $p->project_code,
                $p->group->subject_code ?? '',
                $p->project_name,
                $p->project_type,
                $m1 ? $m1->firstname_std . ' ' . $m1->lastname_std : '',
                $m1?->username_std ?? '',
                $m2 ? $m2->firstname_std . ' ' . $m2->lastname_std : '',
                $m2?->username_std ?? '',
                $p->advisorLecturer?->user_code ?? '',
                $p->committeeLecturers->get(0)?->user_code ?? '',
                $p->committeeLecturers->get(1)?->user_code ?? '',
                $p->committeeLecturers->get(2)?->user_code ?? '',
                $edt?->format('Y-m-d') ?? '',
                $edt?->format('H:i')   ?? '',
                $p->exam_end_time?->format('H:i') ?? '',
                '',
                $p->status_project,
            ], $style, 15);
        }

        $binary = $xlsx->toBinary();

        return response($binary, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fname}.xlsx\"",
            'Content-Length'      => strlen($binary),
        ]);
    }

    private function buildPdfView($projects, int $year, int $semester, string $subject)
    {
        return view('coordinator.subject-summary.print',
            compact('projects', 'year', 'semester', 'subject'));
    }

    // ──────────────────────────────────────────────────────────
    // Private helpers – import parsing & validation
    // ──────────────────────────────────────────────────────────

    /** Map a raw xlsx row (col 0-based) to a named array */
    private function mapRow(array $row, int $lineNum): array
    {
        // Template columns (0-based):
        // 0=No(auto) 1=ProjCode 2=AdvIdAbbr 3=ProjType 4=SubjectCode 5=NameTH 6=NameEN
        // 7=M1Prefix 8=M1Name 9=M1ID 10=M1Email 11=M1Phone 12=M1Type
        // 13=M2Prefix 14=M2Name 15=M2ID 16=M2Email 17=M2Phone 18=M2Type
        // 19=MemberCount(auto) 20=AdvName(auto) 21=CoAdvInternal(auto) 22=CoAdvExternal
        // 23=AdvId 24=Comm1 25=Comm2 26=Comm3
        // 27=ExamDate 28=ExamStart 29=ExamEnd 30=ExamRoom

        $get = fn(int $i) => trim($row[$i] ?? '');

        // Split member name: "ณธรกริช ทองธรรมชาติ" → firstname + lastname
        $splitName = function (string $full): array {
            $parts = preg_split('/\s+/u', trim($full), 2);
            return [$parts[0] ?? '', $parts[1] ?? ''];
        };

        [$m1First, $m1Last] = $splitName($get(8));
        [$m2First, $m2Last] = $splitName($get(14));

        return [
            'line'            => $lineNum,
            'project_code'    => $get(1),
            'project_type'    => strtolower($get(3)),
            'subject_code'    => strtoupper($get(4)),
            'project_name'    => $get(5),
            'project_name_en' => $get(6),
            'm1_prefix'       => $get(7),
            'm1_firstname'    => $m1First,
            'm1_lastname'     => $m1Last,
            'm1_id'           => $get(9),
            'm1_email'        => $get(10),
            'm1_phone'        => $get(11),
            'm1_type'         => strtolower($get(12)),
            'm2_prefix'       => $get(13),
            'm2_firstname'    => $m2First,
            'm2_lastname'     => $m2Last,
            'm2_id'           => $get(15),
            'm2_email'        => $get(16),
            'm2_phone'        => $get(17),
            'm2_type'         => strtolower($get(18)),
            'co_adv_external' => $get(22),
            'adv_code'        => $get(23),
            'comm1_code'      => $get(24),
            'comm2_code'      => $get(25),
            'comm3_code'      => $get(26),
            'exam_date'       => $get(27),
            'exam_start'      => $get(28),
            'exam_end'        => $get(29),
            'exam_room'       => $get(30),
        ];
    }

    private function isBlankRow(array $row): bool
    {
        // A row is blank if project_name (col5), m1_id (col9), and adv_code (col23) are all empty
        return trim($row[5] ?? '') === ''
            && trim($row[9] ?? '') === ''
            && trim($row[23] ?? '') === '';
    }

    private function validateRow(array $item, array $validCodes): array
    {
        $errors = [];

        if (empty($item['subject_code']))
            $errors[] = 'รหัสวิชา (E) ว่างอยู่';
        elseif (!in_array($item['subject_code'], ['CS303', 'CS403']))
            $errors[] = "รหัสวิชา '{$item['subject_code']}' ไม่ถูกต้อง";

        if (empty($item['project_name']))
            $errors[] = 'ชื่อโครงงาน (F) ว่างอยู่';

        if (empty($item['project_type']))
            $errors[] = 'ประเภทโครงงาน (D) ว่างอยู่';
        elseif (!in_array($item['project_type'], ['s', 'r', 'm']))
            $errors[] = "ประเภทโครงงาน '{$item['project_type']}' ต้องเป็น s/r/m";

        if (empty($item['m1_id']))
            $errors[] = 'รหัสนักศึกษา 1 (J) ว่างอยู่';

        if (empty($item['m1_firstname']))
            $errors[] = 'ชื่อนักศึกษา 1 (I) ว่างอยู่';

        if (!empty($item['m1_type']) && !in_array($item['m1_type'], ['s', 'r']))
            $errors[] = "ประเภทนักศึกษา 1 (M) '{$item['m1_type']}' ต้องเป็น s หรือ r";

        if (empty($item['adv_code']))
            $errors[] = 'รหัสที่ปรึกษา (X) ว่างอยู่';
        elseif (!in_array($item['adv_code'], $validCodes))
            $errors[] = "รหัสที่ปรึกษา '{$item['adv_code']}' ไม่พบในระบบ";

        foreach (['comm1_code' => 'Y', 'comm2_code' => 'Z', 'comm3_code' => 'AA'] as $field => $col) {
            if (!empty($item[$field]) && !in_array($item[$field], $validCodes))
                $errors[] = "รหัสกรรมการ ({$col}) '{$item[$field]}' ไม่พบในระบบ";
        }

        if (!empty($item['exam_date']) && !strtotime($item['exam_date']))
            $errors[] = "วันสอบ '{$item['exam_date']}' รูปแบบไม่ถูกต้อง (YYYY-MM-DD)";

        return $errors;
    }

    private function checkDbDuplicate(array $item): bool
    {
        if (!empty($item['project_code'])) {
            return Project::where('project_code', $item['project_code'])->exists();
        }
        // Fallback: check by project_name + subject + same year/semester
        return Project::where('project_name', $item['project_name'])
            ->whereHas('group', fn($q) => $q->where('subject_code', $item['subject_code']))
            ->exists();
    }

    // ──────────────────────────────────────────────────────────
    // Private helpers – import DB write
    // ──────────────────────────────────────────────────────────

    private function ensureStudent(array $item, int $memberNum): ?string
    {
        $id    = $memberNum === 1 ? $item['m1_id']     : $item['m2_id'];
        $first = $memberNum === 1 ? $item['m1_firstname'] : $item['m2_firstname'];
        $last  = $memberNum === 1 ? $item['m1_lastname']  : $item['m2_lastname'];
        $email = $memberNum === 1 ? $item['m1_email']     : $item['m2_email'];
        $type  = $memberNum === 1 ? $item['m1_type']      : $item['m2_type'];

        if (empty($id)) return null;

        $exists = Student::where('username_std', $id)->first();
        if ($exists) return $id;

        // Auto-create student record
        Student::create([
            'username_std'  => $id,
            'firstname_std' => $first ?: $id,
            'lastname_std'  => $last  ?: '-',
            'email_std'     => $email ?: ($id . '@student.cstu.ac.th'),
            'password_std'  => Hash::make($id),
            'role'          => 2048,
            'course_code'   => $item['subject_code'],
            'student_type'  => $type ?: 's',
            'semester'      => 2,
            'year'          => 2568,
        ]);

        return $id;
    }

    private function createFromImport(array $item): void
    {
        // 1) Ensure students exist
        $m1Id = $this->ensureStudent($item, 1);
        $m2Id = $this->ensureStudent($item, 2);

        // 2) Create group
        $group = Group::create([
            'year'         => 2568,
            'semester'     => 2,
            'subject_code' => $item['subject_code'],
            'status_group' => 'approved',
        ]);

        // 3) Group members
        $memberRows = array_values(array_filter([
            $m1Id ? ['group_id' => $group->group_id, 'username_std' => $m1Id, 'created_at' => now(), 'updated_at' => now()] : null,
            $m2Id ? ['group_id' => $group->group_id, 'username_std' => $m2Id, 'created_at' => now(), 'updated_at' => now()] : null,
        ]));
        if (!empty($memberRows)) {
            DB::table('group_members')->insert($memberRows);
        }

        // 4) Generate project code if not provided
        $memberCount = $m2Id ? 2 : 1;
        $code = $item['project_code'] ?: sprintf(
            '%02d-%d-%02d_%s-%s%d',
            68, 2, $group->group_id,
            $item['adv_code'],
            $item['m1_type'] ?: 's',
            $memberCount
        );

        // 5) Create project
        $project = Project::create([
            'group_id'       => $group->group_id,
            'project_name'   => $item['project_name'],
            'project_code'   => $code,
            'student_type'   => $item['project_type'],
            'status_project' => 'in_progress',
            'project_type'   => $item['project_type'],
        ]);

        // 6) Lecturers
        $this->syncProjectLecturers($project, $item);

        // 7) Exam schedule
        $this->syncExamSchedule($project, $item);
    }

    private function updateFromImport(array $item): void
    {
        $project = Project::where('project_code', $item['project_code'])->first();
        if (!$project) return;

        $project->update([
            'project_name' => $item['project_name'] ?: $project->project_name,
            'project_type' => $item['project_type'] ?: $project->project_type,
        ]);

        $this->syncProjectLecturers($project, $item);
        $this->syncExamSchedule($project, $item);
    }

    private function syncProjectLecturers(Project $project, array $item): void
    {
        $project->projectLecturers()->delete();

        $entries = [];

        if (!empty($item['adv_code'])) {
            $entries[] = ['user_code' => $item['adv_code'], 'relationship_id' => 1, 'sort_order' => 1];
        }
        foreach (['comm1_code' => 1, 'comm2_code' => 2, 'comm3_code' => 3] as $field => $order) {
            if (!empty($item[$field])) {
                $entries[] = ['user_code' => $item[$field], 'relationship_id' => 2, 'sort_order' => $order];
            }
        }
        if (!empty($item['co_adv_external'])) {
            // relationship_id=4 = Co-Advisor External (follows relationship_with_projects table)
            $entries[] = ['user_code' => $item['adv_code'], 'relationship_id' => 4, 'sort_order' => 1];
        }

        foreach ($entries as $e) {
            $project->projectLecturers()->create($e);
        }
    }

    private function syncExamSchedule(Project $project, array $item): void
    {
        if (empty($item['exam_date']) || empty($item['exam_start'])) return;

        $start = $item['exam_date'] . ' ' . $item['exam_start'] . ':00';
        $end   = $item['exam_date'] . ' ' . ($item['exam_end'] ?: $item['exam_start']) . ':00';

        $project->examSchedule()->updateOrCreate(
            ['project_id' => $project->project_id],
            [
                'ex_start_time' => $start,
                'ex_end_time'   => $end,
                'location'      => $item['exam_room'] ?: null,
            ]
        );
    }
}
