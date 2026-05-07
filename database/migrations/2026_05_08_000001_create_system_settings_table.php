<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id('setting_id');
            $table->string('setting_key')->unique();
            $table->string('setting_value');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        DB::table('system_settings')->insert([
            ['setting_key' => 'system_status', 'setting_value' => 'open',  'description' => 'System open/close status (open|closed)', 'created_at' => now(), 'updated_at' => now()],
            ['setting_key' => 'system_name',   'setting_value' => 'CSTU SPACE', 'description' => 'System display name',               'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
