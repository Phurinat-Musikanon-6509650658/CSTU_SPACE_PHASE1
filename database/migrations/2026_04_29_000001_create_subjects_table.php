<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id('subject_id');
            
            // รหัสและชื่อวิชา
            $table->string('subject_code', 50)->unique(); // เช่น 01417311
            $table->string('subject_name', 255); // ชื่อวิชา  เช่น CS303 , CS403
            $table->text('description')->nullable(); // รายละเอียดวิชา
            
            // ภาคเรียน และ ปีการศึกษา
            $table->integer('semester'); // 1, 2, 3
            $table->integer('year'); // เช่น 2568
            
            // สถานะ
            $table->boolean('is_enabled')->default(true); // เปิด/ปิดใช้งาน
            
            // ช่วงเวลาเปิด-ปิด
            $table->dateTime('open_date'); // วันเริ่มเปิด
            $table->dateTime('close_date'); // วันสิ้นสุด
            
            $table->timestamps();
            
            // Indexes
            $table->index('subject_code');
            $table->index(['semester', 'year']);
            $table->index('is_enabled');
            $table->index('open_date');
            $table->index('close_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
