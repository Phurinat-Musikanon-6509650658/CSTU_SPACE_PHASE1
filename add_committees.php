<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "================================================================================\n";
echo "  เพิ่มกรรมการสอบ (Committee) ให้กับโครงงานที่อนุมัติแล้ว\n";
echo "================================================================================\n\n";

// ดึงโครงงานที่อนุมัติแล้ว
$approvedProjects = Project::with(['group', 'advisor'])
    ->where('status_project', 'approved')
    ->get();

if ($approvedProjects->isEmpty()) {
    echo "❌ ไม่พบโครงงานที่ได้รับการอนุมัติ\n";
    echo "   กรุณาทำขั้นตอนการอนุมัติโครงงานก่อน\n\n";
    exit(1);
}

echo "✅ พบโครงงานที่อนุมัติแล้ว: {$approvedProjects->count()} โครงงาน\n\n";

// ดึงอาจารย์ทั้งหมด (Lecturer role = 8192)
$lecturers = User::whereRaw('role & 8192 != 0')->get();

echo "✅ พบอาจารย์ในระบบ: {$lecturers->count()} คน\n\n";

if ($lecturers->count() < 3) {
    echo "⚠️  มีอาจารย์น้อยกว่า 3 คน อาจไม่สามารถเพิ่ม committee ครบได้\n\n";
}

foreach ($approvedProjects as $project) {
    echo "--------------------------------------------------------------------------------\n";
    echo "โครงงาน: {$project->project_code}\n";
    echo "ชื่อ: {$project->project_name}\n";
    echo "Advisor: {$project->advisor_code}\n";
    echo "Committee 1: " . ($project->committee1_code ?: '(ยังไม่มี)') . "\n";
    echo "Committee 2: " . ($project->committee2_code ?: '(ยังไม่มี)') . "\n";
    echo "Committee 3: " . ($project->committee3_code ?: '(ยังไม่มี)') . "\n\n";
    
    // ถ้ามี committee ครบแล้ว ข้าม
    if ($project->committee1_code && $project->committee2_code && $project->committee3_code) {
        echo "✅ โครงงานนี้มีกรรมการครบแล้ว\n\n";
        continue;
    }
    
    // เลือกอาจารย์ที่ไม่ใช่ advisor
    $availableLecturers = $lecturers->filter(function($lecturer) use ($project) {
        return $lecturer->user_code !== $project->advisor_code &&
               $lecturer->user_code !== $project->committee1_code &&
               $lecturer->user_code !== $project->committee2_code &&
               $lecturer->user_code !== $project->committee3_code;
    })->values();
    
    $updates = [];
    
    // เพิ่ม Committee 1
    if (!$project->committee1_code && $availableLecturers->count() > 0) {
        $committee1 = $availableLecturers->shift();
        $updates['committee1_code'] = $committee1->user_code;
        echo "➕ เพิ่ม Committee 1: {$committee1->user_code} - {$committee1->firstname_user} {$committee1->lastname_user}\n";
    }
    
    // เพิ่ม Committee 2
    if (!$project->committee2_code && $availableLecturers->count() > 0) {
        $committee2 = $availableLecturers->shift();
        $updates['committee2_code'] = $committee2->user_code;
        echo "➕ เพิ่ม Committee 2: {$committee2->user_code} - {$committee2->firstname_user} {$committee2->lastname_user}\n";
    }
    
    // เพิ่ม Committee 3
    if (!$project->committee3_code && $availableLecturers->count() > 0) {
        $committee3 = $availableLecturers->shift();
        $updates['committee3_code'] = $committee3->user_code;
        echo "➕ เพิ่ม Committee 3: {$committee3->user_code} - {$committee3->firstname_user} {$committee3->lastname_user}\n";
    }
    
    if (!empty($updates)) {
        $project->update($updates);
        echo "✅ บันทึกข้อมูลเรียบร้อย\n";
    } else {
        echo "⚠️  ไม่มีอาจารย์ที่เหมาะสมเพื่อเพิ่มเป็นกรรมการ\n";
    }
    
    echo "\n";
}

echo "================================================================================\n";
echo "  สรุปผลลัพธ์\n";
echo "================================================================================\n\n";

// แสดงผลลัพธ์สุดท้าย
$finalProjects = Project::with(['advisor', 'committee1', 'committee2', 'committee3'])
    ->where('status_project', 'approved')
    ->get();

foreach ($finalProjects as $project) {
    echo "โครงงาน: {$project->project_code}\n";
    echo "  Advisor: {$project->advisor_code}";
    if ($project->advisor) {
        echo " ({$project->advisor->firstname_user} {$project->advisor->lastname_user})";
    }
    echo "\n";
    
    echo "  Committee 1: " . ($project->committee1_code ?: '-');
    if ($project->committee1) {
        echo " ({$project->committee1->firstname_user} {$project->committee1->lastname_user})";
    }
    echo "\n";
    
    echo "  Committee 2: " . ($project->committee2_code ?: '-');
    if ($project->committee2) {
        echo " ({$project->committee2->firstname_user} {$project->committee2->lastname_user})";
    }
    echo "\n";
    
    echo "  Committee 3: " . ($project->committee3_code ?: '-');
    if ($project->committee3) {
        echo " ({$project->committee3->firstname_user} {$project->committee3->lastname_user})";
    }
    echo "\n\n";
}

echo "🎉 เสร็จสิ้น!\n\n";
