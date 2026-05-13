<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Helpers\XlsxParser;

$path = base_path('68-2_Projects1-2_Info-for-CSTU-SPACE.xlsx');
if (!file_exists($path)) {
    die("File not found: $path\n");
}

$sheets = XlsxParser::sheetNames($path);
echo "All sheets:\n";
foreach ($sheets as $i => $s) echo "  [$i] $s\n";
echo "\n";

// Show all sheets
foreach ($sheets as $sName) {
    echo "=== $sName ===\n";
    $rows = XlsxParser::parseSheet($path, $sName, 0);
    echo "Total rows: " . count($rows) . "\n";

    // Show first 5 rows fully
    foreach (array_slice($rows, 0, 5) as $ri => $row) {
        echo "Row $ri: ";
        foreach ($row as $ci => $val) {
            if ($val !== '') echo "  col[$ci]=" . mb_substr($val, 0, 50);
        }
        echo "\n";
    }
    echo "\n";
}
