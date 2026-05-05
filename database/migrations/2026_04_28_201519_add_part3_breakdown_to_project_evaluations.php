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
            $table->decimal('part3a_score', 5, 2)->nullable()->default(0)->after('part3_score');
            $table->decimal('part3b_score', 5, 2)->nullable()->default(0)->after('part3a_score');
            $table->decimal('part3c_score', 5, 2)->nullable()->default(0)->after('part3b_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_evaluations', function (Blueprint $table) {
            $table->dropColumn(['part3a_score', 'part3b_score', 'part3c_score']);
        });
    }
};
