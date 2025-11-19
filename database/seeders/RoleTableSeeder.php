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
            ['id' => 1, 'role' => 'Admin', 'role_code' => 32768, 'role_code_bin' => bindec('1000000000000000')],
            ['id' => 2, 'role' => 'Coordinator', 'role_code' => 16384, 'role_code_bin' => bindec('0100000000000000')], 
            ['id' => 3, 'role' => 'Lecturer', 'role_code' => 8192, 'role_code_bin' => bindec('0010000000000000')],
            ['id' => 4, 'role' => 'Staff', 'role_code' => 4096, 'role_code_bin' => bindec('0001000000000000')],
            ['id' => 5, 'role' => 'Student', 'role_code' => 2048, 'role_code_bin' => bindec('0000100000000000')],
            ['id' => 6, 'role' => 'Guest (Future Work)', 'role_code' => 1, 'role_code_bin' => bindec('0000000000000001')],
            ['id' => 7, 'role' => 'Coordinator - Lecturer', 'role_code' => 24576, 'role_code_bin' => bindec('0110000000000000')],
            ['id' => 8, 'role' => 'Coordinator - Staff', 'role_code' => 20480, 'role_code_bin' => bindec('0101000000000000')]
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['id' => $role['id']], 
                $role
            );
        }
    }
}
