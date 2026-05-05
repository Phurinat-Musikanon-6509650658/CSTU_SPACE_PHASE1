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
        // สร้างตาราง exam_schedule
        Schema::create('exam_schedule', function (Blueprint $table) {
            $table->id('schedule_id');
            $table->unsignedBigInteger('project_id');
            
            // ผู้ประเมิน
            $table->string('advisor_code', 50)->nullable();
            $table->string('committee1_code', 50)->nullable();
            $table->string('committee2_code', 50)->nullable();
            $table->string('committee3_code', 50)->nullable();
            
            // วันเวลา
            $table->dateTime('exam_date')->nullable();
            $table->string('location', 255)->nullable();
            
            // สถานะ
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            
            // หมายเหตุ
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            $table->foreign('advisor_code')->references('user_code')->on('user')->onDelete('set null')->nullable();
            $table->foreign('committee1_code')->references('user_code')->on('user')->onDelete('set null')->nullable();
            $table->foreign('committee2_code')->references('user_code')->on('user')->onDelete('set null')->nullable();
            $table->foreign('committee3_code')->references('user_code')->on('user')->onDelete('set null')->nullable();
            
            // Index
            $table->index('project_id');
            $table->index('exam_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_schedule');
    }
};
