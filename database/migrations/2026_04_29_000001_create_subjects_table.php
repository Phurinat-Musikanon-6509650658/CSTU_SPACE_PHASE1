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
            $table->boolean('is_enabled')->default(true);

            // ช่วงเวลาเปิด-ปิดรายวิชาโดยรวม
            $table->dateTime('open_date')->nullable();
            $table->dateTime('close_date')->nullable();

            // ช่วงเวลาเข้าใช้งานระบบ (นักศึกษา/ผู้ใช้ นอกจาก admin ถ้าปิดจะ lock)
            $table->dateTime('access_open_date')->nullable();
            $table->dateTime('access_close_date')->nullable();

            // ช่วงเวลาประเมินคะแนน (อาจารย์ส่งคะแนนได้ในช่วงนี้)
            $table->dateTime('evaluation_open_date')->nullable();
            $table->dateTime('evaluation_close_date')->nullable();

            // ช่วงเวลาแก้ไขคะแนน (เมื่อเกินกำหนดจะไม่สามารถแก้ไขได้)
            $table->dateTime('grade_edit_open_date')->nullable();
            $table->dateTime('grade_edit_close_date')->nullable();

            $table->timestamps();

            $table->index('subject_code');
            $table->index(['semester', 'year']);
            $table->index('is_enabled');
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
