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
        // Student ทดสอบ
        DB::table('student')->updateOrInsert(
            ['username_std' => 'student'],
            [
                'firstname_std' => 'นักศึกษา',
                'lastname_std' => 'ทดสอบ',
                'email_std' => 'student@cstu.ac.th',
                'password_std' => Hash::make('student123'),
                'role' => 'student',
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
                'role' => 'student',
            ]
        );

        DB::table('student')->updateOrInsert(
            ['username_std' => '6509611676'],
            [
                'firstname_std' => 'ณัชชา',
                'lastname_std' => 'วัฒนบำเพ็ญ',
                'email_std' => 'natcha.wattan@dome.tu.ac.th',
                'password_std' => Hash::make('1709700292985'),
                'role' => 'student',
            ]
        );

        DB::table('student')->updateOrInsert(
            ['username_std' => '6509650658'],
            [
                'firstname_std' => 'ภูริณัฐ',
                'lastname_std' => 'มุสิกานนท์',
                'email_std' => 'phurinat.mus@dome.tu.ac.th',
                'password_std' => Hash::make('1104000099105'),
                'role' => 'student',
            ]
        );        
    }
}
