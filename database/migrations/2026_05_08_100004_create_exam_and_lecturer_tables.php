<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_schedule', function (Blueprint $table) {
            $table->id('ex_id');
            $table->unsignedBigInteger('project_id');
            $table->dateTime('ex_start_time');
            $table->dateTime('ex_end_time');
            $table->string('location', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            $table->index('project_id');
            $table->index('ex_start_time');
        });

        // pivot: อาจารย์ ↔ โครงงาน พร้อมบทบาท (relationship_id=1 advisor, 2 committee, 3-4 co-advisor)
        Schema::create('project_lecturers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('user_code', 50);
            $table->unsignedBigInteger('relationship_id');
            $table->unsignedTinyInteger('sort_order')->default(1);
            $table->timestamps();

            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            $table->foreign('user_code')->references('user_code')->on('user')->onDelete('cascade');
            $table->foreign('relationship_id')->references('id')->on('relationship_with_projects')->onDelete('cascade');
            $table->unique(['project_id', 'user_code', 'relationship_id'], 'unique_project_lecturer_role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_lecturers');
        Schema::dropIfExists('exam_schedule');
    }
};
