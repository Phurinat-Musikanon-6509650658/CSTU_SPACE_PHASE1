<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AddPasswordHashColumns extends Migration
{
    /**
     * Run the migrations.
     * Adds password_user_hash and password_std_hash and copies/creates hashes where possible.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user', function (Blueprint $table) {
            if (!Schema::hasColumn('user', 'password_user_hash')) {
                $table->string('password_user_hash', 255)->nullable()->after('password_user');
            }
        });

        Schema::table('student', function (Blueprint $table) {
            if (!Schema::hasColumn('student', 'password_std_hash')) {
                $table->string('password_std_hash', 255)->nullable()->after('password_std');
            }
        });

        // Populate hash columns: if existing value looks like a hash, copy it; otherwise create a hash from the stored value.
        $users = DB::table('user')->get();
        foreach ($users as $u) {
            $existing = $u->password_user ?? null;
            if ($existing) {
                $info = password_get_info($existing);
                if (!empty($info['algo'])) {
                    $hash = $existing; // already a hash
                } else {
                    $hash = Hash::make($existing);
                }
                DB::table('user')->where('user_id', $u->user_id)->update(['password_user_hash' => $hash]);
            }
        }

        $students = DB::table('student')->get();
        foreach ($students as $s) {
            $existing = $s->password_std ?? null;
            if ($existing) {
                $info = password_get_info($existing);
                if (!empty($info['algo'])) {
                    $hash = $existing;
                } else {
                    $hash = Hash::make($existing);
                }
                DB::table('student')->where('student_id', $s->student_id)->update(['password_std_hash' => $hash]);
            }
        }
    }

    /**
     * Reverse the migrations.
     * Removes the added hash columns.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user', function (Blueprint $table) 
        {
            if (Schema::hasColumn('user', 'password_user_hash')) {
                $table->dropColumn('password_user_hash');
            }
        });

        Schema::table('student', function (Blueprint $table) 
        {
            if (Schema::hasColumn('student', 'password_std_hash')) {
                $table->dropColumn('password_std_hash');
            }
        });
    }
}
