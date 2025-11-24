<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Helpers\PermissionHelper;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users (admin only)
     */
    public function index()
    {
        // Admin เห็นทุกคน
        if (PermissionHelper::canViewAllData()) {
            $users = DB::table('user')->get();
            $students = DB::table('student')->get();
            return view('admin.users.index', compact('users', 'students'));
        }
        
        // Coordinator/Lecturer/Staff เห็นเฉพาะตัวเอง
        if (PermissionHelper::isCoordinator() || PermissionHelper::isLecturer() || PermissionHelper::isStaff()) {
            $userId = PermissionHelper::getCurrentUserId();
            $users = DB::table('user')->where('user_id', $userId)->get();
            $students = collect(); // ไม่แสดง students
            return view('admin.users.index', compact('users', 'students'));
        }

        return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        if (!PermissionHelper::canManageUsers()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เพิ่มผู้ใช้ใหม่');
        }

        return view('admin.users.create');
    }

    /**
     * Store a newly created user in database
     */
    public function store(Request $request)
    {
        if (!PermissionHelper::canManageUsers()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เพิ่มผู้ใช้ใหม่');
        }

        $request->validate([
            'username_user' => 'required|unique:user,username_user',
            'firstname_user' => 'required',
            'lastname_user' => 'required',
            'email_user' => 'required|email',
            'password_user' => 'required|min:6',
            'role' => 'required|integer|min:1',
        ]);

        DB::table('user')->insert([
            'username_user' => $request->username_user,
            'firstname_user' => $request->firstname_user,
            'lastname_user' => $request->lastname_user,
            'email_user' => $request->email_user,
            'password_user' => Hash::make($request->password_user),
            'role' => (int)$request->role,
            'user_code' => $request->user_code,
        ]);

        return redirect()->route('users.index')->with('success', 'เพิ่มผู้ใช้สำเร็จ');
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        // Admin สามารถแก้ไขได้ทุกคน
        if (PermissionHelper::canManageUsers()) {
            $user = DB::table('user')->where('user_id', $id)->first();
        }
        // Coordinator/Lecturer/Staff แก้ไขได้เฉพาะตัวเอง
        else if (PermissionHelper::canManageRoles()) {
            $currentUserId = PermissionHelper::getCurrentUserId();
            if ($id != $currentUserId) {
                return redirect()->route('users.index')->with('error', 'คุณสามารถแก้ไขได้เฉพาะข้อมูลของคุณเอง');
            }
            $user = DB::table('user')->where('user_id', $id)->first();
        }
        else {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์แก้ไขข้อมูลผู้ใช้');
        }

        if (!$user) {
            return redirect()->route('users.index')->with('error', 'ไม่พบผู้ใช้นี้');
        }

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in database
     */
    public function update(Request $request, $id)
    {
        // Admin อัพเดตได้ทุกคน
        if (PermissionHelper::canManageUsers()) {
            // Admin can update anyone
        }
        // Coordinator/Lecturer/Staff อัพเดตได้เฉพาะตัวเอง
        else if (PermissionHelper::canManageRoles()) {
            $currentUserId = PermissionHelper::getCurrentUserId();
            if ($id != $currentUserId) {
                return redirect()->route('users.index')->with('error', 'คุณสามารถแก้ไขได้เฉพาะข้อมูลของคุณเอง');
            }
        }
        else {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์แก้ไขข้อมูลผู้ใช้');
        }

        $request->validate([
            'firstname_user' => 'required',
            'lastname_user' => 'required',
            'email_user' => 'required|email',
            'role' => 'required|integer|min:1',
        ]);

        $updateData = [
            'firstname_user' => $request->firstname_user,
            'lastname_user' => $request->lastname_user,
            'email_user' => $request->email_user,
            'role' => (int)$request->role,
            'user_code' => $request->user_code,
        ];

        // ถ้ามีการเปลี่ยนรหัสผ่าน
        if ($request->filled('password_user')) {
            $updateData['password_user'] = Hash::make($request->password_user);
        }

        DB::table('user')
            ->where('user_id', $id)
            ->update($updateData);

        return redirect()->route('users.index')->with('success', 'แก้ไขผู้ใช้สำเร็จ');
    }

    /**
     * Remove the specified user from database
     */
    public function destroy($id)
    {
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        // ป้องกันไม่ให้ลบตัวเอง
        if (Session::get('user_id') == $id) {
            return redirect()->route('users.index')->with('error', 'ไม่สามารถลบบัญชีตัวเองได้');
        }

        DB::table('user')->where('user_id', $id)->delete();

        return redirect()->route('users.index')->with('success', 'ลบผู้ใช้สำเร็จ');
    }

    /**
     * Show import form
     */
    public function importForm()
    {
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        return view('admin.users.import');
    }

    /**
     * Import users from CSV file
     */
    public function import(Request $request)
    {
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        
        $csv = array_map('str_getcsv', file($path));
        
        // ตรวจสอบ header
        $header = array_shift($csv);
        
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($csv as $index => $row) {
            // ข้ามแถวที่ว่าง
            if (empty(array_filter($row))) {
                continue;
            }

            // Map columns: username, firstname, lastname, email, password, role, user_code
            $username = trim($row[0] ?? '');
            $firstname = trim($row[1] ?? '');
            $lastname = trim($row[2] ?? '');
            $email = trim($row[3] ?? '');
            $password = trim($row[4] ?? '');
            $role = trim($row[5] ?? 'advisor');
            $userCode = trim($row[6] ?? '');

            // Validate required fields
            if (empty($username) || empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
                $errors[] = "แถวที่ " . ($index + 2) . ": ข้อมูลไม่ครบถ้วน";
                $skipped++;
                continue;
            }

            // Validate role
            if (!in_array($role, ['admin', 'coordinator', 'advisor'])) {
                $role = 'advisor'; // default
            }

            // ตรวจสอบว่า username ซ้ำหรือไม่
            $exists = DB::table('user')->where('username_user', $username)->exists();
            
            if ($exists) {
                $errors[] = "แถวที่ " . ($index + 2) . ": Username '{$username}' มีอยู่แล้ว";
                $skipped++;
                continue;
            }

            // Insert user
            try {
                DB::table('user')->insert([
                    'username_user' => $username,
                    'firstname_user' => $firstname,
                    'lastname_user' => $lastname,
                    'email_user' => $email,
                    'password_user' => Hash::make($password),
                    'role' => $role,
                    'user_code' => $userCode,
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "แถวที่ " . ($index + 2) . ": " . $e->getMessage();
                $skipped++;
            }
        }

        $message = "Import สำเร็จ {$imported} รายการ";
        if ($skipped > 0) {
            $message .= ", ข้าม {$skipped} รายการ";
        }

        if (!empty($errors)) {
            Session::flash('import_errors', $errors);
        }

        return redirect()->route('users.index')->with('success', $message);
    }

    /**
     * Download CSV template
     */
    public function downloadTemplate()
    {
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="user_import_template.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['username', 'firstname', 'lastname', 'email', 'password', 'role', 'user_code']);
            
            // ตัวอย่างข้อมูล
            fputcsv($file, ['teacher01', 'สมชาย', 'ใจดี', 'somchai@cstu.ac.th', 'pass1234', 'advisor', 'SCH']);
            fputcsv($file, ['teacher02', 'สมหญิง', 'รักเรียน', 'somying@cstu.ac.th', 'pass5678', 'coordinator', 'SMY']);
            fputcsv($file, ['teacher03', 'สมศักดิ์', 'มานะ', 'somsak@cstu.ac.th', 'pass9012', 'admin', 'SMS']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

