<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    public function username(): string
    {
        return 'login';
    }

    protected function validateLogin(Request $request): void
    {
        $request->validate([
            $this->username() => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);
    }

    protected function attemptLogin(Request $request): bool
    {
        $user = $this->findUserByLogin((string) $request->input($this->username()));

        if (! $user || $user->isLocked()) {
            return false;
        }

        if (! Hash::check($request->input('password'), $user->password)) {
            $user->recordFailedLogin();

            return false;
        }

        if (! $user->is_active) {
            return false;
        }

        if ($user->isReseller() && ! $user->loadMissing('resellerProfile')->hasActiveResellerProfile()) {
            return false;
        }

        $this->guard()->login($user, $request->boolean('remember'));

        $user->recordSuccessfulLogin($request->ip());

        return true;
    }

    protected function findUserByLogin(string $identifier): ?User
    {
        $identifier = Str::lower(trim($identifier));

        return User::query()
            ->whereLoginIdentifier($identifier)
            ->first();
    }

    protected function sendFailedLoginResponse(Request $request): never
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->isAdmin()) {
            return redirect('/admin');
        }

        if ($user->isReseller()) {
            return redirect()->route('reseller.index');
        }

        return redirect('/');
    }
}
