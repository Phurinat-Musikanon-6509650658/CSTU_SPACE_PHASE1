<?php
require 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// กำหนดการเชื่อมต่อฐานข้อมูล
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'cstu_space',
    'username'  => 'root',
    'password'  => '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

// เช็คโครงสร้างตาราง roles
echo "=== Roles table structure ===\n";
$columns = Capsule::select("SHOW COLUMNS FROM roles");
foreach ($columns as $column) {
    echo "Field: " . $column->Field . " | Type: " . $column->Type . " | Key: " . $column->Key . "\n";
}

// เช็ค primary key
echo "\n=== Primary key info ===\n";
$keys = Capsule::select("SHOW KEYS FROM roles WHERE Key_name = 'PRIMARY'");
foreach ($keys as $key) {
    echo "Column: " . $key->Column_name . "\n";
}