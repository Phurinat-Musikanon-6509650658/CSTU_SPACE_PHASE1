# CSTU SPACE - Exam Schedule Management System Report

## ภาพรวมระบบ
ระบบจัดการตารางสอบโครงงาน (Exam Schedule Management System) เป็นส่วนหนึ่งของระบบ CSTU SPACE ที่พัฒนาขึ้นเพื่อจัดการตารางสอบโครงงานของนักศึกษาอย่างมีประสิทธิภาพ รองรับการทำงานของ 3 role หลัก ได้แก่ Admin, Coordinator และ Staff

## วัตถุประสงค์
1. ให้ Admin และ Coordinator สามารถจัดการตารางสอบได้เต็มรูปแบบ (CRUD)
2. ให้ Staff สามารถดูตารางสอบทั้งหมดได้ (Read-only)
3. จัดเก็บการตั้งค่าระบบแบบ centralized
4. รองรับการแสดงผลตารางสอบในรูปแบบรายการและปฏิทิน
5. รองรับการกำหนดตารางสอบหลายโครงงานพร้อมกัน

---

## โครงสร้างฐานข้อมูล

### 1. ตาราง `exam_schedule`
จัดเก็บข้อมูลตารางสอบโครงงาน

**Columns:**
- `ex_id` (BIGINT, PK, AUTO_INCREMENT) - ID ตารางสอบ
- `project_id` (BIGINT, FK→projects.project_id) - รหัสโครงงานที่เชื่อมโยง
- `ex_start_time` (DATETIME) - วันเวลาเริ่มสอบ
- `ex_end_time` (DATETIME) - วันเวลาสิ้นสุดสอบ
- `location` (VARCHAR(200), NULLABLE) - สถานที่สอบ
- `notes` (TEXT, NULLABLE) - หมายเหตุเพิ่มเติม
- `created_at` (TIMESTAMP) - วันที่สร้างข้อมูล
- `updated_at` (TIMESTAMP) - วันที่แก้ไขล่าสุด

**Indexes:**
- PRIMARY KEY: `ex_id`
- FOREIGN KEY: `project_id` → `projects.project_id` (ON DELETE CASCADE)
- INDEX: `ex_start_time` (เพื่อเร่งการค้นหาตามวันที่)

**ความสัมพันธ์:**
- `belongsTo` Project (1 ตารางสอบ → 1 โครงงาน)

---

### 2. ตาราง `system_settings`
จัดเก็บการตั้งค่าระบบแบบ key-value

**Columns:**
- `setting_id` (BIGINT, PK, AUTO_INCREMENT) - ID การตั้งค่า
- `setting_key` (VARCHAR(100), UNIQUE) - คีย์การตั้งค่า
- `setting_value` (TEXT) - ค่าการตั้งค่า
- `description` (VARCHAR(255), NULLABLE) - คำอธิบาย
- `created_at` (TIMESTAMP) - วันที่สร้างข้อมูล
- `updated_at` (TIMESTAMP) - วันที่แก้ไขล่าสุด

**Default Settings:**
| setting_key | setting_value | description |
|------------|---------------|-------------|
| system_status | open | System status: open or closed |
| current_year | 2568 | Current academic year |
| current_semester | 1 | Current semester (1, 2, or 3) |

**Indexes:**
- PRIMARY KEY: `setting_id`
- UNIQUE KEY: `setting_key`

---

## Models

### 1. ExamSchedule Model
**File:** `app/Models/ExamSchedule.php`

**Properties:**
```php
protected $table = 'exam_schedule';
protected $primaryKey = 'ex_id';
protected $fillable = [
    'project_id',
    'ex_start_time',
    'ex_end_time',
    'location',
    'notes'
];
protected $casts = [
    'ex_start_time' => 'datetime',
    'ex_end_time' => 'datetime',
];
```

**Relationships:**
- `belongsTo(Project::class, 'project_id', 'project_id')` - เชื่อมโยงกับโครงงาน

---

### 2. SystemSetting Model
**File:** `app/Models/SystemSetting.php`

**Properties:**
```php
protected $table = 'system_settings';
protected $primaryKey = 'setting_id';
protected $fillable = ['setting_key', 'setting_value', 'description'];
```

**Static Methods:**
- `get($key, $default = null)` - ดึงค่าการตั้งค่า
- `set($key, $value, $description = null)` - บันทึกค่าการตั้งค่า
- `isSystemOpen()` - ตรวจสอบว่าระบบเปิดหรือปิด

---

## Controllers

### SystemSettingsController
**File:** `app/Http/Controllers/SystemSettingsController.php`

#### Admin Methods

**1. System Settings Index**
```php
public function index()
```
- แสดงหน้าหลักการตั้งค่าระบบ
- ดึงข้อมูล: system_status, current_year, current_semester
- Route: `admin.system.index`

**2. Update System Settings**
```php
public function update(Request $request)
```
- อัพเดตการตั้งค่าระบบ
- Validation: system_status (open/closed), current_year, current_semester
- Redirect กลับหน้า index พร้อม success message

**3. Exam Schedule Index (Admin)**
```php
public function examScheduleIndex()
```
- แสดงรายการตารางสอบทั้งหมดพร้อม pagination
- Permission: Admin only
- Route: `admin.exam-schedules.index`

**4. Exam Schedule Calendar (Admin)**
```php
public function examScheduleCalendar()
```
- แสดงตารางสอบในรูปแบบปฏิทิน
- Filter: status, location, search
- Group by date
- Route: `admin.exam-schedules.calendar`

**5. Exam Schedule Create (Admin)**
```php
public function examScheduleCreate()
```
- แสดงฟอร์มสร้างตารางสอบ
- ดึงรายการโครงงานที่ยังไม่มีตารางสอบ
- Route: `admin.exam-schedules.create`

**6. Exam Schedule Store (Admin)**
```php
public function examScheduleStore(Request $request)
```
- บันทึกตารางสอบใหม่ (รองรับหลายโครงงาน)
- Validation: project_ids[], exam_date, start_time, end_time, location, notes
- สร้าง ExamSchedule สำหรับแต่ละโครงงาน
- Route: `admin.exam-schedules.store`

**7. Exam Schedule Edit (Admin)**
```php
public function examScheduleEdit($id)
```
- แสดงฟอร์มแก้ไขตารางสอบ
- Route: `admin.exam-schedules.edit`

**8. Exam Schedule Update (Admin)**
```php
public function examScheduleUpdate(Request $request, $id)
```
- อัพเดตตารางสอบ
- Validation: exam_date, start_time, end_time, location, notes
- Route: `admin.exam-schedules.update`

**9. Exam Schedule Destroy (Admin)**
```php
public function examScheduleDestroy($id)
```
- ลบตารางสอบ
- Return JSON response
- Route: `admin.exam-schedules.destroy`

#### Coordinator Methods
เหมือนกับ Admin Methods ทุกประการ แต่มี permission check ที่แตกต่าง

**Methods:**
1. `coordinatorExamScheduleIndex()`
2. `coordinatorExamScheduleCalendar()`
3. `coordinatorExamScheduleCreate()`
4. `coordinatorExamScheduleStore(Request $request)`
5. `coordinatorExamScheduleEdit($id)`
6. `coordinatorExamScheduleUpdate(Request $request, $id)`
7. `coordinatorExamScheduleDestroy($id)`

**Routes:** ใช้ prefix `coordinator.exam-schedules.*`

#### Staff Methods (Read-Only)

**1. Staff Exam Schedules**
```php
public function staffExamSchedules()
```
- แสดงรายการตารางสอบทั้งหมด (Read-only)
- Permission: Staff only
- ไม่มีปุ่มแก้ไข/ลบ
- Route: `staff.exam-schedules`

**2. Staff Exam Schedules Calendar**
```php
public function staffExamSchedulesCalendar()
```
- แสดงตารางสอบแบบปฏิทิน (Read-only)
- Filter: status, location, search
- Route: `staff.exam-schedules.calendar`

---

## Routes

### Admin Routes
```php
Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // System Settings
    Route::get('system', [SystemSettingsController::class, 'index'])->name('system.index');
    Route::post('system/update', [SystemSettingsController::class, 'update'])->name('system.update');
    
    // Exam Schedules (Full CRUD)
    Route::get('exam-schedules', [SystemSettingsController::class, 'examScheduleIndex'])->name('exam-schedules.index');
    Route::get('exam-schedules/calendar', [SystemSettingsController::class, 'examScheduleCalendar'])->name('exam-schedules.calendar');
    Route::get('exam-schedules/create', [SystemSettingsController::class, 'examScheduleCreate'])->name('exam-schedules.create');
    Route::post('exam-schedules', [SystemSettingsController::class, 'examScheduleStore'])->name('exam-schedules.store');
    Route::get('exam-schedules/{id}/edit', [SystemSettingsController::class, 'examScheduleEdit'])->name('exam-schedules.edit');
    Route::put('exam-schedules/{id}', [SystemSettingsController::class, 'examScheduleUpdate'])->name('exam-schedules.update');
    Route::delete('exam-schedules/{id}', [SystemSettingsController::class, 'examScheduleDestroy'])->name('exam-schedules.destroy');
});
```

### Coordinator Routes
```php
Route::middleware(['role:coordinator'])->prefix('coordinator')->name('coordinator.')->group(function () {
    // Exam Schedules (Full CRUD - เหมือน Admin)
    Route::get('exam-schedules', [SystemSettingsController::class, 'coordinatorExamScheduleIndex'])->name('exam-schedules.index');
    Route::get('exam-schedules/calendar', [SystemSettingsController::class, 'coordinatorExamScheduleCalendar'])->name('exam-schedules.calendar');
    Route::get('exam-schedules/create', [SystemSettingsController::class, 'coordinatorExamScheduleCreate'])->name('exam-schedules.create');
    Route::post('exam-schedules', [SystemSettingsController::class, 'coordinatorExamScheduleStore'])->name('exam-schedules.store');
    Route::get('exam-schedules/{id}/edit', [SystemSettingsController::class, 'coordinatorExamScheduleEdit'])->name('exam-schedules.edit');
    Route::put('exam-schedules/{id}', [SystemSettingsController::class, 'coordinatorExamScheduleUpdate'])->name('exam-schedules.update');
    Route::delete('exam-schedules/{id}', [SystemSettingsController::class, 'coordinatorExamScheduleDestroy'])->name('exam-schedules.destroy');
});
```

### Staff Routes
```php
Route::middleware(['role:staff'])->prefix('staff')->name('staff.')->group(function () {
    // Exam Schedules (Read-Only)
    Route::get('exam-schedules', [SystemSettingsController::class, 'staffExamSchedules'])->name('exam-schedules');
    Route::get('exam-schedules/calendar', [SystemSettingsController::class, 'staffExamSchedulesCalendar'])->name('exam-schedules.calendar');
});
```

---

## Views

### Admin Views
**Path:** `resources/views/admin/exam-schedules/`

**1. index.blade.php**
- แสดงรายการตารางสอบทั้งหมดในรูปแบบตาราง
- มีปุ่ม: เพิ่มตารางสอบ, มุมมองปฏิทิน, กลับ
- แสดงข้อมูล: ID, โครงงาน, เวลาเริ่ม, เวลาสิ้นสุด, สถานที่, ระยะเวลา, หมายเหตุ
- Actions: แก้ไข, ลบ (AJAX)
- Pagination support

**2. calendar.blade.php**
- แสดงตารางสอบแบบ Timeline grouped by date
- Filter: สถานะโครงงาน, สถานที่, ค้นหา
- แสดงการ์ดสำหรับแต่ละตารางสอบ
- Actions: แก้ไข, ลบ (AJAX)

**3. create.blade.php**
- ฟอร์มสร้างตารางสอบ
- รองรับเลือกหลายโครงงานพร้อมกัน (checkbox)
- ฟีเจอร์: Select All, Search, Checkbox selection
- Fields: วันที่สอบ, เวลาเริ่ม, เวลาสิ้นสุด, สถานที่, หมายเหตุ
- แสดงเฉพาะโครงงานที่ยังไม่มีตารางสอบ

**4. edit.blade.php**
- ฟอร์มแก้ไขตารางสอบ
- Pre-filled ข้อมูลเดิม
- Fields เหมือน create แต่แก้ไขทีละ 1 ตารางสอบ

### Coordinator Views
**Path:** `resources/views/coordinator/exam-schedules/`

มี structure และฟังก์ชันเหมือน Admin Views ทุกประการ:
- `index.blade.php`
- `calendar.blade.php`
- `create.blade.php`
- `edit.blade.php`

**ความแตกต่าง:** ใช้ route ที่ต่างกัน (`coordinator.*` แทน `admin.*`)

### Staff Views
**Path:** `resources/views/staff/exam-schedules/`

**1. index.blade.php**
- แสดงรายการตารางสอบทั้งหมด (Read-only)
- ไม่มีปุ่ม: เพิ่มตารางสอบ, แก้ไข, ลบ
- มีปุ่ม: มุมมองปฏิทิน, กลับ
- แสดงข้อมูล: ID, โครงงาน, เวลาเริ่ม, เวลาสิ้นสุด, สถานที่, ระยะเวลา, หมายเหตุ, สถานะโครงงาน

**2. calendar.blade.php**
- แสดงตารางสอบแบบปฏิทิน (Read-only)
- Filter: สถานะโครงงาน, สถานที่, ค้นหา
- ไม่มีปุ่มแก้ไข/ลบในการ์ด

---

## Permission System

### Role Definitions
```php
const ADMIN_PERMISSION = 32768;      // 1000000000000000
const COORDINATOR_PERMISSION = 16384; // 0100000000000000
const STAFF_PERMISSION = 4096;        // 0001000000000000
```

### Permission Checks
**Admin & Coordinator (Full CRUD):**
```php
if (!PermissionHelper::isAdmin() && !PermissionHelper::isCoordinator()) {
    return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
}
```

**Staff (Read-Only):**
```php
if (!PermissionHelper::isStaff() && !PermissionHelper::isAdmin()) {
    return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
}
```

### Middleware
```php
// Admin only
Route::middleware(['role:admin'])

// Coordinator only
Route::middleware(['role:coordinator'])

// Staff only
Route::middleware(['role:staff'])
```

---

## Menu Integration

### MenuController Updates

**Admin Menu:**
```php
[
    'title' => 'ตารางสอบ',
    'description' => 'จัดการตารางสอบโครงงาน',
    'icon' => 'bi-calendar-event-fill',
    'url' => route('admin.exam-schedules.index'),
    'class' => 'warning-card',
    'btn_class' => 'warning-btn'
]
```

**Coordinator Menu:**
```php
[
    'title' => 'ตารางสอบโครงงาน',
    'description' => 'จัดการตารางสอบโครงงาน',
    'icon' => 'bi-calendar-event-fill',
    'url' => route('coordinator.exam-schedules.index'),
    'class' => 'warning-card',
    'btn_class' => 'warning-btn'
]
```

**Staff Menu:**
```php
private function getStaffMenu()
{
    return [
        'title' => 'Staff Dashboard',
        'items' => [
            [
                'title' => 'ตารางสอบโครงงาน',
                'description' => 'ดูตารางสอบทั้งหมด',
                'icon' => 'bi-calendar-event-fill',
                'url' => route('staff.exam-schedules'),
                'class' => 'info-card',
                'btn_class' => 'info-btn'
            ]
        ]
    ];
}
```

---

## ฟีเจอร์พิเศษ

### 1. Multiple Project Selection
- เลือกหลายโครงงานพร้อมกัน
- กำหนดเวลาสอบเดียวกันสำหรับทุกโครงงานที่เลือก
- ประหยัดเวลาในการสร้างตารางสอบ

### 2. Search & Filter
**ในหน้า Create:**
- ค้นหาโครงงานแบบ real-time
- กรองโครงงานที่ยังไม่มีตารางสอบ

**ในหน้า Calendar:**
- Filter by สถานะโครงงาน (active, completed, cancelled)
- Filter by สถานที่สอบ
- ค้นหาชื่อโครงงานหรือหมายเหตุ

### 3. Timeline View
- แสดงตารางสอบ grouped by วันที่
- เรียงลำดับตามเวลา
- แสดงข้อมูลครบถ้วนในรูปแบบการ์ด

### 4. AJAX Delete
- ลบตารางสอบโดยไม่ต้อง reload page
- Confirmation dialog ก่อนลบ
- แสดง success/error message

### 5. Duration Display
- คำนวณระยะเวลาสอบอัตโนมัติ
- แสดงเป็นชั่วโมงและนาที
- Badge สีฟ้าสำหรับความโดดเด่น

---

## Validation Rules

### Create/Update Exam Schedule
```php
$request->validate([
    'project_ids' => 'required|array',           // สำหรับ create (multiple)
    'project_ids.*' => 'exists:projects,project_id',
    'exam_date' => 'required|date',
    'start_time' => 'required|date_format:H:i',
    'end_time' => 'required|date_format:H:i|after:start_time',
    'location' => 'nullable|string|max:200',
    'notes' => 'nullable|string',
]);
```

### Update System Settings
```php
$request->validate([
    'system_status' => 'required|in:open,closed',
    'current_year' => 'required|integer|min:2500|max:2600',
    'current_semester' => 'required|integer|in:1,2,3',
]);
```

---

## การจัดการ Migration

### Consolidated Migrations
รวม migrations ที่เกี่ยวข้องกันเข้าไว้ด้วยกัน:

**ไฟล์เดิม (12 files):**
1. create_sessions_table.php
2. create_user_table.php
3. create_student_table.php
4. create_login_logs_table.php
5. create_groups_and_members_tables.php
6. create_group_invitations_table.php
7. create_user_role_table.php
8. create_projects_table.php
9. create_project_proposals_table.php
10. add_submission_fields_to_projects_table.php
11. create_exam_schedule_table.php
12. create_system_settings_table.php

**ไฟล์ใหม่ (8 files):**
1. create_sessions_table.php
2. create_user_table.php
3. create_student_table.php
4. create_login_logs_table.php
5. **create_groups_related_tables.php** (รวม 3 ตาราง)
6. create_user_role_table.php
7. **create_projects_related_tables.php** (รวม 2 ตาราง + submission fields)
8. **create_exam_schedule_and_system_settings_tables.php** (รวม 2 ตาราง)

**ประโยชน์:**
- ลดจำนวนไฟล์ migration
- จัดกลุ่มตามความเกี่ยวข้อง
- ง่ายต่อการบำรุงรักษา
- Migration ทำงานถูกต้องตามลำดับ dependency

---

## Testing

### Seeder Data
เพิ่มข้อมูล Staff user สำหรับทดสอบ:

```php
DB::table('user')->updateOrInsert(
    ['username_user' => 'staff'],
    [
        'firstname_user' => 'เจ้าหน้าที่',
        'lastname_user' => 'ทดสอบ',
        'user_code' => 'STF',
        'role' => 4096,
        'email_user' => 'staff@cstu.ac.th',
        'password_user' => Hash::make('staff123'),
    ]
);
```

### Test Accounts
| Role | Username | Password | Permissions |
|------|----------|----------|-------------|
| Admin | admin | admin123 | Full CRUD on exam schedules |
| Coordinator | coordinator | coordinator123 | Full CRUD on exam schedules |
| Staff | staff | staff123 | Read-only on exam schedules |
| Advisor | advisor | advisor123 | N/A for exam schedules |

### Migration Testing
```bash
php artisan migrate:fresh --seed
```

**ผลลัพธ์:**
- ✅ 8 migrations executed successfully
- ✅ 12 tables created
- ✅ 3 seeders executed (UserRole, UserTable, StudentTable)
- ✅ Default system settings inserted

---

## UI/UX Design

### Design Theme
ใช้ธีมเดียวกับหน้า Admin Logs:
- Bootstrap Cards
- Table Dark header
- Clean และ minimal design
- Responsive layout

### Color Scheme
- **Primary Blue**: สำหรับปุ่มหลัก
- **Success Green**: สำหรับปุ่มดูปฏิทิน
- **Warning Yellow**: สำหรับปุ่มแก้ไข
- **Danger Red**: สำหรับปุ่มลบ
- **Info Blue**: สำหรับ badge ระยะเวลา

### Icons (Bootstrap Icons)
- `bi-calendar-event-fill`: ตารางสอบ
- `bi-plus-circle`: เพิ่ม
- `bi-calendar3`: ปฏิทิน
- `bi-pencil`: แก้ไข
- `bi-trash`: ลบ
- `bi-clock`: เวลา
- `bi-geo-alt-fill`: สถานที่

---

## Security Considerations

### 1. Permission Checks
ทุก method ใน controller มี permission check:
```php
if (!PermissionHelper::isAdmin() && !PermissionHelper::isCoordinator()) {
    return redirect()->route('menu')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
}
```

### 2. CSRF Protection
ทุก form มี CSRF token:
```php
@csrf
```

### 3. Input Validation
ทุก input มี validation rules

### 4. SQL Injection Prevention
ใช้ Eloquent ORM และ Query Builder

### 5. XSS Prevention
ใช้ Blade templating engine (auto-escape)

---

## Performance Optimization

### 1. Database Indexes
```php
$table->index('ex_start_time');  // สำหรับค้นหาตามวันที่
$table->unique('setting_key');    // สำหรับค้นหา settings
```

### 2. Eager Loading
```php
$examSchedules = ExamSchedule::with('project')->get();
```

### 3. Pagination
```php
$examSchedules->paginate(20);
```

### 4. Caching (Future Enhancement)
พิจารณาใช้ cache สำหรับ system settings

---

## Future Enhancements

### 1. Student View
- นักศึกษาสามารถดูตารางสอบของโครงงานตัวเอง

### 2. Notifications
- แจ้งเตือนเมื่อมีการเพิ่ม/แก้ไขตารางสอบ
- Email notification ถึงนักศึกษาและอาจารย์

### 3. Calendar Export
- Export เป็น iCal format
- Sync กับ Google Calendar

### 4. Conflict Detection
- ตรวจสอบการชนกันของเวลาสอบ
- แจ้งเตือนเมื่อสถานที่ซ้ำกันในเวลาเดียวกัน

### 5. Bulk Operations
- ลบหลายตารางสอบพร้อมกัน
- Export to PDF/Excel

### 6. Advanced Filters
- Filter by อาจารย์ที่ปรึกษา
- Filter by ประเภทโครงงาน
- Filter by ช่วงวันที่

### 7. Mobile Responsive
- ปรับปรุง UI สำหรับ mobile
- Native mobile app

---

## Technical Stack

### Backend
- **Framework**: Laravel 10.x
- **Language**: PHP 8.2
- **Database**: MySQL 8.0
- **ORM**: Eloquent

### Frontend
- **Template Engine**: Blade
- **CSS Framework**: Bootstrap 5
- **Icons**: Bootstrap Icons
- **JavaScript**: Vanilla JS (AJAX)

### Tools
- **Version Control**: Git
- **Repository**: GitHub
- **Development Server**: XAMPP
- **Terminal**: PowerShell

---

## Installation & Setup

### Prerequisites
- PHP >= 8.2
- MySQL >= 8.0
- Composer
- Node.js & npm (สำหรับ assets compilation)

### Installation Steps
```bash
# 1. Clone repository
git clone https://github.com/Phurinat-Musikanon-6509650658/CSTU_SPACE_PHASE1.git
cd CSTU_SPACE_PHASE1

# 2. Checkout feature branch
git checkout feature_menu

# 3. Install dependencies
composer install

# 4. Configure environment
cp .env.example .env
# แก้ไข DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 5. Generate app key
php artisan key:generate

# 6. Run migrations and seeders
php artisan migrate:fresh --seed

# 7. Start development server
php artisan serve
```

### Access URLs
- **Application**: http://127.0.0.1:8000
- **Admin**: Login with admin / admin123
- **Coordinator**: Login with coordinator / coordinator123
- **Staff**: Login with staff / staff123

---

## Conclusion

ระบบจัดการตารางสอบโครงงานได้รับการพัฒนาสำเร็จตามวัตถุประสงค์ โดยรองรับการทำงานของ 3 role หลัก (Admin, Coordinator, Staff) ด้วยสิทธิ์การใช้งานที่แตกต่างกัน ระบบมีความยืดหยุ่น ปลอดภัย และใช้งานง่าย พร้อมสำหรับการพัฒนาต่อยอดในอนาคต

**ไฮไลท์:**
- ✅ Multi-role support (Admin, Coordinator, Staff)
- ✅ Full CRUD operations สำหรับ Admin และ Coordinator
- ✅ Read-only view สำหรับ Staff
- ✅ Multiple project selection
- ✅ Calendar timeline view
- ✅ Advanced filtering
- ✅ Consolidated migrations
- ✅ Clean code architecture
- ✅ Comprehensive permission system

**Developed by:** Phurinat Musikanon  
**Date:** November 27, 2025  
**Version:** 1.0.0  
**Branch:** feature_menu  
**Commit:** 7dd64bb
