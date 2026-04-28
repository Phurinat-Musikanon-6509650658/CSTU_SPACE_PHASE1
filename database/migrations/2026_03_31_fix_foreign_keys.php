<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix foreign key constraints for projects tables
     */
    public function up(): void
    {
        // ปรับปรุง project_proposals เพื่อ cascade delete
        if (Schema::hasTable('project_proposals')) {
            Schema::table('project_proposals', function (Blueprint $table) {
                // Drop old foreign key ถ้ามี
                try {
                    $table->dropForeign(['group_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
            });
            
            Schema::table('project_proposals', function (Blueprint $table) {
                $table->foreign('group_id')->references('group_id')->on('groups')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('project_proposals')) {
            Schema::table('project_proposals', function (Blueprint $table) {
                try {
                    $table->dropForeign(['group_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
            });
        }
    }
};
