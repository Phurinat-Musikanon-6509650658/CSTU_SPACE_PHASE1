<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Helpers\PermissionHelper;

class StudentManagementController extends Controller
{
    /**
     * Show the form for creating a new student
     */
    public function create()
    {
        if (!PermissionHelper::canManageUsers()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์จัดการนักศึกษา');
        }

        return view('admin.students.create');
    }

    /**
     * Store a newly created student in database
     */
    public function store(Request $request)
    {
        if (!PermissionHelper::canManageUsers()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์จัดการนักศึกษา');
        }

        $request->validate([
            'username_std' => 'required|unique:student,username_std',
            'firstname_std' => 'required',
            'lastname_std' => 'required',
            'email_std' => 'required|email',
            'password_std' => 'required|min:6',
        ]);

        DB::table('student')->insert([
            'username_std' => $request->username_std,
            'firstname_std' => $request->firstname_std,
            'lastname_std' => $request->lastname_std,
            'email_std' => $request->email_std,
            'password_std' => Hash::make($request->password_std),
            'role' => 2048, // Student role_code
        ]);

        return redirect()->route('users.index')->with('success', 'เพิ่มนักศึกษาสำเร็จ');
    }

    /**
     * Show the form for editing the specified student
     */
    public function edit($id)
    {
        if (!PermissionHelper::canManageUsers()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์จัดการนักศึกษา');
        }

        $student = DB::table('student')->where('student_id', $id)->first();

        if (!$student) {
            return redirect()->route('users.index')->with('error', 'ไม่พบนักศึกษานี้');
        }

        return view('admin.students.edit', compact('student'));
    }

    /**
     * Update the specified student in database
     */
    public function update(Request $request, $id)
    {
        if (!PermissionHelper::canManageUsers()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์จัดการนักศึกษา');
        }

        $request->validate([
            'firstname_std' => 'required',
            'lastname_std' => 'required',
            'email_std' => 'required|email',
        ]);

        $updateData = [
            'firstname_std' => $request->firstname_std,
            'lastname_std' => $request->lastname_std,
            'email_std' => $request->email_std,
        ];

        // ถ้ามีการเปลี่ยนรหัสผ่าน
        if ($request->filled('password_std')) {
            $updateData['password_std'] = Hash::make($request->password_std);
        }

        DB::table('student')
            ->where('student_id', $id)
            ->update($updateData);

        return redirect()->route('users.index')->with('success', 'แก้ไขนักศึกษาสำเร็จ');
    }

    /**
     * Remove the specified student from database
     */
    public function destroy($id)
    {
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        DB::table('student')->where('student_id', $id)->delete();

        return redirect()->route('users.index')->with('success', 'ลบนักศึกษาสำเร็จ');
    }

    /**
     * Show import form
     */
    public function importForm()
    {
        if (!PermissionHelper::isAdmin()) {
            return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        return view('admin.students.import');
    }

    /**
     * Import students from CSV file
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

            // Map columns: username, firstname, lastname, email, password
            $username = trim($row[0] ?? '');
            $firstname = trim($row[1] ?? '');
            $lastname = trim($row[2] ?? '');
            $email = trim($row[3] ?? '');
            $password = trim($row[4] ?? '');

            // Validate required fields
            if (empty($username) || empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
                $errors[] = "แถวที่ " . ($index + 2) . ": ข้อมูลไม่ครบถ้วน";
                $skipped++;
                continue;
            }

            // ตรวจสอบว่า username ซ้ำหรือไม่
            $exists = DB::table('student')->where('username_std', $username)->exists();
            
            if ($exists) {
                $errors[] = "แถวที่ " . ($index + 2) . ": Username '{$username}' มีอยู่แล้ว";
                $skipped++;
                continue;
            }

            // Insert student
            try {
                DB::table('student')->insert([
                    'username_std' => $username,
                    'firstname_std' => $firstname,
                    'lastname_std' => $lastname,
                    'email_std' => $email,
                    'password_std' => Hash::make($password),
                    'role' => 2048, // Student role_code
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "แถวที่ " . ($index + 2) . ": " . $e->getMessage();
                $skipped++;
            }
        }

        $message = "Import นักศึกษาสำเร็จ {$imported} รายการ";
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
            'Content-Disposition' => 'attachment; filename="student_import_template.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['username', 'firstname', 'lastname', 'email', 'password']);
            
            // ตัวอย่างข้อมูล
            fputcsv($file, ['6509650001', 'สมชาย', 'ใจดี', 'somchai.std@dome.tu.ac.th', 'pass1234']);
            fputcsv($file, ['6509650002', 'สมหญิง', 'รักเรียน', 'somying.std@dome.tu.ac.th', 'pass5678']);
            fputcsv($file, ['6509650003', 'สมศักดิ์', 'มานะ', 'somsak.std@dome.tu.ac.th', 'pass9012']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

