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
        // roles table มี role เป็น primary key อยู่แล้ว ไม่ต้องแก้ไข

        // เพิ่ม foreign key constraint ใน user table
        Schema::table('user', function (Blueprint $table) {
            $table->foreign('role')->references('role')->on('roles')
                  ->onUpdate('cascade')->onDelete('restrict');
        });

        // เพิ่ม foreign key constraint ใน student table  
        Schema::table('student', function (Blueprint $table) {
            $table->foreign('role')->references('role')->on('roles')
                  ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ลบ foreign key constraints
        Schema::table('user', function (Blueprint $table) {
            $table->dropForeign(['role']);
        });

        Schema::table('student', function (Blueprint $table) {
            $table->dropForeign(['role']);
        });
        
        // ไม่ต้องคืนค่า roles table เนื่องจากไม่ได้แก้ไขอะไร
    }
};
