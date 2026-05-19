<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE projects MODIFY COLUMN status_project ENUM(
            'not_proposed','pending','approved','rejected',
            'in_progress','late_submission','submitted','passed','failed'
        ) NOT NULL DEFAULT 'not_proposed'");

        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_project_id')->nullable()->after('project_id');
            $table->foreign('parent_project_id')->references('project_id')->on('projects')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['parent_project_id']);
            $table->dropColumn('parent_project_id');
        });

        DB::statement("ALTER TABLE projects MODIFY COLUMN status_project ENUM(
            'not_proposed','pending','approved','rejected',
            'in_progress','late_submission','submitted'
        ) NOT NULL DEFAULT 'not_proposed'");
    }
};
