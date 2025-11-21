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

// เพิ่ม roles ที่ขาดหายไป
$missingRoles = [
    ['role' => 'advisor', 'role_code' => 16, 'role_code_bin' => 16],
    ['role' => 'student', 'role_code' => 32, 'role_code_bin' => 32]
];

foreach ($missingRoles as $roleData) {
    // เช็คว่ามีอยู่แล้วหรือไม่
    $exists = Capsule::table('roles')->where('role', $roleData['role'])->exists();
    
    if (!$exists) {
        Capsule::table('roles')->insert($roleData);
        echo "Added role: " . $roleData['role'] . "\n";
    } else {
        echo "Role already exists: " . $roleData['role'] . "\n";
    }
}

echo "Done!\n";