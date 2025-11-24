<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Admin (role_code = 32768)
        DB::table('user')->updateOrInsert(
            ['username_user' => 'admin'],
            [
                'firstname_user' => 'Admin',
                'lastname_user' => 'System',
                'user_code' => 'ADM',
                'role' => 32768,  // Admin role_code
                'email_user' => 'admin@cstu.ac.th',
                'password_user' => Hash::make('admin123'),
            ]
        );

        // Coordinator (role_code = 16384)
        DB::table('user')->updateOrInsert(
            ['username_user' => 'coordinator'],
            [
                'firstname_user' => 'ผู้ประสานงาน',
                'lastname_user' => 'ทดสอบ',
                'user_code' => 'CRD',
                'role' => 16384,  // Coordinator role_code
                'email_user' => 'coordinator@cstu.ac.th',
                'password_user' => Hash::make('coordinator123'),
            ]
        );

        // Lecturer/Advisor (role_code = 8192)
        DB::table('user')->updateOrInsert(
            ['username_user' => 'advisor'],
            [
                'firstname_user' => 'อาจารย์ที่ปรึกษา',
                'lastname_user' => 'ทดสอบ',
                'user_code' => 'ADV',
                'role' => 8192,  // Lecturer role_code
                'email_user' => 'advisor@cstu.ac.th',
                'password_user' => Hash::make('advisor123'),
            ]
        );

        // ข้อมูลเดิม
        DB::table('user')->updateOrInsert(
            ['username_user' => '6503640226'],
            [
                'firstname_user' => 'กันตินันท์',
                'lastname_user' => 'ตันติยาภินันท์',
                'user_code' => 'KTN',
                'role' => 32768,  // Admin role_code
                'email_user' => 'kantinan.tan@dome.tu.ac.th',
                'password_user' => Hash::make('1100703568130'),
            ]
        );

        DB::table('user')->updateOrInsert(
            ['username_user' => '6510470310'],
            [
                'firstname_user' => 'ภูรี',
                'lastname_user' => 'เข่งเจริญ',
                'user_code' => 'PHR',
                'role' => 16384,  // Coordinator role_code
                'email_user' => 'phuree.ken@dome.tu.ac.th',
                'password_user' => Hash::make('1102200195289'),
            ]
        );
    }
}
