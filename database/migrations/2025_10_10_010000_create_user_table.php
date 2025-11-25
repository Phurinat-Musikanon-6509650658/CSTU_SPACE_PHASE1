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
        if (Schema::hasTable('user')) {
            return;
        }

        Schema::create('user', function (Blueprint $table) {
            $table->increments('user_id');
            $table->string('firstname_user', 255);
            $table->string('lastname_user', 255);
            $table->string('user_code', 50)->unique();
            $table->integer('role');  // Changed from string to integer for role_code
            $table->string('email_user', 255);
            $table->string('username_user', 50);
            $table->string('password_user', 255);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
