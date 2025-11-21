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

echo "=== All roles with role_code ===\n";
$roles = Capsule::table('roles')->select('role', 'role_code', 'role_code_bin')->get();
foreach ($roles as $role) {
    echo "Role: {$role->role} | Code: {$role->role_code} | CodeBin: {$role->role_code_bin}\n";
}

echo "\n=== Duplicate role_codes ===\n";
$duplicates = Capsule::table('roles')
    ->select('role_code', Capsule::raw('COUNT(*) as count'))
    ->groupBy('role_code')
    ->having('count', '>', 1)
    ->get();
    
foreach ($duplicates as $dup) {
    echo "Role Code: {$dup->role_code} appears {$dup->count} times\n";
    
    // แสดง roles ที่ใช้ role_code นี้
    $rolesWithCode = Capsule::table('roles')->where('role_code', $dup->role_code)->get();
    foreach ($rolesWithCode as $r) {
        echo "  - {$r->role}\n";
    }
}