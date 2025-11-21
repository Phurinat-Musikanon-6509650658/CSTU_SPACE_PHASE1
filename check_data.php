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

echo "=== Roles in roles table ===\n";
$roles = Capsule::table('roles')->select('role')->distinct()->get();
foreach ($roles as $role) {
    echo "Role: " . $role->role . "\n";
}

echo "\n=== Distinct roles in user table ===\n";
$userRoles = Capsule::table('user')->select('role')->distinct()->whereNotNull('role')->get();
foreach ($userRoles as $role) {
    echo "User Role: " . $role->role . "\n";
}

echo "\n=== Distinct roles in student table ===\n";
$studentRoles = Capsule::table('student')->select('role')->distinct()->whereNotNull('role')->get();
foreach ($studentRoles as $role) {
    echo "Student Role: " . $role->role . "\n";
}

// เช็คว่ามี role ใน user หรือ student ที่ไม่มีใน roles table หรือไม่
echo "\n=== Invalid roles in user table ===\n";
$invalidUserRoles = Capsule::table('user')
    ->leftJoin('roles', 'user.role', '=', 'roles.role')
    ->whereNull('roles.role')
    ->whereNotNull('user.role')
    ->select('user.role')
    ->distinct()
    ->get();
    
foreach ($invalidUserRoles as $role) {
    echo "Invalid User Role: " . $role->role . "\n";
}

echo "\n=== Invalid roles in student table ===\n";
$invalidStudentRoles = Capsule::table('student')
    ->leftJoin('roles', 'student.role', '=', 'roles.role')
    ->whereNull('roles.role')
    ->whereNotNull('student.role')
    ->select('student.role')
    ->distinct()
    ->get();
    
foreach ($invalidStudentRoles as $role) {
    echo "Invalid Student Role: " . $role->role . "\n";
}