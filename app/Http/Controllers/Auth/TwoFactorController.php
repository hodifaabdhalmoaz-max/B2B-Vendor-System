<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\TwoFactorCode;

class TwoFactorController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the two factor authentication form.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('auth.twoFactor');
    }

    /**
     * Generate and send two factor authentication code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Generate random code
        $code = rand(100000, 999999);
        
        // Update user's two factor code
        $user->two_factor_code = $code;
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();
        
        // Send code via email
        Mail::to($user->email)->send(new TwoFactorCode($code));
        
        // Log activity
        UserActivity::log($user->id, 'two_factor_code_generated');
        
        return redirect()->route('two-factor.index')
            ->with('message', __('messages.two_factor_code_sent'));
    }

    /**
     * Verify the two factor authentication code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric',
        ]);
        
        $user = Auth::user();
        
        if ($request->code == $user->two_factor_code && 
            $user->two_factor_expires_at > now()) {
            
            // Reset the code
            $user->two_factor_code = null;
            $user->two_factor_expires_at = null;
            $user->two_factor_verified_at = now();
            $user->save();
            
            // Log activity
            UserActivity::log($user->id, 'two_factor_verified');
            
            return redirect()->intended(route('user.dashboard'))
                ->with('success', __('messages.two_factor_verified'));
        }
        
        return redirect()->back()
            ->withErrors(['code' => __('messages.invalid_two_factor_code')]);
    }

    /**
     * Show the two factor settings form.
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        $user = Auth::user();
        
        return view('user.two-factor-settings', compact('user'));
    }

    /**
     * Enable two factor authentication.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enable(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'password' => 'required',
        ]);
        
        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return redirect()->back()
                ->withErrors(['password' => __('messages.current_password_incorrect')]);
        }
        
        // Enable two factor authentication
        $user->two_factor_enabled = true;
        $user->save();
        
        // Log activity
        UserActivity::log($user->id, 'two_factor_enabled');
        
        return redirect()->route('user.two-factor.settings')
            ->with('success', __('messages.two_factor_enabled'));
    }

    /**
     * Disable two factor authentication.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disable(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'password' => 'required',
        ]);
        
        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return redirect()->back()
                ->withErrors(['password' => __('messages.current_password_incorrect')]);
        }
        
        // Disable two factor authentication
        $user->two_factor_enabled = false;
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();
        
        // Log activity
        UserActivity::log($user->id, 'two_factor_disabled');
        
        return redirect()->route('user.two-factor.settings')
            ->with('success', __('messages.two_factor_disabled'));
    }
}
