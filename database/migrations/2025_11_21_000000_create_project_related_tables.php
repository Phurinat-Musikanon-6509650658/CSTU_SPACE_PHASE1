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
            
            // ประเภทนักศึกษา (r=ปกติ, s=พิเศษ, rs=ผสม)
            $table->string('student_type', 2)->default('r')->comment('r=ภาคปกติ, s=ภาคพิเศษ, rs=ผสม');
            
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

        // สร้างตาราง project_evaluations
        Schema::create('project_evaluations', function (Blueprint $table) {
            $table->id('evaluation_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedInteger('student_id')->nullable(); // per-student evaluation
            
            // อาจารย์/กรรมการที่ให้คะแนน
            $table->string('evaluator_code', 50); // user_code ของอาจารย์
            $table->enum('evaluator_role', ['advisor', 'committee1', 'committee2', 'committee3']);
            
            // คะแนนแบ่ง 3 ส่วน
            $table->decimal('part1_score', 5, 2)->nullable()->default(0); // ส่วนที่ 1: 10 คะแนน (advisor only)
            $table->decimal('part2_score', 5, 2)->nullable()->default(0); // ส่วนที่ 2: 30 คะแนน
            $table->decimal('part3_score', 5, 2)->nullable()->default(0); // ส่วนที่ 3: 60 คะแนน
            $table->decimal('total_score', 5, 2)->nullable()->default(0); // รวม 0-100
            
            // ความเห็นเพิ่มเติม
            $table->text('comments')->nullable();
            
            // วันที่ส่งคะแนน
            $table->timestamp('submitted_at')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            $table->foreign('student_id')->references('student_id')->on('student')->onDelete('cascade')->nullable();
            $table->foreign('evaluator_code')->references('user_code')->on('user')->onDelete('cascade');
            
            // ป้องกันการให้คะแนนซ้ำ (1 project, 1 student, 1 evaluator, 1 role = 1 คะแนน)
            $table->unique(['project_id', 'student_id', 'evaluator_code', 'evaluator_role'], 'unique_project_student_evaluator');
            
            // Index
            $table->index('project_id');
            $table->index('evaluator_code');
        });

        // สร้างตาราง project_grades
        Schema::create('project_grades', function (Blueprint $table) {
            $table->id('grade_id');
            $table->unsignedBigInteger('project_id')->unique();
            
            // คะแนนรวมและเกรด
            $table->decimal('final_score', 5, 2)->nullable(); // คะแนนเฉลี่ยจากทุกคน
            $table->string('grade', 2)->nullable(); // A, B+, B, C+, C, D+, D, F
            
            // สถานะการยืนยันจากแต่ละคน
            $table->boolean('advisor_confirmed')->default(false);
            $table->timestamp('advisor_confirmed_at')->nullable();
            
            $table->boolean('committee1_confirmed')->default(false);
            $table->timestamp('committee1_confirmed_at')->nullable();
            
            $table->boolean('committee2_confirmed')->default(false);
            $table->timestamp('committee2_confirmed_at')->nullable();
            
            $table->boolean('committee3_confirmed')->default(false);
            $table->timestamp('committee3_confirmed_at')->nullable();
            
            // สถานะการยืนยันทั้งหมด
            $table->boolean('all_confirmed')->default(false);
            $table->timestamp('all_confirmed_at')->nullable();
            
            // ส่งเกรดให้นักศึกษาแล้ว
            $table->boolean('grade_released')->default(false);
            $table->timestamp('grade_released_at')->nullable();
            
            $table->timestamps();
            
            // Foreign key
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            
            // Index
            $table->index('all_confirmed');
            $table->index('grade_released');
        });

        // สร้างตาราง relationship_with_projects
        Schema::create('relationship_with_projects', function (Blueprint $table) {
            $table->id();
            $table->string('relationship', 100);
            $table->string('relationship_abbrev', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relationship_with_projects');
        Schema::dropIfExists('project_grades');
        Schema::dropIfExists('project_evaluations');
        Schema::dropIfExists('project_proposals');
        Schema::dropIfExists('projects');
    }
};
