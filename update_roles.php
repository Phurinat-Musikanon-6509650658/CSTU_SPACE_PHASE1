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

// ลบ roles ที่ไม่ต้องการ
$rolesToDelete = ['Lecturer', 'Coordinator - Lecturer', 'Coordinator - Staff', 'Guest (Future Work)'];
foreach ($rolesToDelete as $roleToDelete) {
    $deleted = Capsule::table('roles')->where('role', $roleToDelete)->delete();
    if ($deleted) {
        echo "Deleted role: $roleToDelete\n";
    }
}

// อัพเดต advisor ให้มี role_code ที่ถูกต้อง
$updated = Capsule::table('roles')
    ->where('role', 'advisor')
    ->update([
        'role_code' => 8192,
        'role_code_bin' => 8192
    ]);

if ($updated) {
    echo "Updated advisor role_code to 8192\n";
}

echo "Done!\n";