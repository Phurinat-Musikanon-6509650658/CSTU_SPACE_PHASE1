#!/bin/bash
set -e

APP_URL="http://203.131.208.27"
BRANCH="CSTU-SPACE-prod"

echo ""
echo "=========================================="
echo "  CSTU Space — Production Deploy"
echo "  $APP_URL"
echo "=========================================="
echo ""

# ── 1. Pull latest ──────────────────────────────────────────────────
echo "[1/7] Pulling latest from $BRANCH..."
git pull origin $BRANCH

# ── 2. Setup environment ─────────────────────────────────────────────
echo "[2/7] Setting up .env..."
cp .env.docker .env

# ── 3. PHP dependencies (no dev) ─────────────────────────────────────
echo "[3/7] Installing PHP dependencies..."
cd docker
docker-compose run --rm app composer install --no-dev --optimize-autoloader --no-interaction

# ── 4. Build frontend assets ─────────────────────────────────────────
echo "[4/7] Building frontend assets..."
docker-compose run --rm node

# ── 5. Start / restart containers ───────────────────────────────────
echo "[5/7] Starting containers..."
docker-compose up -d --remove-orphans

# wait for db to be ready
echo "      Waiting for database..."
sleep 10

# ── 6. Migrate & optimize ───────────────────────────────────────────
echo "[6/7] Running migrations..."
docker-compose exec app php artisan migrate --force

echo "      Optimizing..."
docker-compose exec app php artisan optimize

# ── 7. Fix permissions ──────────────────────────────────────────────
echo "[7/7] Fixing storage permissions..."
docker-compose exec app chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

echo ""
echo "=========================================="
echo "  Deploy complete!"
echo "  App: $APP_URL"
echo "=========================================="
echo ""
