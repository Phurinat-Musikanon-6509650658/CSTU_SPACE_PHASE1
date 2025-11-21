<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            // Basic roles (เรียงตาม role_code จากมากไปน้อย)
            ['role' => 'admin', 'role_code' => 32768, 'role_code_bin' => bindec('1000000000000000')],
            ['role' => 'coordinator', 'role_code' => 16384, 'role_code_bin' => bindec('0100000000000000')], 
            ['role' => 'advisor', 'role_code' => 8192, 'role_code_bin' => bindec('0010000000000000')],
            ['role' => 'staff', 'role_code' => 4096, 'role_code_bin' => bindec('0001000000000000')],
            ['role' => 'student', 'role_code' => 2048, 'role_code_bin' => bindec('0000100000000000')],
            ['role' => 'committee', 'role_code' => 1024, 'role_code_bin' => bindec('0000010000000000')],
            ['role' => 'guest', 'role_code' => 1, 'role_code_bin' => bindec('0000000000000001')],
            
            // Combined roles (เรียงตาม role_code จากมากไปน้อย)
            ['role' => 'coordinator-advisor', 'role_code' => 24576, 'role_code_bin' => 16384 + 8192], // 16384 + 8192
            ['role' => 'coordinator-staff', 'role_code' => 20480, 'role_code_bin' => 16384 + 4096]    // 16384 + 4096
        ];

        foreach ($roles as $role) {
            \DB::table('roles')->updateOrInsert(
                ['role' => $role['role']], 
                $role
            );
        }
    }
}
