<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * รวม updates จาก:
     * - 2026_03_18_000002_update_project_evaluations_for_grading_system.php (part scores)
     * - 2026_03_18_000003_add_student_id_to_project_evaluations.php
     * - 2026_03_18_000004_create_student_grades_table.php
     */
    public function up(): void
    {
        // เพิ่ม student_grades table สำหรับเก็บเกรดของแต่ละนักศึกษา
        Schema::create('student_grades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedInteger('student_id');
            $table->decimal('final_score', 5, 2)->nullable();
            $table->string('grade', 2)->nullable();
            $table->boolean('released')->default(false);
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            $table->foreign('student_id')->references('student_id')->on('student')->onDelete('cascade');
            $table->unique(['project_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_grades');
    }
};
