# CSTU Space — ระบบจัดการโครงงานนักศึกษา

**CSTU Space** เป็นระบบจัดการโครงงานพิเศษ (CS303 / CS403) สำหรับภาควิชาวิทยาการคอมพิวเตอร์ รองรับ workflow ตั้งแต่นักศึกษาสร้างกลุ่ม เสนอหัวข้อ ส่งรายงาน จนถึงอาจารย์ประเมินคะแนน

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12.x / PHP 8.2 |
| Frontend | Blade + TailwindCSS + Vite |
| Database | MySQL 8.0 |
| Web Server | Nginx |
| Container | Docker & Docker Compose |

---

## Features

- **RBAC แบบ Bitmask** — Admin (32768), Coordinator (16384), Lecturer (8192), Staff (4096), Student (2048) บวกรวมกันได้ เช่น Coordinator+Lecturer = 24576
- **Dual Auth Guard** — `web` guard สำหรับ Staff/Admin/Lecturer, `student` guard สำหรับนักศึกษา
- **Subject-based Access Control** — แต่ละรายวิชา (CS303/CS403) มีช่วงเวลาเปิด-ปิดเป็นอิสระ ไม่มี global system close
- **Project Lecturers Pivot** — อาจารย์ที่ปรึกษาและกรรมการเก็บใน `project_lecturers` table แทน column ตรง ทำให้ยืดหยุ่นและรองรับ co-advisor ได้
- **PDF Submission** — นักศึกษา (หัวหน้ากลุ่ม) อัปโหลดรายงาน PDF
- **CSV Import/Export** — Coordinator นำเข้าตารางสอบและส่งออกข้อมูลโครงงาน

---

## Project Structure

```
CSTU_SPACE_PHASE1/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Admin, Coordinator, Lecturer, Staff, Student
│   │   └── Middleware/         # CheckSubjectAccess, RedirectIfAuthenticated
│   ├── Models/                 # Eloquent Models
│   └── Helpers/                # PermissionHelper, SubjectTimingHelper
├── database/
│   ├── migrations/             # 5 consolidated migration files
│   └── seeders/                # User, Student, Subject, Role, Relationship seeders
├── resources/views/            # Blade templates (coordinator/lecturer/student/staff)
├── docker/
│   ├── docker-compose.yml
│   ├── Dockerfile
│   ├── nginx/
│   ├── php/
│   └── mysql/
├── routes/web.php
└── ER_DIAGRAM.md               # ER Diagram (Mermaid) + คำอธิบาย schema
```

---

## Quick Start

### Prerequisites
- Docker Desktop (running)
- Git

### 1. Clone & Setup Environment

```bash
git clone https://github.com/Phurinat-Musikanon-6509650658/CSTU_SPACE_PHASE1.git
cd CSTU_SPACE_PHASE1

# Windows
copy .env.docker .env

# Linux / macOS
cp .env.docker .env
```

### 2. Install Dependencies

```bash
cd docker
docker-compose run --rm app composer install
```

### 3. Generate App Key

```bash
docker-compose run --rm app php artisan key:generate
```

### 4. Start Containers

```bash
docker-compose up -d
```

### 5. Run Migrations & Seed

```bash
# สร้างตารางทั้งหมดและใส่ข้อมูลเริ่มต้น
docker-compose exec app php artisan migrate:fresh --seed

# Fix permissions
docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
```

### 6. เข้าใช้งาน

| Service | URL |
|---|---|
| Web App | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 |
| Vite Dev | http://localhost:5173 |

---

## Default Login Accounts

> **หมายเหตุ:** บัญชีเหล่านี้สร้างโดย seeder สำหรับพัฒนาและทดสอบเท่านั้น

### Staff / Admin / Lecturer

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `admin123` |
| Coordinator | `coordinator` | `coordinator123` |
| Lecturer (ตัวอย่าง) | `ckasidit` | `kdc2025` |
| Staff | `staff` | `staff123` |

### นักศึกษา

| Username | Password |
|---|---|
| `student` | `student123` |

---

## Database

### Schema (5 Migration Files)

| ไฟล์ | Tables |
|---|---|
| `100001_create_core_tables` | user, student, user_role, login_logs, sessions, jobs |
| `100002_create_group_tables` | groups, group_members, group_invitations |
| `100003_create_project_tables` | relationship_with_projects, projects, project_proposals, project_evaluations |
| `100004_create_exam_and_lecturer_tables` | exam_schedule, project_lecturers |
| `100005_create_subjects_table` | subjects |

ดู schema เต็มและ ER Diagram ได้ที่ [ER_DIAGRAM.md](./ER_DIAGRAM.md)

### Migration Commands

```bash
cd docker

# Fresh reset (ลบทุก table แล้วสร้างใหม่ + seed)
docker-compose exec app php artisan migrate:fresh --seed

# รัน migration ใหม่ที่ยังไม่ได้รัน
docker-compose exec app php artisan migrate

# ดูสถานะ migration
docker-compose exec app php artisan migrate:status
```

### Seeder Commands

```bash
cd docker

# รัน seeder ทั้งหมด
docker-compose exec app php artisan db:seed

# รัน seeder เฉพาะตัว
docker-compose exec app php artisan db:seed --class=UserTableSeeder
docker-compose exec app php artisan db:seed --class=SubjectSeeder
```

**Seeder ที่มี:**

| Seeder | ข้อมูลที่ใส่ |
|---|---|
| `RelationshipWithProjectsSeeder` | บทบาทอาจารย์ 4 ประเภท (Advisor, Committee, Co-Advisor-Int/Ext) |
| `UserRoleSeeder` | Role definitions 8 รายการ |
| `UserTableSeeder` | Admin, Coordinator, Staff + อาจารย์ 19 ท่าน |
| `StudentTableSeeder` | นักศึกษาทดสอบ 9 คน |
| `SubjectSeeder` | CS303 และ CS403 |

---

## Docker Commands

```bash
cd docker

# Start / Stop / Restart
docker-compose up -d
docker-compose stop
docker-compose restart app

# ดู logs
docker-compose logs -f app
docker-compose logs -f webserver

# เข้า shell ของ container
docker-compose exec app bash

# Stop + ลบ container (เก็บ data volume)
docker-compose down

# Stop + ลบทุกอย่างรวม data (⚠️ ข้อมูลหาย)
docker-compose down -v
```

---

## Cache & Optimization

```bash
cd docker

# Clear ทุก cache (ใช้บ่อยระหว่าง dev)
docker-compose exec app php artisan optimize:clear

# Cache สำหรับ production
docker-compose exec app php artisan optimize
```

---

## Troubleshooting

### Error 500 / ไม่มี .env
```bash
copy .env.docker .env   # Windows
cp .env.docker .env     # Linux/Mac
cd docker
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan optimize:clear
docker-compose restart app
```

### Permission Denied (storage)
```bash
cd docker
docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
```

### vendor/autoload.php not found
```bash
cd docker
docker-compose run --rm app composer install
```

### Database error / ตาราง missing
```bash
cd docker
docker-compose exec app php artisan migrate:fresh --seed
```

### Port 8080 ถูกใช้อยู่
แก้ `docker/docker-compose.yml` บรรทัด `ports` ของ `webserver`:
```yaml
ports:
  - "8082:80"   # เปลี่ยนเป็น port ที่ว่าง
```

### Reset ทุกอย่าง (Fresh Install)
```bash
cd docker
docker-compose down -v
docker-compose run --rm app composer install
docker-compose up -d
docker-compose exec app php artisan migrate:fresh --seed
docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
```

---

## Default Configuration

```env
# App
APP_ENV=local
APP_DEBUG=true

# Database (ตรงกับ docker-compose.yml)
DB_HOST=db
DB_PORT=3306
DB_DATABASE=cstu_space
DB_USERNAME=root
DB_PASSWORD=rootpassword

# Cache & Session (ไม่ต้องใช้ Redis)
CACHE_STORE=file
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

---

## Useful Commands

```bash
cd docker

# Artisan tinker
docker-compose exec app php artisan tinker

# ดู routes ทั้งหมด
docker-compose exec app php artisan route:list

# Laravel version
docker-compose exec app php artisan --version

# เข้า MySQL โดยตรง
docker-compose exec db mysql -u root -prootpassword cstu_space
```

---

## Links

- **Repository**: https://github.com/Phurinat-Musikanon-6509650658/CSTU_SPACE_PHASE1
- **Laravel Docs**: https://laravel.com/docs/12.x
- **ER Diagram**: [ER_DIAGRAM.md](./ER_DIAGRAM.md)
