<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            // إذا كان المسار يحتوي على admin، توجيه إلى صفحة تسجيل دخول الإدارة
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('login');
            }
            
            // وإلا توجيه إلى صفحة تسجيل دخول العملاء
            return route('login');
        }

        return null;
    }
}
