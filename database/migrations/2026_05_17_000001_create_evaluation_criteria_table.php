<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('subject_code', 50)->unique();
            $table->string('part1_label', 255)->default('ความก้าวหน้าโครงงาน');
            $table->unsignedTinyInteger('part1_max')->default(10);
            $table->string('part2_label', 255)->default('คุณภาพของรายงาน');
            $table->unsignedTinyInteger('part2_max')->default(30);
            $table->string('part3a_label', 255);
            $table->unsignedTinyInteger('part3a_max')->default(20);
            $table->string('part3b_label', 255)->default('คุณภาพการนำเสนอและการตอบคำถาม');
            $table->unsignedTinyInteger('part3b_max')->default(20);
            $table->string('part3c_label', 255)->default('การประยุกต์ใช้ความรู้ทางวิทยาการคอมพิวเตอร์อย่างเหมาะสมในการนำเสนอโครงงาน');
            $table->unsignedTinyInteger('part3c_max')->default(20);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_criteria');
    }
};
