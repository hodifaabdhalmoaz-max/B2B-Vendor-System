<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AuditService;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->utype === 'ADM') {
            // Log admin access
            $this->auditService->log('admin_access', [
                'user_id' => Auth::id(),
                'route' => $request->route()->getName(),
                'method' => $request->method(),
                'ip' => $request->ip(),
            ]);

            return $next($request);
        }

        // Log unauthorized admin access attempt
        if (Auth::check()) {
            $this->auditService->log('unauthorized_admin_access', [
                'user_id' => Auth::id(),
                'route' => $request->route()->getName(),
                'method' => $request->method(),
                'ip' => $request->ip(),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        return redirect()->route('login')->with('error', __('messages.admin_access_denied'));
    }
}
