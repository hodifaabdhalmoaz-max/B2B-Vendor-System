<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // إذا كان المستخدم مدير، توجيه إلى لوحة التحكم
                if ($user->isAdmin()) {
                    return redirect('/admin');
                }

                if ($user->isReseller() && $user->loadMissing('resellerProfile')->hasActiveResellerProfile()) {
                    return redirect()->route('reseller.index');
                }

                // وإلا توجيه إلى الصفحة الرئيسية
                return redirect('/');
            }
        }

        return $next($request);
    }
}
