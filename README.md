# CSTU Space — ระบบจัดการโครงงานนักศึกษา

> **Branch:** `CSTU-SPACE-prod` — production branch
> **URL:** http://203.131.208.27
> **Dev branch:** `CSTU-SPACE-dev`

**CSTU Space** เป็นระบบจัดการโครงงานพิเศษ (CS303 / CS403) สำหรับภาควิชาวิทยาการคอมพิวเตอร์ รองรับ workflow ตั้งแต่นักศึกษาสร้างกลุ่ม เสนอหัวข้อ ส่งรายงาน จนถึงอาจารย์ประเมินคะแนน

---

## Deploy (Production Server)

### Prerequisites
- Docker & Docker Compose ติดตั้งบน server
- Git
- Port 80 เปิดอยู่

### ครั้งแรก (First Deploy)

```bash
git clone https://github.com/Phurinat-Musikanon-6509650658/CSTU_SPACE_PHASE1.git
cd CSTU_SPACE_PHASE1
git checkout CSTU-SPACE-prod
chmod +x deploy.sh
./deploy.sh
```

### อัปเดต (Re-deploy)

```bash
./deploy.sh
```

Script จะทำทุกขั้นตอนอัตโนมัติ:
1. `git pull` จาก CSTU-SPACE-prod
2. ตั้งค่า `.env` จาก `.env.docker`
3. `composer install --no-dev`
4. Build frontend assets (`npm run build`)
5. Start containers (production mode)
6. `php artisan migrate --force`
7. `php artisan optimize`
8. Fix storage permissions

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12.x / PHP 8.2 |
| Frontend | Blade + Bootstrap 5 + Vite |
| Database | MySQL 8.0 |
| Web Server | Nginx |
| Container | Docker & Docker Compose |

---

## Features

- **RBAC แบบ Bitmask** — Admin (32768), Coordinator (16384), Lecturer (8192), Staff (4096), Student (2048)
- **Dual Auth Guard** — `web` guard สำหรับ Staff/Admin/Lecturer, `student` guard สำหรับนักศึกษา
- **Dynamic Evaluation Criteria** — เกณฑ์คะแนนเปลี่ยนตาม subject_code (CS303 / CS403)
- **Subject-based Access Control** — แต่ละรายวิชามีช่วงเวลาเปิด-ปิดเป็นอิสระ
- **Project Lecturers Pivot** — รองรับ co-advisor, committee หลายคนต่อโครงงาน
- **PDF Export** — ใบประเมินรายโครงงานและ export รวม
- **XLSX Import/Export** — นำเข้า/ส่งออกข้อมูลโครงงานด้วย Excel

---

## Project Structure

```
CSTU_SPACE_PHASE1/
├── app/
│   ├── Http/Controllers/       # Admin, Coordinator, Lecturer, Staff, Student
│   ├── Models/                 # Eloquent Models (รวม EvaluationCriteria)
│   └── Helpers/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/views/
├── docker/
│   ├── docker-compose.yml          # base config
│   ├── docker-compose.prod.yml     # production overrides
│   ├── Dockerfile
│   ├── nginx/default.conf
│   ├── php/local.ini
│   └── mysql/my.cnf
├── deploy.sh                   # one-command deploy script
├── .env.docker                 # production env template
└── ER_DIAGRAM.md
```

---

## Docker (Manual)

```bash
cd docker

# Production mode (MySQL port ไม่ถูก expose)
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d

# Dev mode (MySQL port 3306 เปิด, phpMyAdmin พร้อมใช้)
docker-compose up -d

# phpMyAdmin บน production (เปิดเฉพาะเมื่อต้องการ)
docker-compose --profile tools up -d phpmyadmin
```

---

## Default Login Accounts

> **หมายเหตุ:** สร้างโดย seeder สำหรับทดสอบเท่านั้น

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `admin123` |
| Coordinator | `coordinator` | `coordinator123` |
| Lecturer | `ckasidit` | `kdc2025` |
| Staff | `staff` | `staff123` |
| Student | `student` | `student123` |

---

## Database

### Migration Files

| ไฟล์ | Tables |
|---|---|
| `100001_create_core_tables` | user, student, user_role, login_logs, sessions, jobs |
| `100002_create_group_tables` | groups, group_members, group_invitations |
| `100003_create_project_tables` | relationship_with_projects, projects, project_proposals, project_evaluations |
| `100004_create_exam_and_lecturer_tables` | exam_schedule, project_lecturers |
| `100005_create_subjects_table` | subjects |
| `2026_05_17_..._create_evaluation_criteria_table` | evaluation_criteria |

### Useful Commands

```bash
cd docker

# Fresh reset + seed
docker-compose exec app php artisan migrate:fresh --seed

# Migration status
docker-compose exec app php artisan migrate:status

# Run specific seeder
docker-compose exec app php artisan db:seed --class=EvaluationCriteriaSeeder

# Clear all cache
docker-compose exec app php artisan optimize:clear

# View logs
docker-compose logs -f app
docker-compose logs -f webserver

# Enter shell
docker-compose exec app bash

# MySQL CLI
docker-compose exec db mysql -u root -prootpassword cstu_space
```

---

## Troubleshooting

### Permission Denied (storage)
```bash
cd docker
docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
```

### vendor/autoload.php not found
```bash
cd docker
docker-compose run --rm app composer install --no-dev --optimize-autoloader
```

### Database error / ตาราง missing
```bash
cd docker
docker-compose exec app php artisan migrate:fresh --seed
```

### Reset ทุกอย่าง
```bash
cd docker
docker-compose down -v
./deploy.sh
```

---

## Links

- **Repository**: https://github.com/Phurinat-Musikanon-6509650658/CSTU_SPACE_PHASE1
- **Production**: http://203.131.208.27
- **Dev Branch**: https://github.com/Phurinat-Musikanon-6509650658/CSTU_SPACE_PHASE1/tree/CSTU-SPACE-dev
- **ER Diagram**: [ER_DIAGRAM.md](./ER_DIAGRAM.md)
