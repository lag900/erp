#!/bin/bash

# ========================================
# PRODUCTION OPTIMIZATION & DEPLOYMENT SCRIPT
# ========================================

echo "🚀 Starting Production Optimization..."

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Step 1: Laravel Optimizations
echo -e "${BLUE}📦 Step 1: Laravel Backend Optimization${NC}"

echo "  → Clearing all caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "  → Optimizing Composer autoloader..."
composer install --optimize-autoloader --no-dev --prefer-dist

echo "  → Caching configuration..."
php artisan config:cache

echo "  → Caching routes..."
php artisan route:cache

echo "  → Caching views..."
php artisan view:cache

echo "  → Optimizing event discovery..."
php artisan event:cache

echo -e "${GREEN}✓ Laravel optimization complete${NC}\n"

# Step 2: Frontend Build
echo -e "${BLUE}📦 Step 2: Frontend Production Build${NC}"

echo "  → Installing production dependencies..."
npm ci --production=false

echo "  → Building optimized assets..."
npm run build

echo -e "${GREEN}✓ Frontend build complete${NC}\n"

# Step 3: File Permissions
echo -e "${BLUE}📦 Step 3: Setting Permissions${NC}"

echo "  → Setting storage permissions..."
chmod -R 775 storage bootstrap/cache

echo "  → Setting ownership..."
chown -R www-data:www-data storage bootstrap/cache public/storage 2>/dev/null || echo "  ⚠ Could not set ownership (may need sudo)"

echo -e "${GREEN}✓ Permissions set${NC}\n"

# Step 4: Database Optimization
echo -e "${BLUE}📦 Step 4: Database Optimization${NC}"

echo "  → Running migrations..."
php artisan migrate --force

echo -e "${GREEN}✓ Database optimized${NC}\n"

# Step 5: Storage Link
echo -e "${BLUE}📦 Step 5: Storage Symlink${NC}"

echo "  → Creating storage link..."
php artisan storage:link

echo -e "${GREEN}✓ Storage linked${NC}\n"

# Final Summary
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✓ PRODUCTION OPTIMIZATION COMPLETE${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo "  1. Set APP_ENV=production in .env"
echo "  2. Set APP_DEBUG=false in .env"
echo "  3. Configure your web server (Nginx/Apache)"
echo "  4. Enable HTTPS/SSL"
echo "  5. Set up queue workers if using queues"
echo ""
echo -e "${GREEN}System is ready for production deployment! 🎉${NC}"
