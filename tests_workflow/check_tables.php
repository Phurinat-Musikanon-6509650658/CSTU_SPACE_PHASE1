<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "\n=== DATABASE STRUCTURE CHECK ===\n\n";

echo "GROUP_INVITATIONS columns:\n";
$cols = Schema::getColumnListing('group_invitations');
foreach ($cols as $col) {
    echo "  - {$col}\n";
}

echo "\nUSER table (Lecturers check):\n";
$lecturers = DB::table('user')->where('role', '>=', 8192)->get();
echo "Total Lecturers: " . $lecturers->count() . "\n";
foreach ($lecturers as $lec) {
    echo "  - {$lec->user_code}: {$lec->firstname_user} {$lec->lastname_user} (role: {$lec->role})\n";
}
echo "\n";
