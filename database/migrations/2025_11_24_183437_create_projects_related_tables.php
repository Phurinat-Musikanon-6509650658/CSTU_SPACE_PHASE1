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
        // สร้างตาราง projects
        Schema::create('projects', function (Blueprint $table) {
            $table->id('project_id');
            $table->unsignedBigInteger('group_id')->unique();
            
            // ข้อมูลโครงงาน
            $table->string('project_name')->nullable(); // ชื่อโครงงาน (อัพเดตหลังนักศึกษากรอกเสร็จ)
            $table->string('project_code')->unique(); // 68-1-01_kdc-r1
            
            // อาจารย์ที่ปรึกษาและกรรมการ (เก็บ user_code เช่น ksa, ppr)
            $table->string('advisor_code', 50)->nullable(); // user_code ของอาจารย์ที่ปรึกษา
            $table->string('committee1_code', 50)->nullable();
            $table->string('committee2_code', 50)->nullable();
            $table->string('committee3_code', 50)->nullable();
            
            // วันเวลาสอบ
            $table->datetime('exam_datetime')->nullable();
            
            // ประเภทนักศึกษา (r=ปกติ, s=พิเศษ)
            $table->enum('student_type', ['r', 's'])->default('r');
            
            // สถานะโปรเจ็ค
            $table->enum('status_project', ['not_proposed', 'pending', 'approved', 'rejected', 'in_progress', 'late_submission', 'submitted'])->default('not_proposed');
            
            // ประเภทโปรเจ็ค (สามารถผสมได้ เช่น soft-en,ai หรือ network,datasci)
            $table->text('project_type')->nullable(); // soft-en, network, datasci, ai, etc. (comma-separated)
            
            // ไฟล์รายงาน PDF
            $table->string('submission_file')->nullable();
            $table->string('submission_original_name')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->string('submitted_by', 50)->nullable(); // username_std ของคนส่ง
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('group_id')->references('group_id')->on('groups')->onDelete('cascade');
            $table->foreign('advisor_code')->references('user_code')->on('user')->onDelete('set null');
            $table->foreign('committee1_code')->references('user_code')->on('user')->onDelete('set null');
            $table->foreign('committee2_code')->references('user_code')->on('user')->onDelete('set null');
            $table->foreign('committee3_code')->references('user_code')->on('user')->onDelete('set null');
        });

        // สร้างตาราง project_proposals
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
            
            // Index สำหรับ username columns
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
        Schema::dropIfExists('projects');
    }
};
