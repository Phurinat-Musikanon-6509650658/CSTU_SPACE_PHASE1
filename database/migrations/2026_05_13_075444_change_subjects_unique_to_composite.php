<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique('subjects_subject_code_unique');
            $table->unique(['subject_code', 'year', 'semester'], 'subjects_code_year_semester_unique');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique('subjects_code_year_semester_unique');
            $table->unique('subject_code');
        });
    }
};
