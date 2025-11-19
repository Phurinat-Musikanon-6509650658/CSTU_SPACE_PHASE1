<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run our SQL-based seeders (they will update or insert hashed passwords)
        $this->call([
            \Database\Seeders\RoleTableSeeder::class,
            \Database\Seeders\UserTableSeeder::class,
            \Database\Seeders\StudentTableSeeder::class,
        ]);
    }
}
