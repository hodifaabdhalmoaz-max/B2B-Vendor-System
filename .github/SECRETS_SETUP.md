# 🔐 إعداد GitHub Secrets

## نظرة عامة

هذا الملف يحتوي على قائمة بجميع الـ Secrets المطلوبة لتشغيل CI/CD pipeline بنجاح.

## 🚨 تحذير مهم

**لا تضع أي قيم حقيقية في هذا الملف!** هذا الملف للتوثيق فقط.

## 📋 Secrets المطلوبة

### 🔑 إعدادات التطبيق الأساسية

```
APP_KEY
```
- **الوصف**: مفتاح تشفير Laravel
- **كيفية الإنشاء**: `php artisan key:generate --show`
- **مثال**: `base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX=`

```
APP_URL
```
- **الوصف**: رابط الموقع في الإنتاج
- **مثال**: `https://kids-store.com`

### 🗄️ إعدادات قاعدة البيانات

```
DB_HOST
```
- **الوصف**: عنوان خادم قاعدة البيانات
- **مثال**: `localhost` أو `127.0.0.1`

```
DB_DATABASE
```
- **الوصف**: اسم قاعدة البيانات
- **مثال**: `kids_store_production`

```
DB_USERNAME
```
- **الوصف**: اسم مستخدم قاعدة البيانات
- **مثال**: `kids_store_user`

```
DB_PASSWORD
```
- **الوصف**: كلمة مرور قاعدة البيانات
- **متطلبات**: يجب أن تكون قوية (12+ حرف، أرقام، رموز)
- **مثال**: `StrongPassword123!@#`

### 📧 إعدادات البريد الإلكتروني

```
MAIL_HOST
```
- **الوصف**: خادم SMTP
- **مثال**: `smtp.gmail.com`

```
MAIL_USERNAME
```
- **الوصف**: اسم مستخدم البريد
- **مثال**: `noreply@kids-store.com`

```
MAIL_PASSWORD
```
- **الوصف**: كلمة مرور البريد أو App Password
- **ملاحظة**: استخدم App Password للـ Gmail

### 🔄 إعدادات Redis

```
REDIS_HOST
```
- **الوصف**: عنوان خادم Redis
- **مثال**: `127.0.0.1`

```
REDIS_PASSWORD
```
- **الوصف**: كلمة مرور Redis (اختياري)
- **مثال**: `RedisPassword123!`

### 🚀 إعدادات النشر

```
HOST
```
- **الوصف**: عنوان IP أو domain للخادم
- **مثال**: `192.168.1.100` أو `server.kids-store.com`

```
USERNAME
```
- **الوصف**: اسم مستخدم SSH
- **مثال**: `deploy`

```
PRIVATE_KEY
```
- **الوصف**: SSH Private Key للنشر
- **كيفية الإنشاء**: 
  ```bash
  ssh-keygen -t rsa -b 4096 -C "deploy@kids-store.com"
  cat ~/.ssh/id_rsa
  ```

### 🔒 إعدادات الأمان

```
BACKUP_ENCRYPTION_PASSWORD
```
- **الوصف**: كلمة مرور تشفير النسخ الاحتياطية
- **متطلبات**: قوية جداً (16+ حرف)
- **مثال**: `BackupEncryption2024!@#$`

```
TWO_FACTOR_SECRET_KEY
```
- **الوصف**: مفتاح سري للمصادقة الثنائية
- **كيفية الإنشاء**: يتم إنشاؤه تلقائياً في التطبيق

### ☁️ إعدادات AWS (اختياري)

```
AWS_ACCESS_KEY_ID
```
- **الوصف**: مفتاح الوصول لـ AWS
- **مثال**: `AKIAIOSFODNN7EXAMPLE`

```
AWS_SECRET_ACCESS_KEY
```
- **الوصف**: المفتاح السري لـ AWS
- **مثال**: `wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY`

```
AWS_BUCKET
```
- **الوصف**: اسم S3 bucket للنسخ الاحتياطية
- **مثال**: `kids-store-backups`

### 📊 إعدادات التحليلات (اختياري)

```
GOOGLE_ANALYTICS_ID
```
- **الوصف**: معرف Google Analytics
- **مثال**: `G-XXXXXXXXXX`

```
GOOGLE_TAG_MANAGER_ID
```
- **الوصف**: معرف Google Tag Manager
- **مثال**: `GTM-XXXXXXX`

## 🛠️ كيفية إضافة Secrets في GitHub

### 1. الذهاب إلى إعدادات المستودع
```
GitHub Repository → Settings → Secrets and variables → Actions
```

### 2. إضافة Secret جديد
```
1. انقر على "New repository secret"
2. أدخل اسم الـ Secret (مثل: APP_KEY)
3. أدخل القيمة
4. انقر على "Add secret"
```

### 3. إضافة Environment Secrets
```
1. اذهب إلى "Environments"
2. أنشئ environment جديد (مثل: production)
3. أضف الـ secrets الخاصة بهذه البيئة
```

## 🔄 Environment-Specific Secrets

### Production Environment
- جميع الـ secrets أعلاه مطلوبة
- `FORCE_HTTPS=true`
- `APP_DEBUG=false`
- `TWO_FACTOR_ENABLED=true`

### Staging Environment
- نفس secrets الإنتاج مع قيم مختلفة
- `APP_DEBUG=true` (للاختبار)
- قاعدة بيانات منفصلة

## 🧪 اختبار الـ Secrets

### تشغيل اختبار محلي:
```bash
# تحقق من وجود جميع المتغيرات
php artisan config:show

# اختبار الاتصال بقاعدة البيانات
php artisan tinker --execute="DB::connection()->getPdo();"

# اختبار البريد الإلكتروني
php artisan tinker --execute="Mail::raw('Test', function(\$m) { \$m->to('test@example.com')->subject('Test'); });"
```

## 🔐 أفضل الممارسات

### 1. تدوير الـ Secrets
- غيّر كلمات المرور كل 90 يوم
- استخدم مولدات كلمات مرور قوية
- احتفظ بنسخة احتياطية آمنة

### 2. الحد الأدنى من الصلاحيات
- أعط كل secret أقل صلاحيات ممكنة
- استخدم حسابات منفصلة للنشر
- فعّل 2FA على جميع الحسابات

### 3. المراقبة
- راقب استخدام الـ secrets
- فعّل تنبيهات للوصول غير المعتاد
- سجّل جميع العمليات الحساسة

## 📞 في حالة تسريب Secret

### 1. إجراءات فورية
```bash
# 1. غيّر الـ secret فوراً في GitHub
# 2. غيّر كلمة المرور في الخدمة المتأثرة
# 3. راجع سجلات الوصول
# 4. أبلغ الفريق
```

### 2. إجراءات المتابعة
- راجع جميع الـ secrets الأخرى
- حدّث إجراءات الأمان
- وثّق الحادثة للمراجعة

## 📚 مراجع مفيدة

- [GitHub Secrets Documentation](https://docs.github.com/en/actions/security-guides/encrypted-secrets)
- [Laravel Environment Configuration](https://laravel.com/docs/configuration)
- [SSH Key Generation Guide](https://docs.github.com/en/authentication/connecting-to-github-with-ssh)

---

**ملاحظة**: تأكد من عدم مشاركة هذا الملف مع قيم حقيقية للـ secrets!
