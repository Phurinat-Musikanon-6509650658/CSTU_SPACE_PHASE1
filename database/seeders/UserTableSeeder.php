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
        DB::table('user')->updateOrInsert(
            ['username_user' => '6503640226'],
            [
                'firstname_user' => 'กันตินันท์',
                'lastname_user' => 'ตันติยาภินันท์',
                'user_code' => 'KTN',
                'role' => 'admin',
                'email_user' => 'kantinan.tan@dome.tu.ac.th',
                'password_user' => '1100703568130',
                'password_user_hash' => Hash::make('1100703568130'),
            ]
        );

        DB::table('user')->updateOrInsert(
            ['username_user' => '6510470310'],
            [
                'firstname_user' => 'ภูรี',
                'lastname_user' => 'เข่งเจริญ',
                'user_code' => 'PHR',
                'role' => 'coordinator',
                'email_user' => 'phuree.ken@dome.tu.ac.th',
                'password_user' => '1102200195289',
                'password_user_hash' => Hash::make('1102200195289'),
            ]
        );
    }
}
