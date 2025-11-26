<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create exam_schedule table
        Schema::create('exam_schedule', function (Blueprint $table) {
            $table->id('ex_id');
            $table->unsignedBigInteger('project_id'); // เชื่อมกับตาราง projects
            $table->dateTime('ex_start_time'); // วันเวลาเริ่มสอบ
            $table->dateTime('ex_end_time'); // วันเวลาสิ้นสุดสอบ
            $table->string('location', 200)->nullable(); // สถานที่สอบ
            $table->text('notes')->nullable(); // หมายเหตุ
            $table->timestamps();
            
            // Foreign key
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            
            // Index
            $table->index('ex_start_time');
        });

        // Create system_settings table
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id('setting_id');
            $table->string('setting_key', 100)->unique(); // เช่น 'system_status', 'current_year', 'current_semester'
            $table->text('setting_value'); // ค่าของการตั้งค่า
            $table->string('description', 255)->nullable(); // คำอธิบาย
            $table->timestamps();
        });
        
        // Insert default settings
        DB::table('system_settings')->insert([
            [
                'setting_key' => 'system_status',
                'setting_value' => 'open',
                'description' => 'System status: open or closed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'current_year',
                'setting_value' => '2568',
                'description' => 'Current academic year',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'setting_key' => 'current_semester',
                'setting_value' => '1',
                'description' => 'Current semester (1, 2, or 3)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_schedule');
        Schema::dropIfExists('system_settings');
    }
};
