# CSTU_SPACE - Laravel Docker Setup

## 📋 Overview
CSTU Space เป็นระบบจัดการโครงงานนักศึกษาที่ใช้ Laravel Framework พร้อม Docker setup สำหรับการพัฒนา

## 🛠️ Tech Stack
- **Backend**: Laravel 12.x (PHP 8.2)
- **Frontend**: Vite + TailwindCSS
- **Database**: MySQL 8.0
- **Web Server**: Nginx
- **Containerization**: Docker & Docker Compose

## 📁 Project Structure
```
CSTU_SPACE/
├── docker/                 # Docker configuration
│   ├── docker-compose.yml  # Main compose file
│   ├── Dockerfile          # PHP-FPM container
│   ├── nginx/              # Nginx configuration
│   ├── php/                # PHP configuration
│   └── mysql/              # MySQL configuration
├── app/                    # Laravel application
├── database/               # Migrations & seeders
└── resources/              # Frontend assets
```

## 🚀 Quick Start

### Prerequisites
- Docker Desktop installed and running
- Git

### Installation Steps

#### 1. Clone Repository
```bash
git clone https://github.com/Phurinat-Musikanon-6509650658/CSTU_SPACE_PHASE1.git
cd CSTU_SPACE_PHASE1
```

#### 2. Install Composer Dependencies
**ต้องทำก่อน!** ติดตั้ง dependencies ผ่าน Docker:
```bash
cd docker
docker-compose run --rm app composer install
```

#### 3. Setup Environment File
```bash
# Copy environment file (หรือใช้ .env.docker)
cd ..
copy .env.docker .env

# หรือ ใน Linux/Mac
cp .env.docker .env
```

#### 4. Generate Application Key
```bash
cd docker
docker-compose run --rm app php artisan key:generate
```

#### 5. Start Docker Services
```bash
docker-compose up -d
```

#### 6. Setup Database & Permissions
```bash
# สร้างตาราง jobs สำหรับ queue system
docker-compose exec app php artisan queue:table

# Run migrations
docker-compose exec app php artisan migrate

# แก้ไข permissions
docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Clear และ optimize
docker-compose exec app php artisan optimize:clear
```

#### 7. Access Application
- **Main Website**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081 (username: `root`, password: `rootpassword`)
- **Vite Dev Server**: http://localhost:5173

### ⚠️ หากเจอ Error 500 หรือ Permission Denied
```bash
# แก้ไข permissions
cd docker
docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Clear cache
docker-compose exec app php artisan optimize:clear

# Restart containers
docker-compose restart app queue webserver
```

## 🎮 Docker Commands

### Starting & Stopping Services

#### Start Services
```bash
# Start all services
docker-compose up -d

# Start with rebuild (after Dockerfile changes)
docker-compose up -d --build

# Start specific service
docker-compose up -d app
```

#### Stop Services
```bash
# Stop all services (containers remain)
docker-compose stop

# Stop and remove containers
docker-compose down

# Stop and remove containers + volumes (⚠️ DATA LOSS)
docker-compose down -v
```

#### Restart Services
```bash
# Restart all services
docker-compose restart

# Restart specific service
docker-compose restart app
```

### Service Status
```bash
# Check running containers
docker-compose ps

# View logs
docker-compose logs -f app
docker-compose logs -f webserver

# Execute commands in container
docker-compose exec app bash
```

## 🗄️ Database Management

### Migration Commands
```bash
# Check migration status
docker-compose exec app php artisan migrate:status

# Run new migrations
docker-compose exec app php artisan migrate

# Rollback last migration batch
docker-compose exec app php artisan migrate:rollback

# Rollback specific steps
docker-compose exec app php artisan migrate:rollback --step=3

# Reset all migrations
docker-compose exec app php artisan migrate:reset

# Fresh install (drop all tables + migrate)
docker-compose exec app php artisan migrate:fresh

# Fresh install with seeding
docker-compose exec app php artisan migrate:fresh --seed
```

### Seeding Commands
```bash
# Run all seeders
docker-compose exec app php artisan db:seed

# Run specific seeder
docker-compose exec app php artisan db:seed --class=UserTableSeeder

# Create new seeder
docker-compose exec app php artisan make:seeder TableNameSeeder
```

### Creating Migrations
```bash
# Create new migration
docker-compose exec app php artisan make:migration create_table_name

# Create migration for existing table
docker-compose exec app php artisan make:migration add_column_to_table --table=table_name
```

## 🧹 Cache & Optimization

### Clear Caches
```bash
# Clear all caches
docker-compose exec app php artisan optimize:clear

# Clear specific caches
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan cache:clear
```

### Optimize Application
```bash
# Optimize for production
docker-compose exec app php artisan optimize

# Cache configurations
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

## 🔧 Development Workflow

### Daily Development
```bash
# Morning: Start containers
docker-compose start

# Evening: Stop containers
docker-compose stop
```

### Code Changes
- **PHP/Blade files**: Auto-reload ✅
- **CSS/JS files**: Auto-reload via Vite ✅
- **Config files**: `docker-compose exec app php artisan config:clear`
- **Routes**: Auto-reload ✅

### When to Rebuild
- Dockerfile changes: `docker-compose up -d --build`
- New PHP packages: `docker-compose up -d --build`
- New NPM packages: `docker-compose up -d --build`

## 🐛 Troubleshooting

### Common Issues

#### 1. Error: vendor/autoload.php not found
**สาเหตุ**: ยังไม่ได้ติดตั้ง composer dependencies

**แก้ไข**:
```bash
cd docker
docker-compose run --rm app composer install
```

#### 2. Error 500 - Server Error
**สาเหตุ**: ไม่มีไฟล์ .env หรือ application key

**แก้ไข**:
```bash
# สร้าง .env file
copy .env.docker .env  # Windows
# หรือ cp .env.docker .env  # Linux/Mac

cd docker
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan config:clear
docker-compose restart app webserver
```

#### 3. Permission Denied - storage/framework/views
**สาเหตุ**: ไม่มีสิทธิ์เขียนไฟล์ใน storage

**แก้ไข**:
```bash
cd docker
docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
docker-compose exec app php artisan view:clear
```

#### 4. Redis Connection Error
**สาเหตุ**: Project นี้ไม่ได้ใช้ Redis แล้ว (ใช้ file/database แทน)

**แก้ไข**: ตรวจสอบไฟล์ .env ให้มีค่าดังนี้:
```env
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
```

#### 5. Port Already in Use
```bash
# Check what's using the port
netstat -an | findstr :8080  # Windows
# หรือ lsof -i :8080  # Linux/Mac

# Use different port in docker-compose.yml
ports:
  - "8081:80"  # Change from 8080 to 8081
```

#### 6. Database Connection Issues
```bash
# Check database container
cd docker
docker-compose logs db

# Reset database
docker-compose exec app php artisan migrate:fresh --seed
```

#### 7. Clear Everything and Start Fresh
```bash
cd docker

# Stop and remove everything
docker-compose down -v

# Start fresh installation
docker-compose run --rm app composer install
docker-compose up -d
docker-compose exec app php artisan migrate
docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
```

## 🌐 Production Deployment

### Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Update production settings in .env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database settings
DB_HOST=your-db-host
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-secure-password
```

### Production Commands
```bash
# Optimize for production
docker-compose exec app composer install --optimize-autoloader --no-dev
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

# Run migrations (be careful!)
docker-compose exec app php artisan migrate --force
```

## 📞 Support

### Useful Commands
```bash
# Enter Laravel Tinker
docker-compose exec app php artisan tinker

# Check Laravel version
docker-compose exec app php artisan --version

# List all artisan commands
docker-compose exec app php artisan list

# Database access
docker-compose exec db mysql -u root -prootpassword cstu_space
```

### Links
- **Laravel Documentation**: https://laravel.com/docs
- **Docker Documentation**: https://docs.docker.com
- **Project Repository**: https://github.com/Phurinat-Musikanon-6509650658/CSTU_SPACE_PHASE1

---

## 📝 Notes
- Default MySQL credentials: `root` / `rootpassword`
- phpMyAdmin access: http://localhost:8081 (use above credentials)
- Development server uses **file-based cache/session** and **database queue** (no Redis required)
- All data persists between container restarts (unless using `docker-compose down -v`)
- **ต้องรัน `composer install` ก่อนเสมอ** เมื่อ clone project ใหม่
- ไฟล์ `.env` ถูก ignore โดย git - ใช้ `.env.docker` หรือ `.env.example` เป็น template

## 🔑 Default Configuration
```env
# Database
DB_HOST=db
DB_PORT=3306
DB_DATABASE=cstu_space
DB_USERNAME=root
DB_PASSWORD=rootpassword

# Cache & Session (ไม่ต้องใช้ Redis)
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
```

**Happy Coding! 🚀**