<?php
/**
 * Test Lecturer Notification System
 * 
 * This script tests:
 * 1. New proposal notifications (within 5 minutes)
 * 2. New report submission notifications (within 5 minutes)
 * 3. Recent group formation notifications (within 5 minutes)
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\ProjectProposal;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

echo "=== Lecturer Notification System Test ===\n\n";

// Find a lecturer user
$lecturer = User::where('role', 8192)->first();

if (!$lecturer) {
    echo "❌ No lecturer found in database\n";
    echo "Please create a lecturer user first\n";
    exit(1);
}

echo "✅ Testing with lecturer: {$lecturer->username_user} ({$lecturer->user_code})\n\n";

// Test 1: Check for new proposals (within 5 minutes)
echo "Test 1: New Proposals Notification\n";
echo "-----------------------------------\n";

$timeThreshold = now()->subMinutes(5);
$newProposals = ProjectProposal::where('proposed_to', $lecturer->username_user)
    ->where('status', 'pending')
    ->where('proposed_at', '>=', $timeThreshold)
    ->get();

echo "New proposals in last 5 minutes: " . $newProposals->count() . "\n";
if ($newProposals->count() > 0) {
    echo "✅ NOTIFICATION SHOULD SHOW: มีข้อเสนอโครงงานใหม่!\n";
    foreach ($newProposals as $proposal) {
        echo "  - {$proposal->proposed_title} (proposed at: {$proposal->proposed_at})\n";
    }
} else {
    echo "ℹ️  No notification (no new proposals in last 5 minutes)\n";
}
echo "\n";

// Test 2: Check for new report submissions (within 5 minutes)
echo "Test 2: New Report Submission Notification\n";
echo "-------------------------------------------\n";

$newReports = Project::where('advisor_code', $lecturer->user_code)
    ->whereNotNull('submission_file')
    ->where('submitted_at', '>=', $timeThreshold)
    ->get();

echo "New report submissions in last 5 minutes: " . $newReports->count() . "\n";
if ($newReports->count() > 0) {
    echo "✅ NOTIFICATION SHOULD SHOW: มีรายงานที่ส่งมาใหม่!\n";
    foreach ($newReports as $project) {
        echo "  - Project: {$project->project_code} (submitted at: {$project->submitted_at})\n";
    }
} else {
    echo "ℹ️  No notification (no new reports in last 5 minutes)\n";
}
echo "\n";

// Test 3: Check for recent groups
echo "Test 3: Recent Group Formation Notification\n";
echo "--------------------------------------------\n";

$recentGroups = ProjectProposal::where('proposed_to', $lecturer->username_user)
    ->where('proposed_at', '>=', $timeThreshold)
    ->get();

echo "Recent groups proposing in last 5 minutes: " . $recentGroups->count() . "\n";
if ($recentGroups->count() > 0) {
    echo "✅ NOTIFICATION SHOULD SHOW: มีกลุ่มใหม่ที่เสนอมาหาคุณ!\n";
    foreach ($recentGroups as $proposal) {
        echo "  - Group {$proposal->group_id}: {$proposal->proposed_title}\n";
    }
} else {
    echo "ℹ️  No notification (no recent groups in last 5 minutes)\n";
}
echo "\n";

// Statistics Summary
echo "=== Statistics Summary ===\n";
echo "--------------------------\n";

$stats = [
    'pending_proposals' => ProjectProposal::where('proposed_to', $lecturer->username_user)
        ->where('status', 'pending')
        ->count(),
    'approved_proposals' => ProjectProposal::where('proposed_to', $lecturer->username_user)
        ->where('status', 'approved')
        ->count(),
    'my_projects' => Project::where('advisor_code', $lecturer->user_code)->count(),
    'pending_evaluations' => Project::where(function($q) use ($lecturer) {
            $q->where('advisor_code', $lecturer->user_code)
              ->orWhere('committee1_code', $lecturer->user_code)
              ->orWhere('committee2_code', $lecturer->user_code)
              ->orWhere('committee3_code', $lecturer->user_code);
        })
        ->whereNotNull('exam_datetime')
        ->whereDoesntHave('evaluations', function($q) use ($lecturer) {
            $q->where('evaluator_code', $lecturer->user_code);
        })
        ->count(),
];

echo "Total Pending Proposals: {$stats['pending_proposals']}\n";
echo "Total Approved Proposals: {$stats['approved_proposals']}\n";
echo "My Projects (as advisor): {$stats['my_projects']}\n";
echo "Pending Evaluations: {$stats['pending_evaluations']}\n";

echo "\n✅ Test completed successfully!\n";
echo "\nTo trigger notifications:\n";
echo "1. Have a student submit a new proposal to this lecturer\n";
echo "2. Have a student upload a report (within 5 minutes)\n";
echo "3. Access lecturer dashboard at: /lecturer or /lecturer/dashboard\n";
