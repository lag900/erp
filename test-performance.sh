#!/bin/bash

# ========================================
# PERFORMANCE TEST SCRIPT
# ========================================

echo "🔍 Running Performance Tests..."
echo ""

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Test 1: Check if production optimizations are enabled
echo -e "${BLUE}Test 1: Production Configuration${NC}"

if grep -q "APP_ENV=production" .env 2>/dev/null; then
    echo -e "  ${GREEN}✓${NC} APP_ENV is set to production"
else
    echo -e "  ${YELLOW}⚠${NC} APP_ENV is not set to production"
fi

if grep -q "APP_DEBUG=false" .env 2>/dev/null; then
    echo -e "  ${GREEN}✓${NC} APP_DEBUG is disabled"
else
    echo -e "  ${YELLOW}⚠${NC} APP_DEBUG is still enabled"
fi

echo ""

# Test 2: Check if caches exist
echo -e "${BLUE}Test 2: Laravel Caches${NC}"

if [ -f "bootstrap/cache/config.php" ]; then
    echo -e "  ${GREEN}✓${NC} Config cache exists"
else
    echo -e "  ${YELLOW}⚠${NC} Config cache missing (run: php artisan config:cache)"
fi

if [ -f "bootstrap/cache/routes-v7.php" ]; then
    echo -e "  ${GREEN}✓${NC} Route cache exists"
else
    echo -e "  ${YELLOW}⚠${NC} Route cache missing (run: php artisan route:cache)"
fi

echo ""

# Test 3: Check if production build exists
echo -e "${BLUE}Test 3: Frontend Build${NC}"

if [ -d "public/build" ]; then
    echo -e "  ${GREEN}✓${NC} Production build exists"
    BUILD_SIZE=$(du -sh public/build 2>/dev/null | cut -f1)
    echo -e "  ${BLUE}ℹ${NC} Build size: ${BUILD_SIZE}"
else
    echo -e "  ${RED}✗${NC} Production build missing (run: npm run build)"
fi

echo ""

# Test 4: Check storage link
echo -e "${BLUE}Test 4: Storage Link${NC}"

if [ -L "public/storage" ]; then
    echo -e "  ${GREEN}✓${NC} Storage link exists"
else
    echo -e "  ${YELLOW}⚠${NC} Storage link missing (run: php artisan storage:link)"
fi

echo ""

# Test 5: Check permissions
echo -e "${BLUE}Test 5: File Permissions${NC}"

if [ -w "storage" ] && [ -w "bootstrap/cache" ]; then
    echo -e "  ${GREEN}✓${NC} Storage and cache directories are writable"
else
    echo -e "  ${RED}✗${NC} Permission issues (run: chmod -R 775 storage bootstrap/cache)"
fi

echo ""

# Test 6: Image optimization service
echo -e "${BLUE}Test 6: Optimization Services${NC}"

if [ -f "app/Services/ImageOptimizationService.php" ]; then
    echo -e "  ${GREEN}✓${NC} ImageOptimizationService exists"
else
    echo -e "  ${RED}✗${NC} ImageOptimizationService missing"
fi

if [ -f "app/Http/Middleware/CompressResponse.php" ]; then
    echo -e "  ${GREEN}✓${NC} CompressResponse middleware exists"
else
    echo -e "  ${RED}✗${NC} CompressResponse middleware missing"
fi

echo ""

# Test 7: Check image storage
echo -e "${BLUE}Test 7: Image Storage${NC}"

if [ -d "storage/app/public" ]; then
    IMAGE_COUNT=$(find storage/app/public -type f \( -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.webp" \) 2>/dev/null | wc -l)
    WEBP_COUNT=$(find storage/app/public -type f -name "*.webp" 2>/dev/null | wc -l)
    
    echo -e "  ${BLUE}ℹ${NC} Total images: ${IMAGE_COUNT}"
    echo -e "  ${BLUE}ℹ${NC} WebP images: ${WEBP_COUNT}"
    
    if [ $IMAGE_COUNT -gt 0 ] && [ $WEBP_COUNT -eq 0 ]; then
        echo -e "  ${YELLOW}⚠${NC} No WebP images found (run: php artisan images:optimize)"
    elif [ $WEBP_COUNT -gt 0 ]; then
        PERCENTAGE=$((WEBP_COUNT * 100 / IMAGE_COUNT))
        echo -e "  ${GREEN}✓${NC} ${PERCENTAGE}% images are optimized to WebP"
    fi
fi

echo ""

# Test 8: Composer optimization
echo -e "${BLUE}Test 8: Composer Autoloader${NC}"

if [ -f "vendor/composer/autoload_classmap.php" ]; then
    CLASSMAP_SIZE=$(wc -l < vendor/composer/autoload_classmap.php)
    if [ $CLASSMAP_SIZE -gt 100 ]; then
        echo -e "  ${GREEN}✓${NC} Autoloader is optimized (${CLASSMAP_SIZE} classes)"
    else
        echo -e "  ${YELLOW}⚠${NC} Autoloader may need optimization (run: composer dump-autoload -o)"
    fi
else
    echo -e "  ${RED}✗${NC} Composer autoloader not found"
fi

echo ""

# Summary
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Performance Test Complete${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}Recommendations:${NC}"
echo "  1. Ensure all tests pass before production deployment"
echo "  2. Run './deploy-production.sh' for automated optimization"
echo "  3. Test on mobile device with 4G simulation"
echo "  4. Monitor logs after deployment"
echo ""
