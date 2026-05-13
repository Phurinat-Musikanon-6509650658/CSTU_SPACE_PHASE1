<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserRole;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Helpers\PermissionHelper;
use App\Helpers\XlsxParser;
use App\Helpers\XlsxBuilder;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users (Admin/Coordinator can view)
     */
    public function index()
    {
        // Admin, Coordinator สามารถดูได้ทั้งหมด
        $users = DB::table('user')->get();
        $students = Student::select('student_id', 'username_std', 'firstname_std', 'lastname_std', 'email_std', 'role', 'course_code', 'semester', 'year')
            ->get();
        
        // ไม่มี Staff ทั่วไป ทุกคนแก้ไขได้
        $canEdit = true;
        
        return view('admin.users.index', compact('users', 'students', 'canEdit'));
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
            'prefix_user'   => $request->prefix_user,
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
            'prefix_user'    => $request->prefix_user,
            'firstname_user' => $request->firstname_user,
            'lastname_user'  => $request->lastname_user,
            'email_user'     => $request->email_user,
            'role'           => (int)$request->role,
            'user_code'      => $request->user_code,
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

    // ─── Import (XLSX) ────────────────────────────────────────────────────────

    public function importForm()
    {
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }
        return view('admin.users.import');
    }

    public function importPreview(Request $request)
    {
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ], [
            'file.required' => 'กรุณาเลือกไฟล์',
            'file.mimes'    => 'รองรับเฉพาะ .xlsx / .xls',
        ]);

        try {
            $rows = XlsxParser::parse($request->file('file'), 1); // skip header row
        } catch (\Exception $e) {
            return back()->with('error', 'อ่านไฟล์ไม่ได้: ' . $e->getMessage());
        }

        $existingUsernames = DB::table('user')
            ->pluck('username_user')
            ->flip()
            ->toArray();

        $preview = [];
        foreach ($rows as $row) {
            $prefix    = trim($row[0] ?? '');
            $firstname = trim($row[1] ?? '');
            $lastname  = trim($row[2] ?? '');
            $username  = trim($row[3] ?? '');
            $email     = trim($row[4] ?? '');
            $userCode  = trim($row[5] ?? '');
            $roleRaw   = trim($row[6] ?? '');
            $password  = trim($row[7] ?? '');

            if ($username === '' || $firstname === '') continue;

            $role = $this->parseRole($roleRaw);
            if ($password === '') $password = $username;

            $warnings = [];
            if ($email === '') $warnings[] = 'ไม่มี email';
            if ($userCode === '') $warnings[] = 'ไม่มี user_code';

            $preview[] = [
                'prefix'    => $prefix,
                'firstname' => $firstname,
                'lastname'  => $lastname,
                'username'  => $username,
                'email'     => $email,
                'user_code' => $userCode,
                'role'      => $role,
                'role_raw'  => $roleRaw,
                'password'  => $password,
                'exists'    => isset($existingUsernames[$username]),
                'warnings'  => $warnings,
            ];
        }

        if (empty($preview)) {
            return back()->with('error', 'ไม่พบข้อมูลในไฟล์');
        }

        session(['user_excel_preview' => $preview]);

        $newCount    = count(array_filter($preview, fn($r) => !$r['exists']));
        $existsCount = count(array_filter($preview, fn($r) => $r['exists']));

        return view('admin.users.import', compact('preview', 'newCount', 'existsCount'));
    }

    public function importConfirm(Request $request)
    {
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $preview = session('user_excel_preview', []);
        if (empty($preview)) {
            return redirect()->route('users.importForm')->with('error', 'ไม่พบข้อมูล Preview กรุณาอัปโหลดใหม่');
        }

        $created = 0;
        DB::beginTransaction();
        try {
            foreach ($preview as $row) {
                if ($row['exists']) continue;
                DB::table('user')->insertOrIgnore([
                    'prefix_user'    => $row['prefix'],
                    'firstname_user' => $row['firstname'],
                    'lastname_user'  => $row['lastname'],
                    'username_user'  => $row['username'],
                    'email_user'     => $row['email'],
                    'user_code'      => $row['user_code'] ?: null,
                    'role'           => $row['role'],
                    'password_user'  => Hash::make($row['password']),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
                $created++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }

        session()->forget('user_excel_preview');
        $skipped = count($preview) - $created;

        return redirect()->route('users.index')
            ->with('success', "Import users สำเร็จ {$created} คน"
                . ($skipped ? " (ข้าม {$skipped} ที่มีอยู่แล้ว)" : ''));
    }

    // ─── Export (XLSX) ────────────────────────────────────────────────────────

    public function exportAll()
    {
        $users = DB::table('user')->orderBy('user_id')->get();

        $header = ['prefix', 'firstname', 'lastname', 'username', 'email', 'user_code', 'role', 'password'];
        $rows   = [$header];
        foreach ($users as $u) {
            $rows[] = [
                $u->prefix_user    ?? '',
                $u->firstname_user ?? '',
                $u->lastname_user  ?? '',
                $u->username_user  ?? '',
                $u->email_user     ?? '',
                $u->user_code      ?? '',
                (string)($u->role  ?? ''),
                '',
            ];
        }

        return XlsxBuilder::download('users_export_' . date('Y-m-d_His') . '.xlsx', ['Users' => $rows]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function parseRole(string $raw): int
    {
        if (is_numeric($raw) && (int)$raw > 0) return (int)$raw;
        return match(strtolower(trim($raw))) {
            'admin'                  => 32768,
            'coordinator'            => 16384,
            'advisor', 'lecturer'    => 8192,
            'staff'                  => 4096,
            'student'                => 2048,
            default                  => 8192,
        };
    }
}
