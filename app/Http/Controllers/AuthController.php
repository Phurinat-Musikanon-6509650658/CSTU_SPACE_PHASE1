<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Session;
use DB;
use Illuminate\Support\Facades\Hash;
use App\Models\LoginLog;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Student;

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
                    // อัปเดตข้อมูล department และ student_type จาก API (ถ้าเป็น student)
                    if ($found['type'] === 'student' && isset($data['department'])) {
                        $department = $data['department'];
                        $studentType = $this->determineStudentType($department);
                        
                        DB::table('student')
                            ->where('username_std', $username)
                            ->update([
                                'department' => $department,
                                'student_type' => $studentType
                            ]);
                        
                        // อัปเดต record ที่จะใช้ต่อ
                        $found['record']->department = $department;
                        $found['record']->student_type = $studentType;
                    }
                    
                    $this->setUserSession($found['type'], $found['record']);
                    
                    // Redirect based on user type
                    if ($found['type'] === 'student') {
                        return redirect()->route('student.menu');
                    } else {
                        return redirect()->route('menu');
                    }
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
            $role = $record->role ?? 0; // role_code (integer)
            $userId = $record->user_id ?? null;
            $studentId = null;
            $username = $record->username_user;
            
            // Login with web guard
            $user = User::find($userId);
            if ($user) {
                Auth::guard('web')->login($user);
            }
            
            Session::put('role_code', $role); // เก็บ role_code
            Session::put('department', $this->getRoleNameFromCode($role)); // เก็บชื่อ role สำหรับ compatibility
            Session::put('user_id', $userId);
        } else {
            $display = trim(($record->firstname_std ?? '') . ' ' . ($record->lastname_std ?? '')) ?: ($record->username_std ?? '');
            $role = 2048; // Student role_code
            $userId = null;
            $studentId = $record->student_id ?? null;
            $username = $record->username_std;
            
            // Login with student guard
            $student = Student::find($studentId);
            if ($student) {
                Auth::guard('student')->login($student);
            }
            
            Session::put('role_code', $role); // เก็บ role_code
            Session::put('department', 'student'); // เก็บชื่อ role
            Session::put('student_id', $studentId);
        }

        Session::put('displayname', $display);
        Session::put('login_time', time()); // เก็บเวลา login
        Session::put('last_activity', time()); // เก็บเวลา activity ล่าสุด

        // บันทึก login log
        $loginLog = LoginLog::createLoginLog(
            $username,
            $type,
            $userId,
            $studentId,
            $role,
            'success'
        );
        
        // เก็บ login log ID ไว้ใน session สำหรับอัพเดท logout time
        Session::put('login_log_id', $loginLog->id);
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
                    $this->setUserSession('user', $user);
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
                    $this->setUserSession('user', $user);
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
                    $this->setUserSession('student', $student);
                    return redirect()->route('student.menu');
                }
            } else {
                if (isset($student->password_std) && $student->password_std === $password) {
                    // แปลง plain password เป็น hash และอัพเดตในฐานข้อมูล
                    DB::table('student')
                        ->where('student_id', $student->student_id)
                        ->update(['password_std' => Hash::make($password)]);
                    \Log::info('Password hashed for student: ' . $username);
                    $this->setUserSession('student', $student);
                }
            }
        }

        // ไม่ผ่านทั้งคู่ -> แจ้ง error และบันทึก failed login
        LoginLog::createLoginLog(
            $username,
            'unknown', // ไม่ทราบประเภท user
            null,
            null,
            'unknown',
            'failed',
            'Invalid username or password'
        );
        
        session()->flash('login_error_message', 'Username or Password invalid');
        return back();
    }

    // Logout: clear session and redirect to the login page
    public function logout()
    {
        // อัพเดท logout time ใน login log
        $this->updateLogoutTime();
        
        // Logout from guards
        Auth::guard('web')->logout();
        Auth::guard('student')->logout();
        
        Session::flush();
        return redirect()->route('login');
    }

    // Logout beacon for sending logout time when user closes tab/window
    public function logoutBeacon(Request $request)
    {
        // อัพเดท logout time ใน login log
        $this->updateLogoutTime();
        
        return response()->json(['status' => 'success']);
    }

    // Helper method to update logout time
    private function updateLogoutTime()
    {
        if (Session::has('login_log_id')) {
            $loginLogId = Session::get('login_log_id');
            $loginLog = LoginLog::find($loginLogId);
            
            if ($loginLog && !$loginLog->logout_time) {
                $loginLog->updateLogoutTime();
            }
        }
    }

    // Refresh session for auto-logout prevention
    public function refreshSession()
    {
        if (Session::has('displayname')) {
            // อัพเดท timestamp สำหรับ session
            Session::put('last_activity', time());
            return response()->json(['status' => 'success', 'message' => 'Session refreshed']);
        }
        
        return response()->json(['status' => 'error', 'message' => 'Not logged in'], 401);
    }

    /**
     * กำหนด student_type จาก department
     * @param string $department
     * @return string 'r' หรือ 's'
     */
    private function determineStudentType($department)
    {
        // ภาคพิเศษ -> s
        if (strpos($department, 'ภาคพิเศษ') !== false || strpos($department, 'พิเศษ') !== false) {
            return 's';
        }
        
        // ภาคปกติ -> r
        return 'r';
    }

    /**
     * Get role name from role_code using bitwise check
     */
    private function getRoleNameFromCode($roleCode)
    {
        if (($roleCode & 32768) !== 0) return 'admin';
        if (($roleCode & 16384) !== 0) return 'coordinator';
        if (($roleCode & 8192) !== 0) return 'lecturer';
        if (($roleCode & 4096) !== 0) return 'staff';
        if (($roleCode & 2048) !== 0) return 'student';
        return 'guest';
    }
}