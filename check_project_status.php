<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Project;

echo "\n";
echo "================================================================================\n";
echo "  ตรวจสอบสถานะโครงงาน\n";
echo "================================================================================\n\n";

$projects = Project::with(['group.members.student', 'advisor'])->get();

foreach ($projects as $project) {
    echo "โครงงาน: {$project->project_code}\n";
    echo "  ชื่อ: {$project->project_name}\n";
    echo "  สถานะ: {$project->status_project}\n";
    echo "  Advisor: " . ($project->advisor_code ?: '(ยังไม่มี)') . "\n";
    
    if ($project->group && $project->group->members) {
        echo "  สมาชิกกลุ่ม:\n";
        foreach ($project->group->members as $member) {
            $student = $member->student;
            if ($student) {
                echo "    - {$student->username_std} ({$student->firstname_std} {$student->lastname_std})\n";
            }
        }
    }
    echo "\n";
}

echo "================================================================================\n\n";
