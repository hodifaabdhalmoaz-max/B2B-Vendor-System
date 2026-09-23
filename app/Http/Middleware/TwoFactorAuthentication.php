<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // تحقق من وجود المستخدم وكونه مشرف
        if (!$user || !$user->isAdmin()) {
            return $next($request);
        }

        // تحقق من تفعيل المصادقة الثنائية في الإعدادات
        if (!config('auth.two_factor.enabled', false)) {
            return $next($request);
        }

        // تحقق من وجود المصادقة الثنائية للمستخدم
        if (!$user->two_factor_secret) {
            // إعادة توجيه لإعداد المصادقة الثنائية
            if (!$request->routeIs('admin.2fa.setup')) {
                return redirect()->route('admin.2fa.setup')
                    ->with('warning', 'يجب إعداد المصادقة الثنائية للوصول إلى لوحة التحكم');
            }
            return $next($request);
        }

        // تحقق من التحقق من المصادقة الثنائية في الجلسة الحالية
        if (!Session::get('2fa_verified', false)) {
            // استثناء صفحات التحقق من المصادقة الثنائية
            if (!$request->routeIs(['admin.2fa.verify', 'admin.2fa.challenge'])) {
                return redirect()->route('admin.2fa.verify')
                    ->with('info', 'يرجى إدخال رمز المصادقة الثنائية');
            }
        }

        return $next($request);
    }
}
