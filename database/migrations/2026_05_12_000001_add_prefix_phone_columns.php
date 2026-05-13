<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student', function (Blueprint $table) {
            $table->string('prefix_std', 20)->nullable()->after('student_id');
            $table->string('phone_std', 20)->nullable()->after('email_std');
        });

        Schema::table('user', function (Blueprint $table) {
            $table->string('prefix_user', 20)->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('student', function (Blueprint $table) {
            $table->dropColumn(['prefix_std', 'phone_std']);
        });

        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('prefix_user');
        });
    }
};
