#!/bin/bash
# ══════════════════════════════════════════════════════════════
# Dunya Al-Atfaal — Production Deployment & Optimization Script
# Run via SSH on Hostinger: bash scripts/deploy-optimize.sh
# ══════════════════════════════════════════════════════════════

set -e

echo "🚀 بدء تحسين أداء الإنتاج..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Navigate to project root (adjust if different)
cd "$(dirname "$0")/.."

# ── Step 1: Clear all caches ─────────────────────────────────
echo ""
echo "🧹 مسح الكاش القديم..."
php artisan cache:clear 2>/dev/null || true
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan event:clear 2>/dev/null || true

# ── Step 2: Rebuild production caches ────────────────────────
echo ""
echo "⚡ بناء كاش الإنتاج..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# ── Step 3: Optimize autoloader ──────────────────────────────
echo ""
echo "📦 تحسين Composer Autoloader..."
composer dump-autoload --optimize --no-dev 2>/dev/null || echo "⚠️  تخطي composer (قد لا يكون متاحاً)"

# ── Step 4: Run pending migrations ───────────────────────────
echo ""
echo "🗄️ تشغيل الترحيلات المعلقة..."
php artisan migrate --force

# ── Step 5: Optimize database tables ─────────────────────────
echo ""
echo "📊 تحسين جداول قاعدة البيانات..."
php artisan optimize:performance --force 2>/dev/null || echo "⚠️ تخطي تحسين DB"

# ── Step 6: Set proper file permissions ──────────────────────
echo ""
echo "🔒 ضبط صلاحيات الملفات..."
find storage -type d -exec chmod 755 {} \; 2>/dev/null || true
find storage -type f -exec chmod 644 {} \; 2>/dev/null || true
find bootstrap/cache -type d -exec chmod 755 {} \; 2>/dev/null || true
find bootstrap/cache -type f -exec chmod 644 {} \; 2>/dev/null || true

# ── Step 7: Clean old sessions and logs ──────────────────────
echo ""
echo "🧹 تنظيف الجلسات والسجلات القديمة..."
find storage/framework/sessions -type f -mtime +1 -delete 2>/dev/null || true
find storage/logs -name "*.log" -mtime +7 -delete 2>/dev/null || true

# ── Step 8: Warmup cache ─────────────────────────────────────
echo ""
echo "🔥 تسخين الكاش..."
php artisan schedule:run 2>/dev/null || true

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ تم التحسين بنجاح!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
