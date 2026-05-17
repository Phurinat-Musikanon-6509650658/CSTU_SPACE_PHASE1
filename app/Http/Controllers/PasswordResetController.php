<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    // Step 1 — แสดงฟอร์มยืนยันตัวตน
    public function showVerifyForm()
    {
        return view('password.reset');
    }

    // Step 1 — ตรวจสอบ username + email
    public function verify(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'email'    => 'required|email',
        ], [
            'username.required' => 'กรุณากรอกชื่อผู้ใช้',
            'email.required'    => 'กรุณากรอกอีเมล',
            'email.email'       => 'รูปแบบอีเมลไม่ถูกต้อง',
        ]);

        $username = trim($request->input('username'));
        $email    = trim($request->input('email'));

        // ค้นหาใน user table ก่อน
        $user = DB::table('user')
            ->where('username_user', $username)
            ->where('email_user', $email)
            ->first();

        if ($user) {
            $token = Str::random(40);
            session([
                'reset_token'    => $token,
                'reset_type'     => 'user',
                'reset_id'       => $user->user_id,
                'reset_username' => $username,
            ]);
            return redirect()->route('password.new');
        }

        // ถ้าไม่เจอใน user ให้ค้นหาใน student
        $student = DB::table('student')
            ->where('username_std', $username)
            ->where('email_std', $email)
            ->first();

        if ($student) {
            $token = Str::random(40);
            session([
                'reset_token'    => $token,
                'reset_type'     => 'student',
                'reset_id'       => $student->student_id,
                'reset_username' => $username,
            ]);
            return redirect()->route('password.new');
        }

        return back()->withErrors(['verify' => 'ไม่พบบัญชีที่ตรงกับข้อมูลที่กรอก']);
    }

    // Step 2 — แสดงฟอร์มตั้งรหัสใหม่
    public function showNewPasswordForm()
    {
        if (!session('reset_token')) {
            return redirect()->route('password.reset')
                ->withErrors(['verify' => 'กรุณายืนยันตัวตนก่อน']);
        }

        return view('password.new');
    }

    // Step 2 — บันทึกรหัสผ่านใหม่
    public function update(Request $request)
    {
        if (!session('reset_token')) {
            return redirect()->route('password.reset')
                ->withErrors(['verify' => 'กรุณายืนยันตัวตนก่อน']);
        }

        $request->validate([
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ], [
            'password.required'              => 'กรุณากรอกรหัสผ่านใหม่',
            'password.min'                   => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.confirmed'             => 'รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน',
            'password_confirmation.required' => 'กรุณายืนยันรหัสผ่าน',
        ]);

        $type = session('reset_type');
        $id   = session('reset_id');
        $hashed = Hash::make($request->input('password'));

        if ($type === 'user') {
            DB::table('user')
                ->where('user_id', $id)
                ->update(['password_user' => $hashed]);
        } else {
            DB::table('student')
                ->where('student_id', $id)
                ->update(['password_std' => $hashed]);
        }

        // ล้าง session reset ทั้งหมด
        session()->forget(['reset_token', 'reset_type', 'reset_id', 'reset_username']);

        return redirect()->route('login')
            ->with('success_message', 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว กรุณาเข้าสู่ระบบด้วยรหัสผ่านใหม่');
    }
}
