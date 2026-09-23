# دليل النشر الإنتاجي المتقدم - متجر الأطفال الإلكتروني

## نظرة عامة

هذا الدليل يوفر تعليمات شاملة لنشر متجر الأطفال الإلكتروني في البيئة الإنتاجية مع تطبيق أفضل ممارسات الأمان والأداء.

**المطور**: حذيفة الحذيفي  
**التاريخ**: 2025-06-15  
**الإصدار**: 2.0

## 🚀 الميزات المُطبقة

### ✅ الأمان المتقدم
- Rate Limiting متعدد المستويات
- Security Headers شاملة
- تشفير الجلسات المتقدم
- Content Security Policy
- CSRF Protection محسن

### ✅ النسخ الاحتياطي الذكي
- نسخ احتياطي تلقائي لقاعدة البيانات
- نسخ احتياطي للملفات مع الضغط
- رفع تلقائي للسحابة
- إشعارات البريد الإلكتروني

### ✅ المراقبة والتنبيهات
- فحص صحة النظام التلقائي
- مراقبة الأداء
- تنبيهات فورية للمشاكل
- تقارير أسبوعية

## 📋 المتطلبات الأساسية

### متطلبات الخادم
```
- نظام التشغيل: Ubuntu 22.04 LTS
- المعالج: 4 CPU cores (8 مُوصى به)
- الذاكرة: 8GB RAM (16GB مُوصى به)
- التخزين: 100GB SSD (200GB مُوصى به)
- الشبكة: 1Gbps (مُوصى به)
```

### البرامج المطلوبة
```
- PHP 8.2+ مع جميع الإضافات
- MySQL 8.0+
- Redis 6.0+
- Nginx 1.20+
- Node.js 18+
- Composer 2.5+
- Git
- Certbot (للـ SSL)
```

## 🔧 خطوات التثبيت السريع

### 1. إعداد الخادم الأساسي

```bash
# تحديث النظام
sudo apt update && sudo apt upgrade -y

# تثبيت الأدوات الأساسية
sudo apt install -y curl wget git unzip software-properties-common

# إضافة مستودعات PHP
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# تثبيت PHP والإضافات
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-redis \
    php8.2-gd php8.2-curl php8.2-mbstring php8.2-xml php8.2-zip \
    php8.2-bcmath php8.2-intl php8.2-tokenizer php8.2-fileinfo \
    php8.2-ctype php8.2-json php8.2-opcache
```

### 2. تثبيت قواعد البيانات والخدمات

```bash
# تثبيت MySQL
sudo apt install -y mysql-server
sudo mysql_secure_installation

# تثبيت Redis
sudo apt install -y redis-server
sudo systemctl enable redis-server

# تثبيت Nginx
sudo apt install -y nginx
sudo systemctl enable nginx

# تثبيت Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# تثبيت Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 3. إعداد قاعدة البيانات

```sql
-- إنشاء قاعدة البيانات والمستخدم
CREATE DATABASE kids_store_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'kids_store_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON kids_store_production.* TO 'kids_store_user'@'localhost';
FLUSH PRIVILEGES;
```

### 4. نشر المشروع

```bash
# إنشاء مجلد المشروع
sudo mkdir -p /var/www/kids-store
sudo chown -R $USER:www-data /var/www/kids-store

# استنساخ المشروع
cd /var/www
git clone https://github.com/your-repo/kids-store.git
cd kids-store

# تثبيت التبعيات
composer install --no-dev --optimize-autoloader
npm ci --production

# إعداد البيئة
cp .env.production.example .env
# قم بتحرير .env وإدخال بياناتك

# إنشاء مفتاح التطبيق
php artisan key:generate --force

# تشغيل الترحيلات
php artisan migrate --force

# بناء الأصول
npm run build

# تحسين التطبيق
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## ⚙️ إعداد الخدمات المتقدمة

### 1. إعداد Nginx

```bash
# نسخ إعدادات Nginx المحسنة
sudo cp config/nginx/kids-store.conf /etc/nginx/sites-available/
sudo ln -s /etc/nginx/sites-available/kids-store.conf /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default

# اختبار الإعدادات
sudo nginx -t
sudo systemctl restart nginx
```

### 2. إعداد PHP للإنتاج

```bash
# نسخ إعدادات PHP المحسنة
sudo cp config/php/production.ini /etc/php/8.2/fpm/conf.d/99-kids-store.ini
sudo systemctl restart php8.2-fpm
```

### 3. إعداد Redis

```bash
# نسخ إعدادات Redis المحسنة
sudo cp config/redis/redis.conf /etc/redis/redis.conf
sudo systemctl restart redis-server
```

### 4. إعداد Queue Workers

```bash
# إعداد خدمة Queue Worker
sudo cp config/systemd/kids-store-worker.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable kids-store-worker
sudo systemctl start kids-store-worker
```

### 5. إعداد المهام المجدولة

```bash
# إعداد Cron Jobs
sudo crontab -u www-data config/crontab/kids-store-cron

# إعداد Log Rotation
sudo cp config/logrotate/kids-store /etc/logrotate.d/
```

## 🔒 إعداد الأمان

### 1. إعداد SSL

```bash
# تثبيت Certbot
sudo apt install -y certbot python3-certbot-nginx

# الحصول على شهادة SSL
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# اختبار التجديد التلقائي
sudo certbot renew --dry-run
```

### 2. إعداد Firewall

```bash
# تفعيل UFW
sudo ufw enable
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw allow 3306  # MySQL (إذا كان مطلوب)
sudo ufw allow 6379  # Redis (إذا كان مطلوب)
```

### 3. إعداد Fail2Ban

```bash
# تثبيت وإعداد Fail2Ban
sudo apt install -y fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

## 📊 المراقبة والصيانة

### 1. فحص صحة النظام

```bash
# فحص يدوي
cd /var/www/kids-store
php artisan system:health-check --detailed --notify

# فحص حالة الخدمات
sudo systemctl status nginx php8.2-fpm mysql redis-server kids-store-worker
```

### 2. النسخ الاحتياطي

```bash
# نسخ احتياطي شامل
php artisan backup:full --compress --encrypt --upload --verify --notify

# نسخ احتياطي لقاعدة البيانات فقط
php artisan backup:database --compress --upload --notify

# نسخ احتياطي للملفات فقط
php artisan backup:files --upload --verify
```

### 3. الصيانة الأسبوعية

```bash
# تشغيل الصيانة الأسبوعية
php artisan maintenance:weekly --force
```

## 🚀 النشر والتحديث

### استخدام سكريبت النشر التلقائي

```bash
# جعل السكريبت قابل للتنفيذ
chmod +x scripts/deploy-production.sh

# تشغيل النشر
./scripts/deploy-production.sh

# في حالة الحاجة للتراجع
./scripts/deploy-production.sh rollback
```

## 🔍 استكشاف الأخطاء

### مشاكل شائعة وحلولها

#### 1. خطأ 500 - Internal Server Error
```bash
# فحص سجلات الأخطاء
tail -f /var/log/nginx/kids-store-error.log
tail -f /var/www/kids-store/storage/logs/laravel.log

# فحص صلاحيات الملفات
sudo chown -R www-data:www-data /var/www/kids-store
sudo chmod -R 755 /var/www/kids-store
sudo chmod -R 775 /var/www/kids-store/storage
sudo chmod -R 775 /var/www/kids-store/bootstrap/cache
```

#### 2. مشاكل قاعدة البيانات
```bash
# اختبار الاتصال
mysql -u kids_store_user -p kids_store_production

# فحص حالة MySQL
sudo systemctl status mysql
```

#### 3. مشاكل Redis
```bash
# اختبار Redis
redis-cli ping

# فحص حالة Redis
sudo systemctl status redis-server
```

### أوامر مفيدة للصيانة

```bash
# إعادة تشغيل جميع الخدمات
sudo systemctl restart nginx php8.2-fpm mysql redis-server kids-store-worker

# مسح جميع أنواع الكاش
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# إعادة تحسين التطبيق
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# فحص حالة Queue
php artisan queue:work --once
php artisan queue:restart
```

## 📈 تحسين الأداء

### 1. تحسين قاعدة البيانات
```sql
-- إضافة فهارس مهمة
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_featured ON products(featured);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_created ON orders(created_at);
```

### 2. تحسين Nginx
```bash
# تفعيل ضغط Gzip
# (مُطبق في ملف الإعدادات)

# تحسين أحجام المخازن المؤقتة
# (مُطبق في ملف الإعدادات)
```

### 3. تحسين PHP
```bash
# تفعيل OPcache
# (مُطبق في ملف الإعدادات)

# تحسين إعدادات الذاكرة
# (مُطبق في ملف الإعدادات)
```

## 📞 الدعم والمساعدة

### معلومات الاتصال
- **المطور**: حذيفة الحذيفي
- **البريد الإلكتروني**: support@yourdomain.com
- **الهاتف**: +966-XX-XXX-XXXX

### الموارد المفيدة
- [توثيق Laravel](https://laravel.com/docs)
- [دليل أمان Laravel](https://laravel.com/docs/security)
- [أفضل ممارسات النشر](https://laravel.com/docs/deployment)

---

**ملاحظة**: تأكد من تحديث جميع كلمات المرور والمفاتيح السرية قبل النشر في البيئة الإنتاجية.
