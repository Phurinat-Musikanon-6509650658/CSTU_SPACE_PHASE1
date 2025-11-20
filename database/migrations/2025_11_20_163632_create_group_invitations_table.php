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
        Schema::create('group_invitations', function (Blueprint $table) {
            $table->id('invitation_id');
            $table->unsignedBigInteger('group_id');
            $table->string('inviter_username', 50); // ผู้เชิญ (username_std)
            $table->string('invitee_username', 50); // ผู้ถูกเชิญ (username_std)
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->text('message')->nullable(); // ข้อความแนบ
            $table->timestamp('responded_at')->nullable(); // วันที่ตอบรับ
            $table->timestamps();

            // Foreign key constraint เฉพาะ groups table
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
    }
};
