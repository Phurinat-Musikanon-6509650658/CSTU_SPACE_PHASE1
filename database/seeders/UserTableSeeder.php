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
                'role' => 32768,
                'email_user' => 'admin@cstu.ac.th',
                'password_user' => Hash::make('admin123'),
            ]
        );
    }
}
