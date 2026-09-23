<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureReseller
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => __('Unauthenticated.')], 401)
                : redirect()->guest(route('login'));
        }

        $user->loadMissing('resellerProfile');

        if (
            ! $user->is_active ||
            ! $user->isReseller() ||
            ! $user->resellerProfile ||
            ! $user->resellerProfile->isActive()
        ) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('Forbidden.')], 403);
            }

            abort(403);
        }

        return $next($request);
    }
}
