# Relationship with Projects System (Simplified)

## ภาพรวม
ระบบนี้จัดการความสัมพันธ์ระหว่าง Lecturer กับ Project โดยใช้ตาราง `projects` เป็นหลัก และ `relationship_with_projects` เป็นแค่ตาราง Reference

## โครงสร้างตาราง

### relationship_with_projects (Reference Table - ไม่มี FK)
| id | relationship | relationship_abbrev |
|----|--------------|---------------------|
| 1  | Advisor | Adv |
| 2  | Committee | Com |
| 3  | Co-Advisor-Internal | Co-Adv-Int |
| 4  | Co-Advisor-External | Co-Adv-Ext |

### projects (ตารางหลัก)
**เก็บเฉพาะ user_code ของ Lecturer เท่านั้น:**
- `advisor_code` (string) - user_code ของ Lecturer ที่เป็น Advisor (Auto-fill เมื่อ Lecturer อนุมัติ)
- `committee1_code` (string) - user_code ของ Lecturer ที่เป็น Committee 1 (Coordinator เลือก)
- `committee2_code` (string) - user_code ของ Lecturer ที่เป็น Committee 2 (Coordinator เลือก)
- `committee3_code` (string) - user_code ของ Lecturer ที่เป็น Committee 3 (Coordinator เลือก)

**❌ ไม่มี:** `advisor_id`, `comm1_id`, `comm2_id`, `comm3_id`

## กลไกการทำงาน

### 1. Auto-fill Advisor (เมื่อ Lecturer อนุมัติหัวข้อ)
**ที่:** `ProposalController@approve()`

เมื่อ Lecturer กด "อนุมัติ" ข้อเสนอหัวข้อโครงงาน:
```php
$proposal->group->project->update([
    'status_project' => 'approved',
    'advisor_code' => $user->user_code  // Auto-fill user_code ของ Lecturer
]);
```

### 2. Manual-assign Committees (Coordinator เลือกเอง)
Coordinator เลือก Committee 1, 2, 3 ผ่านหน้า UI

**ตัวอย่าง:**
```php
$project->update([
    'committee1_code' => 'ksa',  // user_code ของ Lecturer
    'committee2_code' => 'ppr',
    'committee3_code' => 'abc',
]);
```

## การแสดงผล

### ในหน้า Groups Detail
```
| AdvId    | Comm1    | Comm2    | Comm3    |
|----------|----------|----------|----------|
| ksa      | ppr      | abc      | xyz      |
| Adv      | Com      | Com      | Com      |
```

- Advisor แสดง badge สีม่วง + label "Adv"
- Committee แสดง badge สีเขียว + label "Com"

### ตัวอย่าง Code
```blade
@if($project->advisor_code)
    <span class="badge bg-primary">{{ $project->advisor_code }}</span>
    <br>
    <small>Adv</small>
@endif

@if($project->committee1_code)
    <span class="badge bg-success">{{ $project->committee1_code }}</span>
    <br>
    <small>Com</small>
@endif
```

## ไฟล์ที่เกี่ยวข้อง

### Migrations
- `2025_11_27_000001_create_relationship_with_projects_table.php` (Reference table เท่านั้น)

### Models
- `app/Models/RelationshipWithProject.php` (ไม่มี relationships กับ Project)
- `app/Models/Project.php` (มีแค่ relationships กับ User)

### Controllers
- `app/Http/Controllers/ProposalController.php` (Auto-fill advisor_code)
- `app/Http/Controllers/CoordinatorController.php` (Manual select committees)

## Validation Rules

**ห้ามซ้ำกัน:**
- ✅ Advisor ≠ Committee 1/2/3
- ✅ Committee 1 ≠ Committee 2 ≠ Committee 3
- ✅ ทุกตำแหน่งต้องเป็น Lecturer คนละคน

**Client-side (JavaScript):**
- Real-time validation เมื่อเลือก dropdown
- แสดง warning alert เมื่อเลือกซ้ำ
- ป้องกัน submit form ถ้าเลือกซ้ำ

**Server-side (PHP):**
```php
// ตรวจสอบไม่ซ้ำกัน
$lecturers = array_filter([
    $request->advisor_code,
    $request->committee1_code,
    $request->committee2_code,
    $request->committee3_code
]);

if (count($lecturers) !== count(array_unique($lecturers))) {
    return back()->with('error', 'ไม่สามารถเลือกอาจารย์คนเดียวกันในหลายตำแหน่งได้');
}
```

## วิธีติดตั้ง

```powershell
# Windows PowerShell
.\setup_relationships.ps1
```

หรือ

```bash
# Manual
php artisan migrate
php artisan db:seed --class=RelationshipWithProjectsSeeder
```

## สรุป

**ข้อดี:**
- ✅ โครงสร้างเรียบง่าย - ใช้ projects table เป็นหลัก
- ✅ ไม่ต้อง FK ซับซ้อน
- ✅ Advisor auto-fill เมื่อ Lecturer อนุมัติ
- ✅ Committee เลือกโดย Coordinator
- ✅ Validation ป้องกันซ้ำทั้ง client และ server

**สิ่งที่ต้องจำ:**
- `relationship_with_projects` เป็นแค่ตาราง Reference (ไม่มี FK)
- แสดง "Adv" และ "Com" แบบ hardcode ใน View
- Coordinator เป็นคนเลือก Committee ทั้งหมดเอง
