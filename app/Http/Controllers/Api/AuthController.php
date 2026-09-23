<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Register a new user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'mobile' => 'required|string|max:20|unique:users',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        /** @var \App\Models\User $user */
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'mobile' => $request->mobile,
        ]);

        // Log user registration
        $this->auditService->log('user_registered', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
            'message' => 'User registered successfully',
        ]);
    }

    /**
     * Login user and create token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'nullable|string',
            'email' => 'nullable|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $identifier = $request->input('login', $request->input('email'));

        if (! filled($identifier)) {
            return response()->json([
                'success' => false,
                'errors' => ['login' => ['The login field is required.']],
            ], 422);
        }

        $normalizedIdentifier = Str::lower(trim((string) $identifier));

        /** @var \App\Models\User|null $user */
        $user = User::query()
            ->where('username', $normalizedIdentifier)
            ->orWhere('email', $normalizedIdentifier)
            ->orWhere('mobile', $normalizedIdentifier)
            ->first();

        if (! $user || ! $user->is_active || ! Hash::check($request->password, $user->password)) {
            $this->auditService->log('login_failed', [
                'identifier' => $normalizedIdentifier,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid login credentials',
            ], 401);
        }

        if ($user->isReseller()) {
            $user->loadMissing('resellerProfile');

            if (! $user->resellerProfile || ! $user->resellerProfile->isActive()) {
                $this->auditService->log('login_failed', [
                    'identifier' => $normalizedIdentifier,
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid login credentials',
                ], 401);
            }

            if ($user->force_password_change) {
                return response()->json([
                    'success' => false,
                    'error' => 'password_change_required',
                    'message' => 'Password change required',
                ], 423);
            }
        }

        // Revoke previous tokens
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        // Log successful login
        $this->auditService->log('login_success', [
            'user_id' => $user->id,
            'email' => $user->email,
            'username' => $user->username,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
            'message' => 'Login successful',
        ]);
    }

    /**
     * Logout user (revoke token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        // Log logout
        $this->auditService->log('logout', [
            'user_id' => $user?->id,
            'ip' => $request->ip(),
        ]);

        if ($user) {
            $user->tokens()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user(),
            ],
        ]);
    }

    /**
     * Refresh token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user->is_active) {
            $user->tokens()->delete();

            return response()->json([
                'success' => false,
                'message' => 'Invalid login credentials',
            ], 401);
        }

        // Revoke previous tokens
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
            ],
            'message' => 'Token refreshed successfully',
        ]);
    }
}
