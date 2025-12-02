<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class CoordinatorUserController extends Controller
{
    /**
     * แสดงหน้า User & Student Management สำหรับ Coordinator
     * - สามารถดูข้อมูลได้
     * - สามารถ Export ได้
     * - สามารถ Import ได้
     * - ไม่สามารถกำหนด role ได้
     */
    public function index()
    {
        // ดึงข้อมูล Users (ไม่รวม Student)
        $users = DB::table('user')
            ->select('user_id', 'username_user', 'firstname_user', 'lastname_user', 'email_user', 'user_code', 'role')
            ->orderBy('user_id', 'asc')
            ->paginate(20, ['*'], 'user_page');

        // ดึงข้อมูล Students (รวม course_code, semester, year) - ใช้ Eloquent Model
        $students = Student::select('student_id', 'username_std', 'firstname_std', 'lastname_std', 'email_std', 'role', 'course_code', 'semester', 'year')
            ->orderBy('student_id', 'asc')
            ->paginate(20, ['*'], 'student_page');

        // Role mapping
        $roleMap = [
            1 => 'Student',
            2 => 'Lecturer',
            4 => 'Coordinator',
            8 => 'Staff',
            16 => 'Admin',
            2048 => 'Student'
        ];

        return view('coordinator.users.index', compact('users', 'students', 'roleMap'));
    }

    /**
     * Export All Users เป็น CSV
     */
    public function exportUsers()
    {
        $users = DB::table('user')
            ->select('user_id', 'username_user', 'firstname_user', 'lastname_user', 'email_user', 'user_code', 'role')
            ->orderBy('user_id', 'asc')
            ->get();

        $roleMap = [
            1 => 'Student',
            2 => 'Lecturer',
            4 => 'Coordinator',
            8 => 'Staff',
            16 => 'Admin'
        ];

        $filename = 'users_export_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($users, $roleMap) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, ['ID', 'Username', 'Firstname', 'Lastname', 'Email', 'User Code', 'Role']);
            
            // Data
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->user_id,
                    $user->username_user,
                    $user->firstname_user,
                    $user->lastname_user,
                    $user->email_user,
                    $user->user_code,
                    $roleMap[$user->role] ?? $user->role
                ]);
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export All Students เป็น CSV
     */
    public function exportStudents()
    {
        $students = DB::table('student')
            ->select('student_id', 'username_std', 'firstname_std', 'lastname_std', 'email_std', 'course_code', 'semester', 'year', 'role')
            ->orderBy('student_id', 'asc')
            ->get();

        $filename = 'students_export_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($students) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, ['ID', 'Username', 'Firstname', 'Lastname', 'Email', 'Course Code', 'Semester', 'Year', 'Role']);
            
            // Data
            foreach ($students as $student) {
                fputcsv($file, [
                    $student->student_id,
                    $student->username_std,
                    $student->firstname_std,
                    $student->lastname_std,
                    $student->email_std,
                    $student->course_code ?? '-',
                    $student->semester ?? '-',
                    $student->year ?? '-',
                    'Student'
                ]);
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * แสดงฟอร์ม Import User
     */
    public function importUserForm()
    {
        return view('coordinator.users.import-user');
    }

    /**
     * แสดงฟอร์ม Import Student
     */
    public function importStudentForm()
    {
        return view('coordinator.users.import-student');
    }

    /**
     * Import Users จาก CSV (ไม่มีการกำหนด role - ใช้ default)
     */
    public function importUsers(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        
        $csv = array_map(function($line) {
            return str_getcsv($line, ',', '"', '\\');
        }, file($path));

        // Skip header
        $header = array_shift($csv);
        
        $imported = 0;
        $errors = [];

        foreach ($csv as $row) {
            if (count($row) < 4) continue; // ต้องมีอย่างน้อย username, firstname, lastname, email
            
            try {
                $exists = DB::table('user')
                    ->where('username_user', $row[0])
                    ->exists();
                
                if (!$exists) {
                    DB::table('user')->insert([
                        'username_user' => $row[0],
                        'firstname_user' => $row[1],
                        'lastname_user' => $row[2],
                        'email_user' => $row[3],
                        'user_code' => $row[4] ?? strtolower(substr($row[1], 0, 3)),
                        'role' => 2, // Default เป็น Lecturer (Coordinator ไม่สามารถกำหนด role ได้)
                        'password_user' => bcrypt('password123') // Default password
                    ]);
                    $imported++;
                }
            } catch (\Exception $e) {
                $errors[] = "Row {$row[0]}: {$e->getMessage()}";
            }
        }

        if (count($errors) > 0) {
            return redirect()->route('coordinator.users.index')
                ->with('import_errors', $errors)
                ->with('success', "Imported $imported users");
        }

        return redirect()->route('coordinator.users.index')
            ->with('success', "Successfully imported $imported users");
    }

    /**
     * Import Students จาก CSV
     */
    public function importStudents(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        
        $csv = array_map(function($line) {
            return str_getcsv($line, ',', '"', '\\');
        }, file($path));

        // Skip header
        $header = array_shift($csv);
        
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($csv as $index => $row) {
            // ข้ามแถวที่ว่าง
            if (empty(array_filter($row))) {
                continue;
            }
            
            // Map columns: username, firstname, lastname, email, password, course_code, semester, year
            $username = trim($row[0] ?? '');
            $firstname = trim($row[1] ?? '');
            $lastname = trim($row[2] ?? '');
            $email = trim($row[3] ?? '');
            $password = trim($row[4] ?? '');
            $courseCode = trim($row[5] ?? 'CS303');
            $semester = trim($row[6] ?? '2');
            $year = trim($row[7] ?? '2568');

            // Validate required fields
            if (empty($username) || empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
                $errors[] = "แถวที่ " . ($index + 2) . ": ข้อมูลไม่ครบถ้วน";
                $skipped++;
                continue;
            }

            // Validate course_code
            if (!in_array($courseCode, ['CS303', 'CS403'])) {
                $errors[] = "แถวที่ " . ($index + 2) . ": รหัสวิชาต้องเป็น CS303 หรือ CS403";
                $skipped++;
                continue;
            }

            // Validate semester
            if (!in_array($semester, ['1', '2'])) {
                $errors[] = "แถวที่ " . ($index + 2) . ": เทอมต้องเป็น 1 หรือ 2";
                $skipped++;
                continue;
            }
            
            try {
                $exists = DB::table('student')
                    ->where('username_std', $username)
                    ->exists();
                
                if (!$exists) {
                    DB::table('student')->insert([
                        'username_std' => $username,
                        'firstname_std' => $firstname,
                        'lastname_std' => $lastname,
                        'email_std' => $email,
                        'password_std' => bcrypt($password),
                        'course_code' => $courseCode,
                        'semester' => (int)$semester,
                        'year' => (int)$year,
                        'role' => 2048, // Student role
                    ]);
                    $imported++;
                }
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
            return redirect()->route('coordinator.users.index')
                ->with('import_errors', $errors)
                ->with('success', $message);
        }

        return redirect()->route('coordinator.users.index')
            ->with('success', $message);
    }
}
