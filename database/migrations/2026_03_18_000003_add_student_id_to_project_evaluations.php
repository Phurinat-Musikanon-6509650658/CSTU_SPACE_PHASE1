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
        Schema::table('project_evaluations', function (Blueprint $table) {
            // Add student_id foreign key for per-student evaluation
            $table->unsignedInteger('student_id')->nullable()->after('project_id');
            $table->foreign('student_id')->references('student_id')->on('student')->onDelete('cascade');
            
            // Update unique constraint to include student_id
            $table->unique(['project_id', 'student_id', 'evaluator_code', 'evaluator_role'], 'unique_project_student_evaluator');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_evaluations', function (Blueprint $table) {
            $table->dropUnique('unique_project_student_evaluator');
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');
        });
    }
};
