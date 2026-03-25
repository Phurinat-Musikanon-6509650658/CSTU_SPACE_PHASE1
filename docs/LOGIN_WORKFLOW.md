# 🔐 ระบบ Login ไปยังหน้าเมนู - คู่มือละเอียด

## 📋 สารบัญ
1. [ขั้นตอนการ Login](#ขั้นตอนการ-login)
2. [รายละเอียด 6 ส่วนหลัก](#รายละเอียด-6-ส่วนหลัก)
3. [Guard Authentication](#guard-authentication)
4. [Middleware Protection](#middleware-protection)
5. [ตารางสรุม](#ตารางสรุม)

---

## ขั้นตอนการ Login

### 🔄 ลำดับขั้นตอน 9 ขั้น

```
1️⃣  User เข้า / login
    ↓
2️⃣  AuthController::login()
    ↓
    ✓ Validate username/password
    ↓
3️⃣  เรียก TU API ตรวจสอบ?
    ├─→ API สำเร็จ → 4️⃣  findLocalRecord()
    └─→ API ล้มเหลว → Fallback: checkLoginInDatabase()
    ↓
5️⃣  หาบัญชีใน DB?
    ├─→ type=user → 6️⃣  setUserSession (STAFF/ADMIN)
    └─→ type=student → 6️⃣  setUserSession (STUDENT)
    ↓
7️⃣  Auth::guard(X)::login()
    ├─→ guard('web')::login() หรือ
    └─→ guard('student')::login()
    ↓
8️⃣  เก็บ Session:
    ├─→ role_code
    ├─→ displayname
    └─→ user_id / student_id
    ↓
9️⃣  Redirect ตามประเภท User
    ├─→ Student → student.menu
    └─→ Staff/Admin → menu
    ↓
✅ MenuController::index() หรือ StudentController::menu()
```

---

## รายละเอียด 6 ส่วนหลัก

### 1️⃣ ตรวจสอบ Credentials

**Route Definition**
- File: `routes/web.php` (Line 45-48)
```
POST /login  →  AuthController::login()
GET  /       →  AuthController::showLoginForm()
```

**Validate Input**
- File: `app/Http/Controllers/AuthController.php` (Line 25-32)
```php
$request->validate([
    'username' => 'required',
    'password' => 'required',
]);

$username = $request->input('username');
$password = $request->input('password');
```

---

### 2️⃣ เรียก TU API (ลองเชื่อมต่อ)

**File**: `app/Http/Controllers/AuthController.php` (Line 36-45)

```php
// สร้าง HTTP client
$client = new Client();

try {
    // เรียก API ภายนอก (Thammasat University API)
    $apiKey = env('TU_API_KEY');
    $response = $client->post('https://restapi.tu.ac.th/api/v1/auth/Ad/verify', [
        'json' => [ 
            'UserName' => $username, 
            'PassWord' => $password 
        ],
        'headers' => [ 
            'Content-Type' => 'application/json', 
            'Application-Key' => $apiKey 
        ],
        'timeout' => 5
    ]);

    // Decode response
    $data = json_decode($response->getBody(), true);

    // ถ้า API ยืนยัน → ไปขั้นตอนต่อ
    if (!empty($data['status']) && $data['status'] === true) {
        // ... ขั้นตอน 4-5
    }
} catch (\GuzzleHttp\Exception\ConnectException $e) {
    // ❌ API ไม่สามารถเชื่อมต่อได้ → Fallback
    \Log::warning('API connection failed, using local database');
    return $this->checkLoginInDatabase($username, $password);
} catch (\GuzzleHttp\Exception\RequestException $e) {
    // ❌ API request error → Fallback
    return $this->checkLoginInDatabase($username, $password);
}
```

**⚠️ Fallback Mechanism**
ถ้า API ไม่ตอบหรือเกิด error → ใช้ฐานข้อมูลท้องถิ่นแทน

---

### 3️⃣ หาบัญชี 2 ประเภท

**File**: `app/Http/Controllers/AuthController.php` (Line 109-122)

```php
private function findLocalRecord($username)
{
    // ✅ ตรวจหาใน table 'user' ก่อน (Admin/Coordinator/Lecturer/Staff)
    $user = DB::table('user')->where('username_user', $username)->first();
    if ($user) {
        return ['type' => 'user', 'record' => $user];
    }

    // ✅ ถ้าไม่เจอ ตรวจหาใน table 'student'
    $student = DB::table('student')->where('username_std', $username)->first();
    if ($student) {
        return ['type' => 'student', 'record' => $student];
    }

    // ❌ ไม่เจอทั้งคู่
    return null;
}
```

**ลำดับค้นหา**:
1. หา username ใน table `user`
2. ถ้าไม่เจอ → หา username ใน table `student`
3. ถ้าไม่เจอเลย → return null (ไปเซ็ตข้อมูล login failed)

---

### 4️⃣ เซ็ต Session (บันทึกข้อมูล User)

**File**: `app/Http/Controllers/AuthController.php` (Line 126-175)

```php
private function setUserSession($type, $record)
{
    if ($type === 'user') {
        // === สำหรับ USER (Staff/Admin/Lecturer) ===
        
        // สร้างชื่อแสดงผล
        $display = trim(($record->firstname_user ?? '') . ' ' . ($record->lastname_user ?? '')) 
                   ?: ($record->username_user ?? '');
        $role = $record->role ?? 0;  // role_code (integer)
        $userId = $record->user_id ?? null;
        
        // ① Login with web guard
        $user = User::find($userId);
        if ($user) {
            Auth::guard('web')->login($user);
        }
        
        // ② เก็บ Session
        Session::put('role_code', $role);          // เก็บ role_code สำหรับ permissions
        Session::put('department', 'staff');       // ชื่อ role (สำหรับ compatibility)
        Session::put('user_id', $userId);
        
    } else {
        // === สำหรับ STUDENT ===
        
        // สร้างชื่อแสดงผล
        $display = trim(($record->firstname_std ?? '') . ' ' . ($record->lastname_std ?? '')) 
                   ?: ($record->username_std ?? '');
        $role = 2048;  // Student role_code (fixed)
        $studentId = $record->student_id ?? null;
        
        // ① Login with student guard
        $student = Student::find($studentId);
        if ($student) {
            Auth::guard('student')->login($student);
        }
        
        // ② เก็บ Session
        Session::put('role_code', $role);          // role_code = 2048
        Session::put('department', 'student');     // ชื่อ role
        Session::put('student_id', $studentId);
    }

    // ③ เก็บข้อมูลทั่วไป (ทุกประเภท user)
    Session::put('displayname', $display);         // ชื่อแสดงผล
    Session::put('login_time', time());            // เวลา login
    Session::put('last_activity', time());         // เวลา activity ล่าสุด

    // ④ บันทึก Login Log
    $loginLog = LoginLog::createLoginLog(
        $username,
        $type,
        $userId,
        $studentId,
        $role,
        'success'
    );
    
    // เก็บ login log ID สำหรับอัพเดท logout time
    Session::put('login_log_id', $loginLog->id);
}
```

**Session Keys ที่เก็บ**:

| Key | Value | ใช้สำหรับ |
|-----|-------|---------|
| `displayname` | ชื่อแสดงผล | แสดงชื่อใน UI |
| `role_code` | Binary role (int) | Check permissions |
| `user_id` | ID ใน table user | Staff/Admin/Lecturer |
| `student_id` | ID ใน table student | Students |
| `login_time` | timestamp | Track login time |
| `last_activity` | timestamp | Session timeout |
| `login_log_id` | ID ใน table login_log | Update logout time |

---

### 5️⃣ Redirect ตามประเภท User

**File**: `app/Http/Controllers/AuthController.php` (Line 72-79)

```php
if ($found) {
    // เซ็ต session (ขั้นตอน 4)
    $this->setUserSession($found['type'], $found['record']);
    
    // Redirect based on user type
    if ($found['type'] === 'student') {
        return redirect()->route('student.menu');  // ไปหน้า Student Menu
    } else {
        return redirect()->route('menu');  // ไปหน้า Staff/Admin Menu
    }
}
```

**Redirect Routes**:
- **Student**: `GET /student/menu` → `student.menu`
- **Staff/Admin/Lecturer**: `GET /menu` → `menu`

---

### 6️⃣ แสดงหน้าเมนู (Protected Routes)

#### 🎓 สำหรับ **STUDENT**

**Route Definition**
- File: `routes/web.php` (Line 48-52)
```php
Route::middleware(['auth:student', 'check.system.status'])->group(function () {
    Route::get('/student/menu', [StudentController::class, 'menu'])->name('student.menu');
    Route::get('/student/dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');
    // ... routes อื่นๆ
});
```

**Controller Method**
- File: `app/Http/Controllers/StudentController.php` (Line 8-20)
```php
public function menu()
{
    // ① ดึง Student object จาก guard
    $student = Auth::guard('student')->user();
    
    if (!$student) {
        return redirect()->route('login')->with('error', 'กรุณาเข้าสู่ระบบ');
    }

    // ② ดึงข้อมูลกลุ่มของ student
    $myGroup = $student->groups()
        ->with(['members.student', 'latestProposal.lecturer', 'project.grade'])
        ->first();
    
    // ③ ดึงคำเชิญที่ค้างอยู่
    $pendingInvitations = $student->pendingInvitations()
        ->with(['group', 'inviter'])
        ->orderBy('created_at', 'desc')
        ->get();

    // ④ เช็คว่าเป็นหัวหน้ากลุ่มหรือไม่
    $isGroupLeader = false;
    if ($myGroup) {
        $firstMember = $myGroup->members()->orderBy('groupmem_id', 'asc')->first();
        $isGroupLeader = $firstMember && $firstMember->username_std === $student->username_std;
    }

    // ⑤ ส่งข้อมูลไปยัง view
    return view('student.menu', [
        'student' => $student,
        'myGroup' => $myGroup,
        'pendingInvitations' => $pendingInvitations,
        'isGroupLeader' => $isGroupLeader
    ]);
}
```

#### 👨‍💼 สำหรับ **STAFF/ADMIN/LECTURER**

**Route Definition**
- File: `routes/web.php` (Line 99+)
```php
Route::middleware(['auth', 'check.system.status'])->group(function () {
    Route::get('/menu', [MenuController::class, 'index'])->name('menu');
    // ... routes อื่นๆ
});
```

**Controller Method**
- File: `app/Http/Controllers/MenuController.php` (Line 12-33)
```php
public function index()
{
    // ① ตรวจสอบว่า user login หรือยัง
    if (!Session::has('displayname')) {
        return redirect()->route('login');
    }

    // ② ดึงข้อมูลจาก session
    $displayname = Session::get('displayname');
    $roleCode = Session::get('role_code', 2048);  // ดึง role_code จาก session
    $department = Session::get('department', 'student');

    // ③ สร้างเมนูตาม binary permission
    // role_code เป็น binary ตัวเลข เช่น:
    // - 32768 = Admin
    // - 16384 = Coordinator
    // - 8192 = Lecturer
    // - 2048 = Student
    $menuGroups = $this->getMenuByPermission($roleCode);

    // ④ ดึง roles ทั้งหมดที่ user มี
    $userRoles = $this->getUserRoles($roleCode);

    // ⑤ ส่งข้อมูลไปยัง view
    return view('menu', [
        'displayname' => $displayname,
        'role' => $department,
        'userRoles' => $userRoles,
        'menuGroups' => $menuGroups
    ]);
}
```

**Binary Role Codes**:
```php
private function getUserRoles($roleCode)
{
    $roles = [];
    
    // เช็คแต่ละ role ด้วย bitwise AND operator (&)
    if (($roleCode & 32768) !== 0) $roles[] = ['name' => 'Admin', 'class' => 'admin'];
    if (($roleCode & 16384) !== 0) $roles[] = ['name' => 'Coordinator', 'class' => 'coordinator'];
    if (($roleCode & 8192) !== 0) $roles[] = ['name' => 'Lecturer', 'class' => 'lecturer'];
    if (($roleCode & 4096) !== 0) $roles[] = ['name' => 'Staff', 'class' => 'staff'];
    if (($roleCode & 2048) !== 0) $roles[] = ['name' => 'Student', 'class' => 'student'];
    
    return $roles;
}
```

---

## Guard Authentication

### 🔐 Laravel Guards Configuration

**File**: `config/auth.php` (Line 37-46)

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',      // ใช้ User model
    ],
    'student' => [
        'driver' => 'session',
        'provider' => 'students',   // ใช้ Student model
    ],
],
```

### ✅ How Guards Work

**Web Guard** (สำหรับ User/Staff/Admin/Lecturer):
```php
Auth::guard('web')->login($user);           // เข้าสู่ระบบ
$user = Auth::guard('web')->user();         // ดึง user object
Auth::guard('web')->logout();               // ออกจากระบบ
Auth::guard('web')->check();                // ตรวจสอบ login
```

**Student Guard** (สำหรับ Student):
```php
Auth::guard('student')->login($student);    // เข้าสู่ระบบ
$student = Auth::guard('student')->user();  // ดึง student object
Auth::guard('student')->logout();           // ออกจากระบบ
Auth::guard('student')->check();            // ตรวจสอบ login
```

---

## Middleware Protection

### 🛡️ Route Middleware

#### 1. `auth:web` (ค่าเริ่มต้น)
```php
Route::middleware(['auth'])->group(function () {
    // ✅ ต้องเข้าสู่ระบบด้วย web guard
});
```

#### 2. `auth:student`
```php
Route::middleware(['auth:student'])->group(function () {
    // ✅ ต้องเข้าสู่ระบบด้วย student guard
});
```

#### 3. `check.system.status`
**File**: `app/Http/Middleware/CheckSystemStatus.php`

```php
public function handle(Request $request, Closure $next)
{
    // ✅ Admin ผ่านได้เสมอ
    if (PermissionHelper::isAdmin()) {
        return $next($request);
    }

    // ✅ ตรวจสอบสถานะระบบ
    if (!SystemSetting::isSystemOpen()) {
        // ❌ ระบบปิด และ user ไม่ใช่ admin
        return response()->view('errors.system_closed', [], 503);
    }
    
    return $next($request);
}
```

---

## ตารางสรุม

### 📊 ตารางที่ 1: ลำดับขั้นตอน

| ขั้น | ส่วน | ไฟล์ | Line | หน้าที่ |
|------|------|------|------|--------|
| 1-2 | Login Form & Validate | AuthController.php | 18-32 | แสดงฟอร์ม & ตรวจสอบ input |
| 3 | Call API | AuthController.php | 36-99 | เรียก TU API |
| 4 | Find Record | AuthController.php | 109-122 | หา user/student ใน DB |
| 5 | Verify Password | AuthController.php | 181-230 | ตรวจสอบรหัสผ่าน |
| 6 | Set Session | AuthController.php | 126-175 | เก็บข้อมูล session |
| 7 | Login Guard | AuthController.php | 133/148 | Auth::guard(X)::login() |
| 8 | Create Log | AuthController.php | 169-173 | บันทึก login log |
| 9 | Redirect | AuthController.php | 72-79 | ไปหน้าเมนู |

### 📊 ตารางที่ 2: User Types

| ประเภท | Table | Guard | Role Code | Redirect to |
|--------|-------|-------|-----------|-------------|
| **Admin** | user | web | & 32768 | menu |
| **Coordinator** | user | web | & 16384 | menu |
| **Lecturer** | user | web | & 8192 | menu |
| **Staff** | user | web | & 4096 | menu |
| **Student** | student | student | 2048 | student.menu |

### 📊 ตารางที่ 3: Database Tables

| Table | Column | Type | ใช้สำหรับ |
|-------|--------|------|---------|
| **user** | user_id | INT | Primary Key |
| | username_user | VARCHAR | Login username |
| | password_user | VARCHAR | Password (hashed) |
| | firstname_user | VARCHAR | ชื่อ |
| | lastname_user | VARCHAR | นามสกุล |
| | role | INT | Binary role_code |
| **student** | student_id | INT | Primary Key |
| | username_std | VARCHAR | Login username |
| | password_std | VARCHAR | Password (hashed) |
| | firstname_std | VARCHAR | ชื่อ |
| | lastname_std | VARCHAR | นามสกุล |
| **login_log** | id | INT | Primary Key |
| | username | VARCHAR | username ที่ login |
| | user_type | VARCHAR | 'user' หรือ 'student' |
| | login_time | TIMESTAMP | เวลา login |
| | logout_time | TIMESTAMP | เวลา logout |

### 📊 ตารางที่ 4: Session Keys

| Key | Value | Type | ใช้สำหรับ |
|-----|-------|------|---------|
| `displayname` | ชื่อแสดงผล | string | UI display |
| `role_code` | Binary role | int | Permission check |
| `user_id` | ID ใน table user | int/null | Identify staff user |
| `student_id` | ID ใน table student | int/null | Identify student |
| `department` | ชื่อ role | string | UI display |
| `login_time` | timestamp | int | Track login |
| `last_activity` | timestamp | int | Auto-logout |
| `login_log_id` | ID log entry | int | Update on logout |

---

## 🔍 Error Handling

### ❌ Login Failures

```php
// Fallback: ถ้า API failed
if (API_call_failed) {
    return $this->checkLoginInDatabase($username, $password);
}

// Invalid credentials
if (!$found || !password_matches) {
    LoginLog::createLoginLog($username, 'unknown', null, null, 'unknown', 'failed');
    session()->flash('login_error_message', 'Username or Password invalid');
    return back();
}

// Not found in system
if (found_in_API_but_not_in_DB) {
    session()->flash('login_error_message', 'ไม่พบบัญชีนี้ในระบบภายใน');
    return back();
}
```

---

## 🚀 API Integration Flow

```
┌─────────────────────────────────┐
│ User submits login form         │
└────────────┬────────────────────┘
             │
             ▼
┌─────────────────────────────────┐
│ AuthController::login()         │
│ - Validate input                │
└────────────┬────────────────────┘
             │
             ▼
        ┌────────────┐
        │ Call TU API│
        └────┬───┬──┘
             │   │
        ✅   │   │   ❌
        Success  Error
             │   │
             ▼   ▼
        ┌─────────────────────────────┐
        │ Find in Local Database      │
        │ - Check user table          │
        │ - Check student table       │
        └────┬────────────────────────┘
             │
        ┌────┴────┐
        ▼         ▼
     Found   Not Found
        │         │
        ▼         ▼
    ✅ Set    ❌ Error
     Session  Message
        │
        ▼
    Redirect
    to Menu
```

---

## 📚 File References

| File | Purpose |
|------|---------|
| `routes/web.php` | Route definitions (login, menu) |
| `app/Http/Controllers/AuthController.php` | Login logic & session management |
| `app/Http/Controllers/MenuController.php` | Staff/Admin menu display |
| `app/Http/Controllers/StudentController.php` | Student menu display |
| `config/auth.php` | Guard & provider configuration |
| `app/Http/Middleware/CheckSystemStatus.php` | System status check |
| `app/Models/User.php` | User model |
| `app/Models/Student.php` | Student model |
| `app/Models/LoginLog.php` | Login log model |

---

**Last Updated**: March 18, 2026
**Project**: CSTU Space Phase 1
