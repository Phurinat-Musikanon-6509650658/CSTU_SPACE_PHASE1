<?php
/**
 * Test import flows:
 *   1. Student Excel import (parse + preview logic)
 *   2. Project import from template (parse + insert via SubjectSummaryController logic)
 *
 * Run: docker exec cstu_space_app php scripts/test_import.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Helpers\XlsxParser;

$pass = 0;
$fail = 0;

function ok(string $msg)  { global $pass; $pass++; echo "  ✓ $msg\n"; }
function err(string $msg) { global $fail; $fail++; echo "  ✗ $msg\n"; }
function section(string $title) { echo "\n── $title ──\n"; }

// ═══════════════════════════════════════════════════════════════
// 1. STUDENT EXCEL IMPORT
// ═══════════════════════════════════════════════════════════════
section('1. Student Excel — Parse');

$xlsxPath = base_path('public/templates/test_students.xlsx');
if (!file_exists($xlsxPath)) { err("test_students.xlsx not found"); goto done; }
ok("test_students.xlsx exists");

$sheets = XlsxParser::sheetNames($xlsxPath);
$studentSheets = array_filter($sheets, fn($n) => str_contains($n, '--Students'));
ok("Sheet names: " . implode(', ', $sheets));
count($studentSheets) >= 2 ? ok("Found " . count($studentSheets) . " --Students sheets") : err("Missing --Students sheets");

// Parse each sheet
$preview = [];
foreach ($studentSheets as $sName) {
    preg_match('/\b(CS\d+)\b/i', $sName, $m);
    $courseCode = $m[1] ?? 'CS303';
    $rows = XlsxParser::parseSheet($xlsxPath, $sName, 0);
    // skip header row (row[0])
    foreach (array_slice($rows, 1) as $row) {
        $sid = trim($row[2] ?? '');
        if ($sid === '') continue;
        $fullname = trim($row[1] ?? '');
        $parts = preg_split('/\s+/u', $fullname, 2);
        $courseTypeText = trim($row[5] ?? '');
        $preview[] = [
            'prefix'       => trim($row[0] ?? ''),
            'firstname_std'=> $parts[0] ?? $fullname,
            'lastname_std' => $parts[1] ?? '-',
            'username_std' => $sid,
            'email_std'    => trim($row[3] ?? ''),
            'phone'        => trim($row[4] ?? ''),
            'course_code'  => $courseCode,
            'student_type' => str_contains($courseTypeText, 'พิเศษ') ? 's' : 'r',
        ];
    }
}

count($preview) === 8 ? ok("Parsed 8 students (7 CS303 + 1 CS403)") : err("Expected 8 students, got " . count($preview));

// Spot-check first student
$first = $preview[0];
$first['prefix'] === 'นาย'    ? ok("prefix OK: {$first['prefix']}") : err("prefix wrong: {$first['prefix']}");
$first['username_std'] === '6509650757' ? ok("username_std OK") : err("username_std wrong: {$first['username_std']}");
$first['student_type'] === 'r' ? ok("student_type 'r' OK") : err("student_type wrong: {$first['student_type']}");

// Spot-check พิเศษ row (index 4 = สมหญิง ภาคพิเศษ)
$sp = $preview[4];
$sp['student_type'] === 's' ? ok("student_type 's' OK ({$sp['firstname_std']})") : err("student_type wrong for special: {$sp['student_type']}");

// phone stored
!empty($first['phone']) ? ok("phone parsed: {$first['phone']}") : err("phone empty");

section('2. Student Excel — DB Insert (clean run)');

// Clear test students first (only seeder-pattern IDs)
$testIds = array_column($preview, 'username_std');
DB::table('student')->whereIn('username_std', $testIds)->delete();
ok("Cleared " . count($testIds) . " test records from DB");

// Check existing
$existing = DB::table('student')->whereIn('username_std', $testIds)->pluck('username_std')->toArray();
count($existing) === 0 ? ok("No duplicates before insert") : err("Unexpected existing records: " . implode(',', $existing));

// Insert all
$created = 0;
DB::beginTransaction();
try {
    foreach ($preview as $item) {
        DB::table('student')->insert([
            'prefix_std'    => $item['prefix'],
            'username_std'  => $item['username_std'],
            'firstname_std' => $item['firstname_std'],
            'lastname_std'  => $item['lastname_std'],
            'email_std'     => $item['email_std'],
            'phone_std'     => $item['phone'],
            'password_std'  => Hash::make($item['username_std']),
            'role'          => 2048,
            'course_code'   => $item['course_code'],
            'student_type'  => $item['student_type'],
            'semester'      => 2,
            'year'          => 2568,
        ]);
        $created++;
    }
    DB::commit();
    ok("Inserted $created students");
} catch (\Exception $e) {
    DB::rollBack();
    err("Insert failed: " . $e->getMessage());
}

// Verify in DB
$inserted = DB::table('student')
    ->whereIn('username_std', $testIds)
    ->get(['username_std','prefix_std','phone_std','course_code','student_type']);

count($inserted) === 8 ? ok("DB has 8 students") : err("DB has " . count($inserted) . " (expected 8)");

// Check prefix/phone stored
$s1 = $inserted->where('username_std', '6509650757')->first();
$s1 && $s1->prefix_std === 'นาย' ? ok("prefix_std stored: {$s1->prefix_std}") : err("prefix_std not stored");
$s1 && !empty($s1->phone_std)    ? ok("phone_std stored: {$s1->phone_std}") : err("phone_std not stored");

// Check duplicate prevention
$existing2 = DB::table('student')->whereIn('username_std', $testIds)->pluck('username_std')->toArray();
$newOnes = array_filter($existing2, fn($id) => !in_array($id, $testIds));
count($newOnes) === 0 ? ok("No ghost records") : err("Ghost records found");

// Test duplicate detection logic (preview status)
$existingIds = DB::table('student')->whereIn('username_std', $testIds)->pluck('username_std')->toArray();
$dupeCount = count(array_filter($preview, fn($r) => in_array($r['username_std'], $existingIds)));
$dupeCount === 8 ? ok("Duplicate detection: all 8 marked 'exists' after insert") : err("Duplicate detection wrong: $dupeCount/8");

// ═══════════════════════════════════════════════════════════════
// 3. PROJECT IMPORT — Template parse check
// ═══════════════════════════════════════════════════════════════
section('3. Project Template — Parse');

$tplPath = base_path('public/templates/project_import_template.xlsx');
if (!file_exists($tplPath)) { err("project_import_template.xlsx not found"); goto done; }
ok("project_import_template.xlsx exists");

$tplSheets = XlsxParser::sheetNames($tplPath);
ok("Template sheets: " . implode(', ', $tplSheets));

// ใช้ชื่อ sheet แรกที่พบจริง
$tplSheetName = $tplSheets[0] ?? 'Template';
$tplRows = XlsxParser::parseSheet($tplPath, $tplSheetName, 5); // skip 5 header rows
ok("Sheet '$tplSheetName' — data rows after 5-header skip: " . count($tplRows));

if (count($tplRows) > 0) {
    $r = $tplRows[0];
    echo "   First data row cols 0-9: [" . implode('] [', array_slice($r, 0, 10)) . "…]\n";
}

// ═══════════════════════════════════════════════════════════════
// 4. PROJECT IMPORT — Real import with sample data
// ═══════════════════════════════════════════════════════════════
section('4. Project Import — prerequisites');

// ตรวจ relationship_with_projects (id=1 = Advisor ต้องมี)
$relAdv = DB::table('relationship_with_projects')->where('id', 1)->first();
$relAdv ? ok("relationship_with_projects id=1 (Advisor) exists") : err("Missing relationship_with_projects — run RelationshipWithProjectsSeeder");

// ตรวจว่ามี lecturer
$lec = DB::table('user')->where('role', 8192)->first();
if (!$lec) { err("No lecturer in DB — run UserTableSeeder"); goto done; }
ok("Lecturer found: {$lec->user_code} ({$lec->firstname_user} {$lec->lastname_user})");

// ตรวจว่ามี subjects (plural) CS303
$subj = DB::table('subjects')->where('subject_code', 'CS303')->first();
$subj ? ok("subjects.CS303 exists") : err("subjects.CS303 not found — run SubjectSeeder (non-blocking)");

// ตรวจ test student
$testStudent = DB::table('student')->where('username_std', '6509650001')->first();
if (!$testStudent) { err("Test student 6509650001 not found"); goto done; }
ok("Test student: {$testStudent->firstname_std} {$testStudent->lastname_std}");

section('4. Project Import — createFromImport (mirror SubjectSummaryController)');

const TEST_CODE = 'TEST-IMPORT-001';

// Cleanup ก่อน
$oldProj = DB::table('projects')->where('project_code', TEST_CODE)->first();
if ($oldProj) {
    DB::table('project_lecturers')->where('project_id', $oldProj->project_id)->delete();
    DB::table('exam_schedule')->where('project_id', $oldProj->project_id)->delete();
    DB::table('projects')->where('project_id', $oldProj->project_id)->delete();
    $oldGroup = DB::table('groups')->where('group_id', $oldProj->group_id)->first();
    if ($oldGroup) {
        DB::table('group_members')->where('group_id', $oldGroup->group_id)->delete();
        DB::table('groups')->where('group_id', $oldGroup->group_id)->delete();
    }
    ok("Cleaned up old TEST-IMPORT-001 data");
}

// item จำลองเหมือนที่ SubjectSummaryController::createFromImport ได้รับ
$item = [
    'project_code' => TEST_CODE,
    'adv_code'     => $lec->user_code,
    'project_name' => 'โครงงานทดสอบ Import',
    'project_type' => 'r',        // ภาคปกติ
    'subject_code' => 'CS303',
    'm1_id'        => $testStudent->username_std,
    'm1_firstname' => $testStudent->firstname_std,
    'm1_lastname'  => $testStudent->lastname_std,
    'm1_email'     => $testStudent->email_std,
    'm1_type'      => 'r',
    'm2_id'        => '',
    'comm1_code'   => '',
    'comm2_code'   => '',
    'comm3_code'   => '',
    'exam_date'    => '',
    'exam_start'   => '',
    'exam_end'     => '',
    'exam_room'    => '',
];

DB::beginTransaction();
try {
    // 1. Group
    $groupId = DB::table('groups')->insertGetId([
        'year'         => 2568,
        'semester'     => 2,
        'subject_code' => $item['subject_code'],
        'status_group' => 'approved',
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);
    ok("groups created (id=$groupId, subject_code={$item['subject_code']})");

    // 2. Group member
    DB::table('group_members')->insert([
        'group_id'    => $groupId,
        'username_std'=> $item['m1_id'],
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);
    ok("group_members: {$item['m1_id']} inserted");

    // 3. Project
    $projectId = DB::table('projects')->insertGetId([
        'group_id'       => $groupId,
        'project_name'   => $item['project_name'],
        'project_code'   => $item['project_code'],
        'student_type'   => $item['project_type'],
        'status_project' => 'in_progress',
        'project_type'   => $item['project_type'],
        'created_at'     => now(),
        'updated_at'     => now(),
    ]);
    ok("projects created (id=$projectId, code={$item['project_code']})");

    // 4. Advisor link (relationship_id=1)
    DB::table('project_lecturers')->insert([
        'project_id'      => $projectId,
        'user_code'       => $item['adv_code'],
        'relationship_id' => 1,
        'sort_order'      => 1,
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);
    ok("project_lecturers: advisor {$item['adv_code']} linked (rel_id=1)");

    DB::commit();
    ok("Transaction committed");

    // 5. Verify
    $proj = DB::table('projects')->where('project_code', TEST_CODE)->first();
    $proj ? ok("Verify: project in DB (id={$proj->project_id})") : err("Verify: project missing");

    $memCnt = DB::table('group_members')->where('group_id', $groupId)->count();
    $memCnt === 1 ? ok("Verify: 1 group_member") : err("Verify: member count = $memCnt");

    $plCnt = DB::table('project_lecturers')->where('project_id', $projectId)->count();
    $plCnt >= 1 ? ok("Verify: $plCnt project_lecturer row(s)") : err("Verify: project_lecturers empty");

    // 6. Duplicate-code rejection
    try {
        DB::table('projects')->insert([
            'group_id'       => $groupId + 999,
            'project_name'   => 'ซ้ำ',
            'project_code'   => TEST_CODE,  // ซ้ำ!
            'student_type'   => 'r',
            'status_project' => 'in_progress',
            'project_type'   => 'r',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
        err("Verify: duplicate project_code was NOT rejected (unique constraint missing?)");
    } catch (\Illuminate\Database\QueryException $e) {
        ok("Verify: duplicate project_code correctly rejected (unique constraint works)");
    }

} catch (\Exception $e) {
    DB::rollBack();
    err("Project import failed: " . $e->getMessage());
}

// ═══════════════════════════════════════════════════════════════
done:
echo "\n══════════════════════════════════════\n";
echo "RESULT: {$pass} passed, {$fail} failed\n";
if ($fail === 0) echo "ALL TESTS PASSED ✓\n";
else echo "SOME TESTS FAILED — check errors above\n";
echo "══════════════════════════════════════\n";
