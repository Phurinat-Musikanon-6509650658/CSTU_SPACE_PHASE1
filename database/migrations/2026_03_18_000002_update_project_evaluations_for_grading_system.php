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
            // Add new score columns for part1, part2, part3
            $table->decimal('part1_score', 5, 2)->nullable()->default(0)->after('evaluator_role'); // ส่วนที่ 1: 10 คะแนน (advisor only)
            $table->dropColumn(['document_score', 'presentation_score']); // Remove old columns
        });
        
        // Add part2 and part3 columns
        Schema::table('project_evaluations', function (Blueprint $table) {
            $table->decimal('part2_score', 5, 2)->nullable()->default(0)->after('part1_score'); // ส่วนที่ 2: 30 คะแนน
            $table->decimal('part3_score', 5, 2)->nullable()->default(0)->after('part2_score'); // ส่วนที่ 3: 60 คะแนน
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_evaluations', function (Blueprint $table) {
            $table->dropColumn(['part1_score', 'part2_score', 'part3_score']);
            $table->decimal('document_score', 5, 2)->nullable()->default(0);
            $table->decimal('presentation_score', 5, 2)->nullable()->default(0);
        });
    }
};
