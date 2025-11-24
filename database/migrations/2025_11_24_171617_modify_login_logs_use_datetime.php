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
        Schema::table('login_logs', function (Blueprint $table) {
            // เปลี่ยนจาก timestamp เป็น datetime เพื่อป้องกัน auto-update behavior ของ Laravel
            $table->datetime('login_time')->change();
            $table->datetime('logout_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            $table->timestamp('login_time')->change();
            $table->timestamp('logout_time')->nullable()->change();
        });
    }
};
