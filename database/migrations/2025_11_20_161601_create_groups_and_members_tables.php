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
        // สร้างตาราง groups
        Schema::create('groups', function (Blueprint $table) {
            $table->id('group_id');
            $table->string('project_name');
            $table->string('project_code')->unique();
            $table->string('subject_code');
            $table->year('year');
            $table->tinyInteger('semester');
            $table->string('status_group')->default('active');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // สร้างตาราง group_members
        Schema::create('group_members', function (Blueprint $table) {
            $table->id('groupmem_id');
            $table->unsignedBigInteger('group_id');
            $table->string('username_std', 50);
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('group_id')->references('group_id')->on('groups')->onDelete('cascade');
            // Note: username_std references student table but we'll add this constraint later if needed
            
            // Unique constraint to prevent duplicate memberships
            $table->unique(['group_id', 'username_std']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_members');
        Schema::dropIfExists('groups');
    }
};
