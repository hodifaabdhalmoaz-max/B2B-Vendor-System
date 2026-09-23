<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use App\Services\TwoFactorAuthService;

class SecurityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // تسجيل خدمة المصادقة الثنائية
        $this->app->singleton(TwoFactorAuthService::class, function ($app) {
            return new TwoFactorAuthService();
        });

        // تسجيل إعدادات الأمان
        $this->mergeConfigFrom(
            __DIR__.'/../../config/security.php', 'security'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // فرض HTTPS في بيئة الإنتاج
        if (config('security.headers.force_https') && $this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // إضافة قواعد التحقق المخصصة
        $this->registerCustomValidationRules();

        // إضافة Blade Directives أمنية
        $this->registerBladeDirectives();

        // إضافة View Composers
        $this->registerViewComposers();

        // تسجيل الأوامر
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\SecurityAudit::class,
            ]);
        }
    }

    /**
     * تسجيل قواعد التحقق المخصصة
     */
    protected function registerCustomValidationRules(): void
    {
        // قاعدة للتحقق من قوة كلمة المرور
        Validator::extend('strong_password', function ($attribute, $value, $parameters, $validator) {
            $config = config('security.password');
            
            // الحد الأدنى للطول
            if (strlen($value) < $config['min_length']) {
                return false;
            }

            // التحقق من وجود أحرف كبيرة
            if ($config['require_uppercase'] && !preg_match('/[A-Z]/', $value)) {
                return false;
            }

            // التحقق من وجود أحرف صغيرة
            if ($config['require_lowercase'] && !preg_match('/[a-z]/', $value)) {
                return false;
            }

            // التحقق من وجود أرقام
            if ($config['require_numbers'] && !preg_match('/[0-9]/', $value)) {
                return false;
            }

            // التحقق من وجود رموز
            if ($config['require_symbols'] && !preg_match('/[^A-Za-z0-9]/', $value)) {
                return false;
            }

            return true;
        });

        // قاعدة للتحقق من عدم وجود محتوى ضار
        Validator::extend('safe_content', function ($attribute, $value, $parameters, $validator) {
            $suspiciousPatterns = [
                '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
                '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi',
                '/javascript:/i',
                '/vbscript:/i',
                '/onload=/i',
                '/onerror=/i',
                '/onclick=/i',
            ];

            foreach ($suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    return false;
                }
            }

            return true;
        });

        // رسائل التحقق المخصصة
        Validator::replacer('strong_password', function ($message, $attribute, $rule, $parameters) {
            return 'كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل، وتشمل أحرف كبيرة وصغيرة وأرقام ورموز.';
        });

        Validator::replacer('safe_content', function ($message, $attribute, $rule, $parameters) {
            return 'المحتوى يحتوي على عناصر غير آمنة.';
        });
    }

    /**
     * تسجيل Blade Directives أمنية
     */
    protected function registerBladeDirectives(): void
    {
        // Directive للتحقق من المصادقة الثنائية
        Blade::if('twofactor', function () {
            return auth()->check() && auth()->user()->hasTwoFactorEnabled();
        });

        // Directive للتحقق من كون المستخدم مشرف
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->isAdmin();
        });

        // Directive لتنظيف المحتوى من XSS
        Blade::directive('clean', function ($expression) {
            return "<?php echo e(strip_tags({$expression})); ?>";
        });

        // Directive لعرض CSRF token
        Blade::directive('csrfToken', function () {
            return "<?php echo csrf_token(); ?>";
        });

        // Directive للتحقق من الأذونات
        Blade::directive('canAccess', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->can({$expression})): ?>";
        });

        Blade::directive('endcanAccess', function () {
            return "<?php endif; ?>";
        });
    }

    /**
     * تسجيل View Composers
     */
    protected function registerViewComposers(): void
    {
        // إضافة متغيرات أمنية للـ Views (فقط ما لا يمكن الوصول إليه مباشرة)
        View::composer('*', function ($view) {
            $view->with('isSecureConnection', request()->isSecure());
        });

        // إضافة معلومات المصادقة الثنائية للوحة التحكم
        View::composer('admin.*', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                $view->with([
                    'hasTwoFactor' => $user->hasTwoFactorEnabled(),
                    'isAccountLocked' => $user->isLocked(),
                    'lastLogin' => $user->last_login_at,
                    'failedAttempts' => $user->failed_login_attempts,
                ]);
            }
        });
    }
}
