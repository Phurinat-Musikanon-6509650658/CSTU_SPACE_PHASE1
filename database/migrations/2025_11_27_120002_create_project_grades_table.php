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
        Schema::create('project_grades', function (Blueprint $table) {
            $table->id('grade_id');
            $table->unsignedBigInteger('project_id')->unique();
            
            // คะแนนรวมและเกรด
            $table->decimal('final_score', 5, 2)->nullable(); // คะแนนเฉลี่ยจากทุกคน
            $table->string('grade', 2)->nullable(); // A, B+, B, C+, C, D+, D, F
            
            // สถานะการยืนยันจากแต่ละคน
            $table->boolean('advisor_confirmed')->default(false);
            $table->timestamp('advisor_confirmed_at')->nullable();
            
            $table->boolean('committee1_confirmed')->default(false);
            $table->timestamp('committee1_confirmed_at')->nullable();
            
            $table->boolean('committee2_confirmed')->default(false);
            $table->timestamp('committee2_confirmed_at')->nullable();
            
            $table->boolean('committee3_confirmed')->default(false);
            $table->timestamp('committee3_confirmed_at')->nullable();
            
            // สถานะการยืนยันทั้งหมด
            $table->boolean('all_confirmed')->default(false);
            $table->timestamp('all_confirmed_at')->nullable();
            
            // ส่งเกรดให้นักศึกษาแล้ว
            $table->boolean('grade_released')->default(false);
            $table->timestamp('grade_released_at')->nullable();
            
            $table->timestamps();
            
            // Foreign key
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            
            // Index
            $table->index('all_confirmed');
            $table->index('grade_released');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_grades');
    }
};
