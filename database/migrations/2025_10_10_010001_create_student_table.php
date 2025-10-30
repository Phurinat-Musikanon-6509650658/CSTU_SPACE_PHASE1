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
        if (Schema::hasTable('student')) {
            return;
        }

        Schema::create('student', function (Blueprint $table) {
            $table->increments('student_id');
            $table->string('firstname_std', 255);
            $table->string('lastname_std', 255);
            $table->string('email_std', 255);
            $table->string('username_std', 50);
            $table->string('password_std', 255);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student');
    }
};
