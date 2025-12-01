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
- Docker Desktop installed
- Git

### 1. Clone Repository
```bash
git clone https://github.com/Phurinat-Musikanon-6509650658/CSTU_SPACE_PHASE1.git
cd CSTU_SPACE_PHASE1
```

### 2. Start Docker Services
```bash
cd docker
docker-compose up -d
```

### 3. Setup Laravel
```bash
# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations and seed database
docker-compose exec app php artisan migrate:fresh --seed

# Create storage symbolic link
docker-compose exec app php artisan storage:link
```

### 4. Access Application
- **Main Website**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
- **Vite Dev Server**: http://localhost:5173

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

#### Port Already in Use
```bash
# Check what's using the port
netstat -an | findstr :8080

# Use different port in docker-compose.yml
ports:
  - "8081:80"  # Change from 8080 to 8081
```

#### Permission Issues
```bash
# Fix storage permissions
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

#### Database Connection Issues
```bash
# Check database container
docker-compose logs db

# Reset database
docker-compose exec app php artisan migrate:fresh --seed
```

#### Clear Everything and Start Fresh
```bash
# Stop and remove everything
docker-compose down -v
docker system prune -f

# Start fresh
docker-compose up -d --build
docker-compose exec app php artisan migrate:fresh --seed
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
- phpMyAdmin access: http://localhost:8081
- Development server uses file-based sessions and cache for simplicity
- All data persists between container restarts (unless using `docker-compose down -v`)

**Happy Coding! 🚀**