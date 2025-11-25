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
        Schema::create('projects', function (Blueprint $table) {
            $table->id('project_id');
            $table->unsignedBigInteger('group_id')->unique();
            
            // ข้อมูลโครงงาน
            $table->string('project_name')->nullable(); // ชื่อโครงงาน (อัพเดตหลังนักศึกษากรอกเสร็จ)
            $table->string('project_code')->unique(); // 68-1-01_kdc-r1
            
            // อาจารย์ที่ปรึกษาและกรรมการ (เก็บ user_code เช่น ksa, ppr)
            $table->string('advisor_code', 50)->nullable(); // user_code ของอาจารย์ที่ปรึกษา
            $table->string('committee1_code', 50)->nullable();
            $table->string('committee2_code', 50)->nullable();
            $table->string('committee3_code', 50)->nullable();
            
            // วันเวลาสอบ
            $table->datetime('exam_datetime')->nullable();
            
            // ประเภทนักศึกษา (r=ปกติ, s=พิเศษ)
            $table->enum('student_type', ['r', 's'])->default('r');
            
            // สถานะโปรเจ็ค (กำลังดำเนินงาน, ส่งตรงเวลา, สมาชิกออก, เพิ่มสมาชิก, ฯลฯ)
            $table->string('status_project')->default('pending'); // pending, in_progress, submitted, late, member_left, member_added, completed, etc.
            
            // ประเภทโปรเจ็ค (สามารถผสมได้ เช่น soft-en,ai หรือ network,datasci)
            $table->text('project_type')->nullable(); // soft-en, network, datasci, ai, etc. (comma-separated)
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('group_id')->references('group_id')->on('groups')->onDelete('cascade');
            $table->foreign('advisor_code')->references('user_code')->on('user')->onDelete('set null');
            $table->foreign('committee1_code')->references('user_code')->on('user')->onDelete('set null');
            $table->foreign('committee2_code')->references('user_code')->on('user')->onDelete('set null');
            $table->foreign('committee3_code')->references('user_code')->on('user')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
