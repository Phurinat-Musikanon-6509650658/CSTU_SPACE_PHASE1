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
        Schema::create('project_proposals', function (Blueprint $table) {
            $table->id('proposal_id');
            $table->unsignedBigInteger('group_id');
            
            // ข้อมูลข้อเสนอ
            $table->string('proposed_title'); // ชื่อโครงงานที่เสนอ
            $table->text('description')->nullable(); // รายละเอียด
            $table->string('proposed_to', 50); // username_user ของ lecturer ที่เสนอไป
            $table->string('proposed_by', 50); // username_std ของหัวหน้ากลุ่มที่เสนอ
            
            // สถานะข้อเสนอ
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable(); // เหตุผลที่ปฏิเสธ (ถ้ามี)
            
            // วันที่ดำเนินการ
            $table->timestamp('proposed_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('group_id')->references('group_id')->on('groups')->onDelete('cascade');
            
            // Index สำหรับ username columns แทน foreign key เนื่องจาก username ไม่ได้เป็น primary/unique key
            $table->index(['group_id', 'status']);
            $table->index('proposed_to');
            $table->index('proposed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_proposals');
    }
};
