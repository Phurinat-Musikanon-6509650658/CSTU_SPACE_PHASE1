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
        DB::table('student')->updateOrInsert(
            ['username_std' => 'student'],
            [
                'firstname_std' => 'นักศึกษา',
                'lastname_std' => 'ทดสอบ',
                'email_std' => 'student@cstu.ac.th',
                'password_std' => Hash::make('student123'),
                'role' => 2048,
                'course_code' => 'CS303',
                'semester' => 2,
                'year' => 2568,
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
                'course_code' => 'CS303',
                'semester' => 2,
                'year' => 2568,
            ]
        );
    }
}
