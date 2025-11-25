<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // อัพเดต groups table - สถานะกลุ่ม
        DB::statement("ALTER TABLE `groups` MODIFY `status_group` ENUM('not_created', 'created', 'member_left', 'member_added', 'disbanded') DEFAULT 'created'");
        
        // อัพเดต projects table - สถานะโครงงาน (ไม่ใช่ project_proposals)
        DB::statement("ALTER TABLE `projects` MODIFY `status_project` ENUM('not_proposed', 'pending', 'approved', 'rejected', 'in_progress', 'late_submission', 'submitted') DEFAULT 'pending'");
        
        // project_proposals ใช้แค่ pending, approved, rejected
        DB::statement("ALTER TABLE `project_proposals` MODIFY `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `groups` MODIFY `status_group` ENUM('created', 'pending', 'approved', 'rejected') DEFAULT 'created'");
        DB::statement("ALTER TABLE `projects` MODIFY `status_project` VARCHAR(255) DEFAULT 'pending'");
        DB::statement("ALTER TABLE `project_proposals` MODIFY `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");
    }
};
