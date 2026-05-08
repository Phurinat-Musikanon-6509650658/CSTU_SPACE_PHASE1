<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id('subject_id');
            $table->string('subject_code', 50)->unique();
            $table->string('subject_name', 255);
            $table->text('description')->nullable();
            $table->integer('semester');
            $table->integer('year');
            $table->boolean('is_enabled')->default(true);
            $table->dateTime('open_date')->nullable();
            $table->dateTime('close_date')->nullable();
            $table->dateTime('access_open_date')->nullable();
            $table->dateTime('access_close_date')->nullable();
            $table->dateTime('evaluation_open_date')->nullable();
            $table->dateTime('evaluation_close_date')->nullable();
            $table->dateTime('grade_edit_open_date')->nullable();
            $table->dateTime('grade_edit_close_date')->nullable();
            $table->timestamps();

            $table->index('subject_code');
            $table->index(['semester', 'year']);
            $table->index('is_enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
