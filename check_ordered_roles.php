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

echo "=== Roles เรียงตาม role_code (มาก->น้อย), combined roles ล่างสุด ===\n";

// Basic roles เรียงตาม role_code จากมากไปน้อย
$basicRoles = Capsule::table('roles')
    ->whereNotIn('role', ['coordinator-advisor', 'coordinator-staff'])
    ->orderBy('role_code', 'desc')
    ->get();

foreach ($basicRoles as $role) {
    echo "Role: {$role->role} | Code: {$role->role_code} | CodeBin: {$role->role_code_bin}\n";
}

echo "\n=== Combined Roles ===\n";
// Combined roles เรียงตาม role_code จากมากไปน้อย
$combinedRoles = Capsule::table('roles')
    ->whereIn('role', ['coordinator-advisor', 'coordinator-staff'])
    ->orderBy('role_code', 'desc')
    ->get();

foreach ($combinedRoles as $role) {
    echo "Role: {$role->role} | Code: {$role->role_code} | CodeBin: {$role->role_code_bin}\n";
}