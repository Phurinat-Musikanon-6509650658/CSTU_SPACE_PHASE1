<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add multiple columns to student table
        Schema::table('student', function (Blueprint $table) {
            // จาก migration 2025_11_27_190457
            if (!Schema::hasColumn('student', 'department')) {
                $table->string('department', 255)->nullable()->after('email_std');
            }
            if (!Schema::hasColumn('student', 'student_type')) {
                $table->char('student_type', 1)->nullable()->after('department')->comment('r = ภาคปกติ, s = ภาคพิเศษ');
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

        // Set default values for existing students
        DB::statement("UPDATE student SET course_code = 'CS303', semester = 2, year = 2568 WHERE course_code IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student', function (Blueprint $table) {
            $columns = ['course_code', 'semester', 'year', 'department', 'student_type'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('student', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
