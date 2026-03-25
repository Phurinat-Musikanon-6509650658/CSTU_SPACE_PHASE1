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
