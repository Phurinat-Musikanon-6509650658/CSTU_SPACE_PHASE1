# Docker Setup for CSTU_SPACE Laravel Application

## Prerequisites
- Docker Desktop installed on your system
- Docker Compose v3.8 or higher

## Services Included
- **app**: Laravel PHP-FPM application
- **webserver**: Nginx web server
- **db**: MySQL 8.0 database
- **phpmyadmin**: Database management interface
- **redis**: Redis cache and session store
- **node**: Node.js for Vite development server
- **queue**: Laravel queue worker

## Quick Start

### 1. Clone and Navigate
```bash
cd docker
```

### 2. Copy Environment File
```bash
cp ../.env.docker ../.env
```

### 3. Build and Start Services
```bash
docker-compose up -d --build
```

### 4. Install Dependencies and Setup Laravel
```bash
# Enter the app container
docker-compose exec app bash

# Inside the container:
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
```

## Access Points
- **Laravel Application**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
- **Vite Dev Server**: http://localhost:5173

## Useful Commands

### Start Services
```bash
docker-compose up -d
```

### Stop Services
```bash
docker-compose down
```

### View Logs
```bash
docker-compose logs -f app
docker-compose logs -f webserver
docker-compose logs -f db
```

### Execute Commands in App Container
```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan tinker
docker-compose exec app composer install
```

### Database Access
```bash
# Access MySQL directly
docker-compose exec db mysql -u root -prootpassword cstu_space
```

### Reset Everything
```bash
# Stop and remove all containers, networks, and volumes
docker-compose down -v
docker-compose up -d --build
```

## Database Configuration
- **Host**: db (internal) / localhost (external)
- **Port**: 3306
- **Database**: cstu_space
- **Username**: root
- **Password**: rootpassword

## Development Workflow

### Frontend Development
The Node.js service automatically runs `npm run dev` and serves Vite on port 5173. Your assets will be hot-reloaded during development.

### Backend Development
The app container mounts your local files, so changes are reflected immediately. No need to rebuild the container for code changes.

### Queue Processing
The queue worker runs automatically and processes jobs from the Redis queue.

## Troubleshooting

### Permission Issues
```bash
sudo chown -R $USER:$USER .
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Clear Laravel Caches
```bash
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear
```

### Rebuild Containers
```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```