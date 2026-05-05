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
        Schema::table('subjects', function (Blueprint $table) {
            // ช่วงเวลาการเปิด-ปิดรายวิชา (สำหรับ Student/User)
            $table->dateTime('access_open_date')->nullable()->after('close_date')->comment('วันเวลาที่เปิดการเข้าใช้ (ก่อนนี้จะ locked)');
            $table->dateTime('access_close_date')->nullable()->after('access_open_date')->comment('วันเวลาที่ปิดการเข้าใช้ (หลังนี้จะ locked สำหรับ student)');
            
            // ช่วงเวลาประเมินคะแนนของอาจารย์
            $table->dateTime('evaluation_open_date')->nullable()->after('access_close_date')->comment('วันเวลาเปิดให้อาจารย์เริ่มประเมินคะแนน');
            $table->dateTime('evaluation_close_date')->nullable()->after('evaluation_open_date')->comment('วันเวลาปิดการประเมินคะแนน (หลังนี้ไม่สามารถส่งคะแนนได้)');
            
            // ช่วงเวลาแก้ไขคะแนนของอาจารย์
            $table->dateTime('grade_edit_open_date')->nullable()->after('evaluation_close_date')->comment('วันเวลาเปิดให้อาจารย์แก้ไขคะแนน');
            $table->dateTime('grade_edit_close_date')->nullable()->after('grade_edit_open_date')->comment('วันเวลาปิดการแก้ไขคะแนน (หลังนี้ไม่สามารถแก้ไขได้)');
            
            // เพิ่ม indexes สำหรับเวลา
            $table->index('access_open_date');
            $table->index('access_close_date');
            $table->index('evaluation_open_date');
            $table->index('evaluation_close_date');
            $table->index('grade_edit_open_date');
            $table->index('grade_edit_close_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn([
                'access_open_date',
                'access_close_date',
                'evaluation_open_date',
                'evaluation_close_date',
                'grade_edit_open_date',
                'grade_edit_close_date',
            ]);
            
            $table->dropIndex(['access_open_date']);
            $table->dropIndex(['access_close_date']);
            $table->dropIndex(['evaluation_open_date']);
            $table->dropIndex(['evaluation_close_date']);
            $table->dropIndex(['grade_edit_open_date']);
            $table->dropIndex(['grade_edit_close_date']);
        });
    }
};
