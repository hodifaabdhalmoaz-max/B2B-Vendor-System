<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SecurityAudit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:audit {--fix : Attempt to fix issues automatically}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform a comprehensive security audit of the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔒 بدء فحص الأمان الشامل...');
        $this->newLine();

        $issues = [];
        $fixes = [];

        // فحص إعدادات البيئة
        $envIssues = $this->checkEnvironmentSettings();
        $issues = array_merge($issues, $envIssues);

        // فحص أذونات الملفات
        $fileIssues = $this->checkFilePermissions();
        $issues = array_merge($issues, $fileIssues);

        // فحص قاعدة البيانات
        $dbIssues = $this->checkDatabaseSecurity();
        $issues = array_merge($issues, $dbIssues);

        // فحص الجلسات
        $sessionIssues = $this->checkSessionSecurity();
        $issues = array_merge($issues, $sessionIssues);

        // فحص التشفير
        $encryptionIssues = $this->checkEncryption();
        $issues = array_merge($issues, $encryptionIssues);

        // فحص الـ Headers الأمنية
        $headerIssues = $this->checkSecurityHeaders();
        $issues = array_merge($issues, $headerIssues);

        // عرض النتائج
        $this->displayResults($issues);

        // تطبيق الإصلاحات إذا طُلب ذلك
        if ($this->option('fix') && !empty($fixes)) {
            $this->applyFixes($fixes);
        }

        return Command::SUCCESS;
    }

    /**
     * فحص إعدادات البيئة
     */
    protected function checkEnvironmentSettings(): array
    {
        $issues = [];

        // فحص APP_DEBUG
        if (config('app.debug') && app()->environment('production')) {
            $issues[] = [
                'type' => 'critical',
                'message' => 'APP_DEBUG مفعل في بيئة الإنتاج',
                'fix' => 'تعيين APP_DEBUG=false في ملف .env'
            ];
        }

        // فحص APP_KEY
        if (empty(config('app.key'))) {
            $issues[] = [
                'type' => 'critical',
                'message' => 'APP_KEY غير محدد',
                'fix' => 'تشغيل php artisan key:generate'
            ];
        }

        // فحص كلمة مرور قاعدة البيانات
        if (empty(config('database.connections.mysql.password'))) {
            $issues[] = [
                'type' => 'high',
                'message' => 'كلمة مرور قاعدة البيانات فارغة',
                'fix' => 'تعيين كلمة مرور قوية لقاعدة البيانات'
            ];
        }

        // فحص HTTPS
        if (!config('security.headers.force_https') && app()->environment('production')) {
            $issues[] = [
                'type' => 'medium',
                'message' => 'HTTPS غير مفروض في بيئة الإنتاج',
                'fix' => 'تعيين FORCE_HTTPS=true'
            ];
        }

        return $issues;
    }

    /**
     * فحص أذونات الملفات
     */
    protected function checkFilePermissions(): array
    {
        $issues = [];

        $sensitiveFiles = [
            '.env',
            'config/',
            'database/',
            'storage/',
        ];

        foreach ($sensitiveFiles as $file) {
            $path = base_path($file);
            if (File::exists($path)) {
                $permissions = substr(sprintf('%o', fileperms($path)), -4);
                
                if ($file === '.env' && $permissions !== '0600') {
                    $issues[] = [
                        'type' => 'high',
                        'message' => "أذونات ملف .env غير آمنة: {$permissions}",
                        'fix' => 'chmod 600 .env'
                    ];
                }
            }
        }

        return $issues;
    }

    /**
     * فحص أمان قاعدة البيانات
     */
    protected function checkDatabaseSecurity(): array
    {
        $issues = [];

        try {
            // فحص المستخدمين بدون كلمة مرور
            $usersWithoutPassword = DB::table('users')
                ->whereNull('password')
                ->orWhere('password', '')
                ->count();

            if ($usersWithoutPassword > 0) {
                $issues[] = [
                    'type' => 'high',
                    'message' => "يوجد {$usersWithoutPassword} مستخدم بدون كلمة مرور",
                    'fix' => 'إجبار المستخدمين على تعيين كلمات مرور'
                ];
            }

            // فحص المشرفين
            $adminCount = DB::table('users')
                ->where('utype', 'ADM')
                ->count();

            if ($adminCount === 0) {
                $issues[] = [
                    'type' => 'medium',
                    'message' => 'لا يوجد مشرفين في النظام',
                    'fix' => 'إنشاء حساب مشرف'
                ];
            }

        } catch (\Exception $e) {
            $issues[] = [
                'type' => 'low',
                'message' => 'تعذر فحص قاعدة البيانات: ' . $e->getMessage(),
                'fix' => 'التحقق من اتصال قاعدة البيانات'
            ];
        }

        return $issues;
    }

    /**
     * فحص أمان الجلسات
     */
    protected function checkSessionSecurity(): array
    {
        $issues = [];

        if (!config('session.encrypt')) {
            $issues[] = [
                'type' => 'medium',
                'message' => 'تشفير الجلسات غير مفعل',
                'fix' => 'تعيين SESSION_ENCRYPT=true'
            ];
        }

        if (!config('session.http_only')) {
            $issues[] = [
                'type' => 'medium',
                'message' => 'HttpOnly للجلسات غير مفعل',
                'fix' => 'تعيين SESSION_HTTP_ONLY=true'
            ];
        }

        return $issues;
    }

    /**
     * فحص التشفير
     */
    protected function checkEncryption(): array
    {
        $issues = [];

        if (config('app.cipher') !== 'AES-256-CBC') {
            $issues[] = [
                'type' => 'medium',
                'message' => 'خوارزمية التشفير ليست AES-256-CBC',
                'fix' => 'استخدام AES-256-CBC للتشفير'
            ];
        }

        return $issues;
    }

    /**
     * فحص الـ Headers الأمنية
     */
    protected function checkSecurityHeaders(): array
    {
        $issues = [];

        if (!config('security.headers.enabled')) {
            $issues[] = [
                'type' => 'high',
                'message' => 'Security Headers غير مفعلة',
                'fix' => 'تعيين SECURITY_HEADERS_ENABLED=true'
            ];
        }

        return $issues;
    }

    /**
     * عرض النتائج
     */
    protected function displayResults(array $issues): void
    {
        if (empty($issues)) {
            $this->info('✅ لم يتم العثور على مشاكل أمنية!');
            return;
        }

        $this->error("🚨 تم العثور على " . count($issues) . " مشكلة أمنية:");
        $this->newLine();

        $critical = array_filter($issues, fn($issue) => $issue['type'] === 'critical');
        $high = array_filter($issues, fn($issue) => $issue['type'] === 'high');
        $medium = array_filter($issues, fn($issue) => $issue['type'] === 'medium');
        $low = array_filter($issues, fn($issue) => $issue['type'] === 'low');

        if (!empty($critical)) {
            $this->error('🔴 مشاكل حرجة:');
            foreach ($critical as $issue) {
                $this->line("  - {$issue['message']}");
                $this->line("    الحل: {$issue['fix']}");
            }
            $this->newLine();
        }

        if (!empty($high)) {
            $this->warn('🟠 مشاكل عالية الخطورة:');
            foreach ($high as $issue) {
                $this->line("  - {$issue['message']}");
                $this->line("    الحل: {$issue['fix']}");
            }
            $this->newLine();
        }

        if (!empty($medium)) {
            $this->info('🟡 مشاكل متوسطة الخطورة:');
            foreach ($medium as $issue) {
                $this->line("  - {$issue['message']}");
                $this->line("    الحل: {$issue['fix']}");
            }
            $this->newLine();
        }

        if (!empty($low)) {
            $this->comment('🔵 مشاكل منخفضة الخطورة:');
            foreach ($low as $issue) {
                $this->line("  - {$issue['message']}");
                $this->line("    الحل: {$issue['fix']}");
            }
        }
    }

    /**
     * تطبيق الإصلاحات
     */
    protected function applyFixes(array $fixes): void
    {
        $this->info('🔧 تطبيق الإصلاحات...');
        
        foreach ($fixes as $fix) {
            $this->line("تطبيق: {$fix}");
            // تطبيق الإصلاحات هنا
        }
    }
}
