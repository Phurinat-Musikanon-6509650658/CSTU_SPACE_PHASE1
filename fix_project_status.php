<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Project;
use App\Models\ProjectProposal;

echo "\n";
echo "================================================================================\n";
echo "  ตรวจสอบและแก้ไขสถานะโครงงาน\n";
echo "================================================================================\n\n";

// ตรวจสอบ proposal ที่ approved
$approvedProposals = ProjectProposal::where('status', 'approved')->with('group.project')->get();

echo "Proposals ที่อนุมัติแล้ว: {$approvedProposals->count()} รายการ\n\n";

foreach ($approvedProposals as $proposal) {
    if ($proposal->group && $proposal->group->project) {
        $project = $proposal->group->project;
        
        echo "Proposal ID: {$proposal->proposal_id}\n";
        echo "  Proposal Status: {$proposal->status}\n";
        echo "  Project Code: {$project->project_code}\n";
        echo "  Project Status (ก่อน): {$project->status_project}\n";
        
        if ($project->status_project !== 'approved') {
            $project->update(['status_project' => 'approved']);
            echo "  Project Status (หลัง): approved ✅ แก้ไขแล้ว\n";
        } else {
            echo "  Project Status: approved ✅ ถูกต้องแล้ว\n";
        }
        echo "\n";
    }
}

echo "================================================================================\n";
echo "  สถานะโครงงานทั้งหมดหลังอัปเดต\n";
echo "================================================================================\n\n";

$allProjects = Project::all();
foreach ($allProjects as $p) {
    echo "{$p->project_code}: {$p->status_project}\n";
}

echo "\n🎉 เสร็จสิ้น!\n\n";
