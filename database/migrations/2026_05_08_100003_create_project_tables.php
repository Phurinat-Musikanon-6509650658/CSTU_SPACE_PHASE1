<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ตารางระบุบทบาทของอาจารย์ในโครงงาน (advisor, committee, co-advisor)
        Schema::create('relationship_with_projects', function (Blueprint $table) {
            $table->id();
            $table->string('relationship', 100);
            $table->string('relationship_abbrev', 20);
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id('project_id');
            $table->unsignedBigInteger('group_id')->unique();
            $table->string('project_name')->nullable();
            $table->string('project_code')->unique();
            $table->datetime('exam_datetime')->nullable();
            $table->string('student_type', 2)->default('r')->comment('r=ภาคปกติ, s=ภาคพิเศษ, rs=ผสม');
            $table->enum('status_project', ['not_proposed', 'pending', 'approved', 'rejected', 'in_progress', 'late_submission', 'submitted'])->default('not_proposed');
            $table->text('project_type')->nullable();
            $table->string('submission_file')->nullable();
            $table->string('submission_original_name')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->string('submitted_by', 50)->nullable();
            $table->timestamps();

            $table->foreign('group_id')->references('group_id')->on('groups')->onDelete('cascade');
        });

        Schema::create('project_proposals', function (Blueprint $table) {
            $table->id('proposal_id');
            $table->unsignedBigInteger('group_id');
            $table->string('proposed_title');
            $table->text('description')->nullable();
            $table->string('proposed_to', 50);
            $table->string('proposed_by', 50);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('proposed_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->foreign('group_id')->references('group_id')->on('groups')->onDelete('cascade');
            $table->index(['group_id', 'status']);
            $table->index('proposed_to');
            $table->index('proposed_by');
        });

        Schema::create('project_evaluations', function (Blueprint $table) {
            $table->id('evaluation_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedInteger('student_id')->nullable();
            $table->string('evaluator_code', 50);
            $table->enum('evaluator_role', ['advisor', 'committee1', 'committee2', 'committee3']);
            $table->decimal('part1_score', 5, 2)->nullable()->default(0);  // 10 คะแนน (advisor only)
            $table->decimal('part2_score', 5, 2)->nullable()->default(0);  // 30 คะแนน
            $table->decimal('part3_score', 5, 2)->nullable()->default(0);  // 60 คะแนน (รวม)
            $table->decimal('part3a_score', 5, 2)->nullable()->default(0); // แบ่งย่อย 20
            $table->decimal('part3b_score', 5, 2)->nullable()->default(0); // แบ่งย่อย 20
            $table->decimal('part3c_score', 5, 2)->nullable()->default(0); // แบ่งย่อย 20
            $table->decimal('total_score', 5, 2)->nullable()->default(0);
            $table->text('comments')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            $table->foreign('student_id')->references('student_id')->on('student')->onDelete('cascade');
            $table->foreign('evaluator_code')->references('user_code')->on('user')->onDelete('cascade');
            $table->unique(['project_id', 'student_id', 'evaluator_code', 'evaluator_role'], 'unique_project_student_evaluator');
            $table->index('project_id');
            $table->index('evaluator_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_evaluations');
        Schema::dropIfExists('project_proposals');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('relationship_with_projects');
    }
};
