<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Student;
use App\Models\Project;
use App\Models\Group;
use App\Models\ProjectLecturer;
use App\Models\Subject;
use App\Models\RelationshipWithProject;

$pass = 0;
$fail = 0;

function check(string $label, bool $result): void {
    global $pass, $fail;
    if ($result) {
        echo "  ✔  $label\n";
        $pass++;
    } else {
        echo "  ✘  $label\n";
        $fail++;
    }
}

echo "\n=== DB Table Counts ===\n";
check('Users seeded (23)',        User::count() === 23);
check('Students seeded (9)',      Student::count() === 9);
check('Relationships seeded (4)', RelationshipWithProject::count() === 4);
check('Subjects seeded (2)',      Subject::count() === 2);

echo "\n=== User Role Checks ===\n";
$admin  = User::where('user_code', 'ADM')->first();
$coord  = User::where('user_code', 'CRD')->first();
$lec    = User::where('user_code', 'kdc')->first();
$staff  = User::where('user_code', 'STF')->first();

check('Admin exists',            $admin !== null);
check('Admin isAdmin()',         $admin?->isAdmin());
check('Coordinator exists',      $coord !== null);
check('Coordinator isCoordinator()', $coord?->isCoordinator());
check('Lecturer exists',         $lec !== null);
check('Lecturer isLecturer()',   $lec?->isLecturer());
check('Staff exists',            $staff !== null);
check('Staff isStaff()',         $staff?->isStaff());

echo "\n=== Project Lecturers Pivot ===\n";
// Create test group + project
$group = Group::create([
    'year' => 2568, 'semester' => 1,
    'subject_code' => 'CS303', 'status_group' => 'approved',
]);
$project = Project::create([
    'group_id'       => $group->group_id,
    'project_name'   => 'TEST PROJECT',
    'project_code'   => 'TEST-REFACTOR-001',
    'student_type'   => 'r',
    'status_project' => 'in_progress',
]);

// Assign: kdc=advisor, ppr=committee1, ssr=committee2
$project->projectLecturers()->create(['user_code' => 'kdc', 'relationship_id' => 1, 'sort_order' => 1]);
$project->projectLecturers()->create(['user_code' => 'ppr', 'relationship_id' => 2, 'sort_order' => 1]);
$project->projectLecturers()->create(['user_code' => 'ssr', 'relationship_id' => 2, 'sort_order' => 2]);

$project->load(['advisorLecturer.user', 'committeeLecturers.user', 'projectLecturers']);

check('advisorLecturer loaded',          $project->advisorLecturer !== null);
check('committeeLecturers count = 2',    $project->committeeLecturers->count() === 2);

// Backward-compat accessors
check('$project->advisor_code = kdc',    $project->advisor_code === 'kdc');
check('$project->committee1_code = ppr', $project->committee1_code === 'ppr');
check('$project->committee2_code = ssr', $project->committee2_code === 'ssr');
check('$project->committee3_code = null',$project->committee3_code === null);
check('$project->advisor->firstname',    $project->advisor->firstname_user === 'กษิดิศ');
check('$project->committee1->firstname', $project->committee1->firstname_user === 'ประภาพร');

// getEvaluatorRole()
check('getEvaluatorRole(kdc) = advisor',     $project->getEvaluatorRole('kdc') === 'advisor');
check('getEvaluatorRole(ppr) = committee1',  $project->getEvaluatorRole('ppr') === 'committee1');
check('getEvaluatorRole(ssr) = committee2',  $project->getEvaluatorRole('ssr') === 'committee2');
check('getEvaluatorRole(ADM) = null',        $project->getEvaluatorRole('ADM') === null);

// syncLecturers()
$project->syncLecturers('ddp', 'tnt', 'pkl', null);
$project->load(['advisorLecturer.user', 'committeeLecturers.user', 'projectLecturers']);

check('syncLecturers: new advisor = ddp',    $project->advisor_code === 'ddp');
check('syncLecturers: committee1 = tnt',     $project->committee1_code === 'tnt');
check('syncLecturers: committee2 = pkl',     $project->committee2_code === 'pkl');
check('syncLecturers: committee3 = null',    $project->committee3_code === null);
check('syncLecturers: old kdc gone',         $project->projectLecturers->firstWhere('user_code','kdc') === null);

// whereHas queries (used in controllers)
$found = Project::whereHas('advisorLecturer', fn($q) => $q->where('user_code', 'ddp'))->first();
check('whereHas advisorLecturer works',      $found?->project_id === $project->project_id);

$found2 = Project::whereHas('projectLecturers', fn($q) => $q->where('user_code', 'tnt'))->first();
check('whereHas projectLecturers works',     $found2?->project_id === $project->project_id);

// Cleanup
$project->delete();
$group->delete();

echo "\n=== Student Auth ===\n";
$student = Student::where('username_std', '6509650658')->first();
check('Student 6509650658 exists', $student !== null);
check('Student course_code = CS303', $student?->course_code === 'CS303');

echo "\n=== Subjects ===\n";
$cs303 = Subject::where('subject_code', 'CS303')->first();
check('CS303 exists',              $cs303 !== null);
check('CS303 is_enabled = true',   $cs303?->is_enabled === true);
check('CS303 has access_open_date', $cs303?->access_open_date !== null);

echo "\n";
echo "===========================================\n";
echo "  PASSED: $pass  |  FAILED: $fail\n";
echo "===========================================\n\n";
