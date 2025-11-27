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
        Schema::table('student', function (Blueprint $table) {
            $table->string('department', 255)->nullable()->after('email_std');
            $table->char('student_type', 1)->nullable()->after('department')->comment('r = ภาคปกติ, s = ภาคพิเศษ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student', function (Blueprint $table) {
            $table->dropColumn(['department', 'student_type']);
        });
    }
};
