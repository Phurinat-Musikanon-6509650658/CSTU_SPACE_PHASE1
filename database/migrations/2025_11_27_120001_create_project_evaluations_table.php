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
        Schema::create('project_evaluations', function (Blueprint $table) {
            $table->id('evaluation_id');
            $table->unsignedBigInteger('project_id');
            
            // อาจารย์/กรรมการที่ให้คะแนน
            $table->string('evaluator_code', 50); // user_code ของอาจารย์
            $table->enum('evaluator_role', ['advisor', 'committee1', 'committee2', 'committee3']);
            
            // คะแนน
            $table->decimal('document_score', 5, 2)->nullable()->default(0); // คะแนนรูปเล่ม 0-30
            $table->decimal('presentation_score', 5, 2)->nullable()->default(0); // คะแนนพรีเซนต์ 0-70
            $table->decimal('total_score', 5, 2)->nullable()->default(0); // รวม 0-100
            
            // ความเห็นเพิ่มเติม
            $table->text('comments')->nullable();
            
            // วันที่ส่งคะแนน
            $table->timestamp('submitted_at')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            $table->foreign('evaluator_code')->references('user_code')->on('user')->onDelete('cascade');
            
            // ป้องกันการให้คะแนนซ้ำ (1 project, 1 evaluator, 1 role = 1 คะแนน)
            $table->unique(['project_id', 'evaluator_code', 'evaluator_role'], 'proj_eval_unique');
            
            // Index
            $table->index('project_id');
            $table->index('evaluator_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_evaluations');
    }
};
