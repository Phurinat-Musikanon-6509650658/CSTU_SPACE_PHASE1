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
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->string('username'); // username ของผู้ login
            $table->string('user_type'); // 'user' หรือ 'student' 
            $table->unsignedBigInteger('user_id')->nullable(); // ID จาก table user
            $table->unsignedBigInteger('student_id')->nullable(); // ID จาก table student
            $table->string('role'); // admin, coordinator, advisor, student
            $table->string('ip_address'); // IP address ของผู้ login
            $table->text('user_agent'); // Browser/Device information
            $table->enum('login_status', ['success', 'failed']); // สถานะการ login
            $table->string('failure_reason')->nullable(); // เหตุผลที่ login ไม่สำเร็จ
            $table->datetime('login_time'); // เวลาที่ login (ใช้ datetime แทน timestamp)
            $table->datetime('logout_time')->nullable(); // เวลาที่ logout (null หากยังไม่ logout)
            $table->integer('session_duration')->nullable(); // ระยะเวลาใช้งาน (วินาที)
            $table->timestamps();
            
            // Index สำหรับการค้นหา
            $table->index(['username', 'login_time']);
            $table->index(['role', 'login_time']);
            $table->index(['login_status', 'login_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
