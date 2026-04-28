<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * รวม updates จาก:
     * - 2025_11_27_190457_add_department_to_student_table.php
     * - 2025_11_27_190940_update_student_type_to_varchar.php
     * - 2025_12_03_000001_add_course_and_semester_to_users_and_students.php
     */
    public function up(): void
    {
        Schema::table('student', function (Blueprint $table) {
            // เพิ่ม department column
            if (!Schema::hasColumn('student', 'department')) {
                $table->string('department', 255)->nullable()->after('email_std');
            }
            
            // เปลี่ยน student_type เป็น varchar เพื่อรองรับผสมระหว่าง regular/special
            if (!Schema::hasColumn('student', 'student_type')) {
                $table->string('student_type', 2)->nullable()->after('department')->comment('r = ภาคปกติ, s = ภาคพิเศษ, rs = ผสม');
            }
            
            // เพิ่ม course_code, semester, year (ล็อกรหัสวิชาและชั้นปีกับนักศึกษา)
            if (!Schema::hasColumn('student', 'course_code')) {
                $table->string('course_code', 20)->nullable()->after('email_std');
            }
            if (!Schema::hasColumn('student', 'semester')) {
                $table->tinyInteger('semester')->nullable()->after('course_code');
            }
            if (!Schema::hasColumn('student', 'year')) {
                $table->integer('year')->nullable()->after('semester'); // ปี พ.ศ. เช่น 2568
            }
        });

        // กำหนดค่าเริ่มต้นสำหรับ student ที่เก่า
        DB::statement("UPDATE student SET course_code = 'CS303', semester = 2, year = 2568 WHERE course_code IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student', function (Blueprint $table) {
            $columns = ['department', 'student_type', 'course_code', 'semester', 'year'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('student', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
