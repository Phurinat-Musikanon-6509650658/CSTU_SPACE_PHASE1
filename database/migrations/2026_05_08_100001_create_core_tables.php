<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('firstname_user', 255);
            $table->string('lastname_user', 255);
            $table->string('user_code', 50)->unique();
            $table->integer('role');
            $table->string('email_user', 255);
            $table->string('username_user', 50);
            $table->string('password_user', 255);
        });

        Schema::create('student', function (Blueprint $table) {
            $table->increments('student_id');
            $table->string('firstname_std', 255);
            $table->string('lastname_std', 255);
            $table->string('email_std', 255);
            $table->integer('role')->default(2048);
            $table->string('username_std', 50);
            $table->string('password_std', 255);
            $table->string('department', 255)->nullable();
            $table->string('student_type', 2)->nullable()->comment('r=ภาคปกติ, s=ภาคพิเศษ, rs=ผสม');
            $table->string('course_code', 20)->nullable();
            $table->tinyInteger('semester')->nullable();
            $table->integer('year')->nullable();
        });

        Schema::create('user_role', function (Blueprint $table) {
            $table->id('role_id');
            $table->string('role_name', 50)->unique();
            $table->integer('role_code')->unique();
            $table->bigInteger('role_code_bin')->unsigned();
            $table->timestamps();
        });

        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('user_type');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('role');
            $table->string('ip_address');
            $table->text('user_agent');
            $table->enum('login_status', ['success', 'failed']);
            $table->string('failure_reason')->nullable();
            $table->datetime('login_time');
            $table->datetime('logout_time')->nullable();
            $table->integer('session_duration')->nullable();
            $table->timestamps();

            $table->index(['username', 'login_time']);
            $table->index(['role', 'login_time']);
            $table->index(['login_status', 'login_time']);
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('login_logs');
        Schema::dropIfExists('user_role');
        Schema::dropIfExists('student');
        Schema::dropIfExists('user');
    }
};
