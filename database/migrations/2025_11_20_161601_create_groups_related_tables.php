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
            $table->integer('year'); // ใช้ integer เพื่อรองรับปี พ.ศ. (2568)
            $table->tinyInteger('semester');
            $table->string('subject_code');
            $table->enum('status_group', ['not_created', 'created', 'member_left', 'member_added', 'disbanded'])->default('created');
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
            
            // Unique constraint to prevent duplicate memberships
            $table->unique(['group_id', 'username_std']);
        });

        // สร้างตาราง group_invitations
        Schema::create('group_invitations', function (Blueprint $table) {
            $table->id('invitation_id');
            $table->unsignedBigInteger('group_id');
            $table->string('inviter_username', 50); // ผู้เชิญ (username_std)
            $table->string('invitee_username', 50); // ผู้ถูกเชิญ (username_std)
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->text('message')->nullable(); // ข้อความแนบ
            $table->timestamp('responded_at')->nullable(); // วันที่ตอบรับ
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('group_id')->references('group_id')->on('groups')->onDelete('cascade');
            
            // Unique constraint เพื่อป้องกันการเชิญซ้ำในกลุ่มเดียวกัน
            $table->unique(['group_id', 'invitee_username']);
            
            // Index เพื่อค้นหาได้เร็ว
            $table->index(['invitee_username', 'status']);
            $table->index(['inviter_username']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_invitations');
        Schema::dropIfExists('group_members');
        Schema::dropIfExists('groups');
    }
};
