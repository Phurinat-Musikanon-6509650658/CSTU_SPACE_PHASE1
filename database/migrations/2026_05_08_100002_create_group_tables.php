<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id('group_id');
            $table->integer('year');
            $table->tinyInteger('semester');
            $table->string('subject_code');
            $table->enum('status_group', ['not_created', 'created', 'pending', 'approved', 'rejected', 'member_left', 'member_added', 'disbanded'])->default('created');
            $table->timestamps();
        });

        Schema::create('group_members', function (Blueprint $table) {
            $table->id('groupmem_id');
            $table->unsignedBigInteger('group_id');
            $table->string('username_std', 50);
            $table->timestamps();

            $table->foreign('group_id')->references('group_id')->on('groups')->onDelete('cascade');
            $table->unique(['group_id', 'username_std']);
        });

        Schema::create('group_invitations', function (Blueprint $table) {
            $table->id('invitation_id');
            $table->unsignedBigInteger('group_id');
            $table->string('inviter_username', 50);
            $table->string('invitee_username', 50);
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->text('message')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->foreign('group_id')->references('group_id')->on('groups')->onDelete('cascade');
            $table->unique(['group_id', 'invitee_username']);
            $table->index(['invitee_username', 'status']);
            $table->index('inviter_username');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_invitations');
        Schema::dropIfExists('group_members');
        Schema::dropIfExists('groups');
    }
};
