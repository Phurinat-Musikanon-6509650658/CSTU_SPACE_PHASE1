<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Session;
use DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // แสดงหน้า Login
    public function showLoginForm()
    {
        return view('login'); 
    }
    // ประมวลผลการล็อกอิน
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
    // รับค่าจากฟอร์ม
        $username = $request->input('username');
        $password = $request->input('password');

    // สร้าง HTTP client สำหรับเรียก API
        $client = new Client();

        try {
            // เรียก API ภายนอกเพื่อตรวจสอบ (ถ้ามี API_KEY)
            $apiKey = env('TU_API_KEY');
            $response = $client->post('https://restapi.tu.ac.th/api/v1/auth/Ad/verify', [
                'json' => [ 'UserName' => $username, 'PassWord' => $password ],
                'headers' => [ 'Content-Type' => 'application/json', 'Application-Key' => $apiKey ]
            ]);

            //body http massage --> json (ในส่วน body))
            $data = json_decode($response->getBody(), true); //ดึง json

            // ถ้า API ยืนยัน ให้ตรวจสอบว่ามีบัญชีใน DB และรหัสผ่านตรงกัน
            if (!empty($data['status']) && $data['status'] === true) {
                // ล้างการล็อกพยายามผิดพลาด
                session()->forget(['wrong_attempts', 'lock_time']);

                $found = $this->findLocalRecord($username);
                    if ($found) {
                    $this->setUserSession($found['type'], $found['record']);
                    return redirect()->route('menu');
                }
                // API ตอบว่าใช้ได้ แต่ใน DB ไม่มีบัญชีที่ตรงกัน
                session()->flash('login_error_message', 'ไม่พบบัญชีนี้ในระบบภายใน');
                return back();
            }

            // ถ้า API ไม่ตอบหรือไม่ผ่าน ให้ตรวจสอบจาก DB อย่างเดียว
            return $this->checkLoginInDatabase($username, $password);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // API ไม่ตอบ -> ใช้ DB-only flow
            return $this->checkLoginInDatabase($username, $password);
        }
    }

    private function findUserInDb($username, $password)
    {
        return $this->findLocalRecord($username);
    }

    // หา record ใน DB (user ก่อน ถ้าไม่เจอตรวจ student)
    private function findLocalRecord($username)
    {
        $user = DB::table('user')->where('username_user', $username)->first();
        if ($user) {
            return ['type' => 'user', 'record' => $user];
        }

        $student = DB::table('student')->where('username_std', $username)->first();
        if ($student) {
            return ['type' => 'student', 'record' => $student];
        }

        return null;
    }


    // เซ็ตค่า session สำหรับผู้ใช้งาน
    private function setUserSession($type, $record)
    {
        if ($type === 'user') {
            $display = trim(($record->firstname_user ?? '') . ' ' . ($record->lastname_user ?? '')) ?: ($record->username_user ?? '');
            Session::put('department', $record->role ?? '');
            Session::put('user_id', $record->user_id ?? null);
        } else {
            $display = trim(($record->firstname_std ?? '') . ' ' . ($record->lastname_std ?? '')) ?: ($record->username_std ?? '');
            Session::put('department', 'student');
            Session::put('student_id', $record->student_id ?? null);
        }

        Session::put('displayname', $display);
    }

    // ตรวจสอบ login โดยใช้ข้อมูลในฐานข้อมูลเท่านั้น (DB-only flow)
    public function checkLoginInDatabase($username, $password)
    {
        // ตรวจหาในตาราง user ก่อน
        $user = DB::table('user')
            ->where('username_user', $username)
            ->first();

        if ($user) {
            if (!empty($user->password_user) && password_get_info($user->password_user)['algo']) {
                if (Hash::check($password, $user->password_user)) {
                    Session::put('displayname', trim(($user->firstname_user ?? '') . ' ' . ($user->lastname_user ?? '')) ?: $username);
                    Session::put('department', $user->role ?? '');
                    Session::put('user_id', $user->user_id ?? null);
                    return redirect()->route('menu');
                }
            } else {
                // legacy plain password
                if (isset($user->password_user) && $user->password_user === $password) {
                    // แปลง plain password เป็น hash และอัพเดตในฐานข้อมูล
                    DB::table('user')
                        ->where('user_id', $user->user_id)
                        ->update(['password_user' => Hash::make($password)]);
                    \Log::info('Password hashed for user: ' . $username);
                    Session::put('displayname', trim(($user->firstname_user ?? '') . ' ' . ($user->lastname_user ?? '')) ?: $username);
                    Session::put('department', $user->role ?? '');
                    Session::put('user_id', $user->user_id ?? null);
                    return redirect()->route('menu');
                }
            }
        }

        // ถ้าไม่ผ่านใน user ให้ตรวจสอบใน student
        $student = DB::table('student')
            ->where('username_std', $username)
            ->first();

        if ($student) {
            if (!empty($student->password_std) && password_get_info($student->password_std)['algo']) {
                if (Hash::check($password, $student->password_std)) {
                    Session::put('displayname', trim(($student->firstname_std ?? '') . ' ' . ($student->lastname_std ?? '')) ?: $username);
                    Session::put('department', 'student');
                    Session::put('student_id', $student->student_id ?? null);
                    return redirect()->route('menu');
                }
            } else {
                if (isset($student->password_std) && $student->password_std === $password) {
                    // แปลง plain password เป็น hash และอัพเดตในฐานข้อมูล
                    DB::table('student')
                        ->where('student_id', $student->student_id)
                        ->update(['password_std' => Hash::make($password)]);
                    \Log::info('Password hashed for student: ' . $username);
                    Session::put('displayname', trim(($student->firstname_std ?? '') . ' ' . ($student->lastname_std ?? '')) ?: $username);
                    Session::put('department', 'student');
                    Session::put('student_id', $student->student_id ?? null);
                    return redirect()->route('menu');
                }
            }
        }

        // ไม่ผ่านทั้งคู่ -> แจ้ง error
        session()->flash('login_error_message', 'Username or Password invalid');
        return back();
    }

    // Logout: clear session and redirect to the login page
    public function logout()
    {
        Session::flush();
        return redirect()->route('login');
    }
}