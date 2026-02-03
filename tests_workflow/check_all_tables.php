<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "\n================================================================================\n";
echo "  ตรวจสอบ Schema ของทุก Table ที่เกี่ยวข้อง\n";
echo "================================================================================\n";

$tables = [
    'project_proposals',
    'projects', 
    'project_evaluations',
    'project_grades'
];

foreach ($tables as $table) {
    echo "\n--- Table: $table ---\n";
    if (Schema::hasTable($table)) {
        $columns = DB::select("DESCRIBE $table");
        foreach ($columns as $col) {
            echo sprintf("  %-30s %-20s %s\n", 
                $col->Field, 
                $col->Type,
                $col->Null === 'YES' ? 'NULL' : 'NOT NULL'
            );
        }
    } else {
        echo "  ❌ Table ไม่มีอยู่!\n";
    }
}

echo "\n";
