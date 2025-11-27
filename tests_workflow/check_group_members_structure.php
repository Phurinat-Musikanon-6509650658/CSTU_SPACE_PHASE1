<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "\n=== GROUP_MEMBERS TABLE STRUCTURE ===\n\n";

$columns = Schema::getColumnListing('group_members');

echo "Columns:\n";
foreach ($columns as $column) {
    echo "  - {$column}\n";
}
echo "\n";
