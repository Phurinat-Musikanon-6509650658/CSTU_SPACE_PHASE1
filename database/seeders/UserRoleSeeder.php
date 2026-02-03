<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserRole;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['role_id' => 1, 'role_name' => 'Admin', 'role_code' => 32768, 'role_code_bin' => bindec('1000000000000000')],
            ['role_id' => 2, 'role_name' => 'Coordinator', 'role_code' => 16384, 'role_code_bin' => bindec('0100000000000000')], 
            ['role_id' => 3, 'role_name' => 'Lecturer', 'role_code' => 8192, 'role_code_bin' => bindec('0010000000000000')],
            ['role_id' => 4, 'role_name' => 'Staff', 'role_code' => 4096, 'role_code_bin' => bindec('0001000000000000')],
            ['role_id' => 5, 'role_name' => 'Student', 'role_code' => 2048, 'role_code_bin' => bindec('0000100000000000')],
            ['role_id' => 6, 'role_name' => 'Guest (Future Work)', 'role_code' => 1, 'role_code_bin' => bindec('0000000000000001')],
            ['role_id' => 7, 'role_name' => 'Coordinator - Lecturer', 'role_code' => 24576, 'role_code_bin' => bindec('0110000000000000')],
            ['role_id' => 8, 'role_name' => 'Coordinator - Staff', 'role_code' => 20480, 'role_code_bin' => bindec('0101000000000000')]
        ];

        foreach ($roles as $role) {
            UserRole::updateOrCreate(
                ['role_id' => $role['role_id']], 
                $role
            );
        }
    }
}
