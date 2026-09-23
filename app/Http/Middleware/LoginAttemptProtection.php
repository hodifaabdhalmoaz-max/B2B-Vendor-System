<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class LoginAttemptProtection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // تطبيق الحماية فقط على صفحات تسجيل الدخول
        if (!$this->isLoginRoute($request)) {
            return $next($request);
        }

        $ip = $request->ip();
        $email = $request->input('email', '');
        
        // مفاتيح التتبع
        $ipKey = 'login_attempts_ip:' . $ip;
        $emailKey = 'login_attempts_email:' . $email;
        $globalKey = 'login_attempts_global';

        // فحص المحاولات حسب IP
        if ($this->tooManyAttempts($ipKey, 10, 15)) { // 10 محاولات في 15 دقيقة
            return $this->buildLockoutResponse('تم حظر عنوان IP الخاص بك مؤقتاً بسبب كثرة المحاولات الفاشلة');
        }

        // فحص المحاولات حسب البريد الإلكتروني
        if ($email && $this->tooManyAttempts($emailKey, 5, 30)) { // 5 محاولات في 30 دقيقة
            return $this->buildLockoutResponse('تم حظر هذا الحساب مؤقتاً بسبب كثرة المحاولات الفاشلة');
        }

        // فحص المحاولات العامة
        if ($this->tooManyAttempts($globalKey, 100, 5)) { // 100 محاولة في 5 دقائق
            return $this->buildLockoutResponse('النظام مشغول حالياً، يرجى المحاولة لاحقاً');
        }

        $response = $next($request);

        // تسجيل المحاولة الفاشلة إذا كانت استجابة تسجيل الدخول فاشلة
        if ($this->isFailedLoginResponse($response)) {
            $this->recordFailedAttempt($ipKey, 15);
            if ($email) {
                $this->recordFailedAttempt($emailKey, 30);
            }
            $this->recordFailedAttempt($globalKey, 5);
            
            // تسجيل في السجلات
            logger()->warning('محاولة تسجيل دخول فاشلة', [
                'ip' => $ip,
                'email' => $email,
                'user_agent' => $request->userAgent(),
                'timestamp' => now(),
            ]);
        }

        return $response;
    }

    /**
     * تحديد ما إذا كان الطلب متعلق بتسجيل الدخول
     */
    protected function isLoginRoute(Request $request): bool
    {
        return $request->routeIs(['login', 'admin.login']) || 
               $request->is(['login', 'admin/login']) ||
               ($request->isMethod('POST') && str_contains($request->url(), 'login'));
    }

    /**
     * فحص عدد المحاولات
     */
    protected function tooManyAttempts(string $key, int $maxAttempts, int $decayMinutes): bool
    {
        return RateLimiter::tooManyAttempts($key, $maxAttempts);
    }

    /**
     * تسجيل محاولة فاشلة
     */
    protected function recordFailedAttempt(string $key, int $decayMinutes): void
    {
        RateLimiter::hit($key, $decayMinutes * 60);
    }

    /**
     * تحديد ما إذا كانت الاستجابة تشير لفشل تسجيل الدخول
     */
    protected function isFailedLoginResponse(Response $response): bool
    {
        // فحص رمز الاستجابة أو المحتوى
        return $response->getStatusCode() === 422 || 
               $response->getStatusCode() === 401 ||
               (method_exists($response, 'getOriginalContent') && 
                str_contains($response->getOriginalContent(), 'error'));
    }

    /**
     * بناء استجابة الحظر — HTML for web, JSON for API
     */
    protected function buildLockoutResponse(string $message): Response
    {
        $retryAfter = 900; // 15 دقيقة

        // Return JSON for AJAX/API requests
        if (request()->expectsJson() || request()->is('api/*')) {
            return response()->json([
                'message' => $message,
                'error' => 'too_many_attempts',
                'retry_after' => $retryAfter,
            ], 429, [
                'Retry-After' => $retryAfter,
            ]);
        }

        // Return the Blade 429 error page for web requests
        return response()->view('errors.429', [
            'retryAfter' => $retryAfter,
        ], 429, [
            'Retry-After' => $retryAfter,
        ]);
    }
}
