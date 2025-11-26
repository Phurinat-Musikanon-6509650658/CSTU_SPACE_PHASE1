<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Student ทดสอบ - role_code = 2048
        DB::table('student')->updateOrInsert(
            ['username_std' => 'student'],
            [
                'firstname_std' => 'นักศึกษา',
                'lastname_std' => 'ทดสอบ',
                'email_std' => 'student@cstu.ac.th',
                'password_std' => Hash::make('student123'),
                'role' => 2048,
            ]
        );

        // ข้อมูลเดิม
        DB::table('student')->updateOrInsert(
            ['username_std' => '6509650757'],
            [
                'firstname_std' => 'หฤษฎ์',
                'lastname_std' => 'อัชฌาวนิชย์',
                'email_std' => 'haritch.utc@dome.tu.ac.th',
                'password_std' => Hash::make('1101700338550'),
                'role' => 2048,
            ]
        );

        DB::table('student')->updateOrInsert(
            ['username_std' => '6509611676'],
            [
                'firstname_std' => 'ณัชชา',
                'lastname_std' => 'วัฒนบำเพ็ญ',
                'email_std' => 'natcha.wattan@dome.tu.ac.th',
                'password_std' => Hash::make('1709700292985'),
                'role' => 2048,
            ]
        );

        DB::table('student')->updateOrInsert(
            ['username_std' => '6509650658'],
            [
                'firstname_std' => 'ภูริณัฐ',
                'lastname_std' => 'มุสิกานนท์',
                'email_std' => 'phurinat.mus@dome.tu.ac.th',
                'password_std' => Hash::make('1104000099105'),
                'role' => 2048,
            ]
        );

        // เพิ่ม student เพื่อทดสอบกลุ่ม
        DB::table('student')->updateOrInsert(
            ['username_std' => '6509650001'],
            [
                'firstname_std' => 'สมชาย',
                'lastname_std' => 'ใจดี',
                'email_std' => '6509650001@dome.tu.ac.th',
                'password_std' => Hash::make('password'),
                'role' => 2048,
            ]
        );

        DB::table('student')->updateOrInsert(
            ['username_std' => '6509650002'],
            [
                'firstname_std' => 'สมหญิง',
                'lastname_std' => 'รักเรียน',
                'email_std' => '6509650002@dome.tu.ac.th',
                'password_std' => Hash::make('password'),
                'role' => 2048,
            ]
        );

        DB::table('student')->updateOrInsert(
            ['username_std' => '6509650003'],
            [
                'firstname_std' => 'วิทยา',
                'lastname_std' => 'ศรีสุข',
                'email_std' => '6509650003@dome.tu.ac.th',
                'password_std' => Hash::make('password'),
                'role' => 2048,
            ]
        );

        DB::table('student')->updateOrInsert(
            ['username_std' => '6509650004'],
            [
                'firstname_std' => 'ชัยชนะ',
                'lastname_std' => 'มั่นคง',
                'email_std' => '6509650004@dome.tu.ac.th',
                'password_std' => Hash::make('password'),
                'role' => 2048,
            ]
        );

        DB::table('student')->updateOrInsert(
            ['username_std' => '6509650005'],
            [
                'firstname_std' => 'ปรีชา',
                'lastname_std' => 'เจริญ',
                'email_std' => '6509650005@dome.tu.ac.th',
                'password_std' => Hash::make('password'),
                'role' => 2048,
            ]
        );
    }
}
