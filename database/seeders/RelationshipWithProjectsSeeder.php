<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RelationshipWithProjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('relationship_with_projects')->insert([
            [
                'id' => 1,
                'relationship' => 'Advisor',
                'relationship_abbrev' => 'Adv',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'relationship' => 'Committee',
                'relationship_abbrev' => 'Com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'relationship' => 'Co-Advisor-Internal',
                'relationship_abbrev' => 'Co-Adv-Int',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'relationship' => 'Co-Advisor-External',
                'relationship_abbrev' => 'Co-Adv-Ext',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
