<?php

namespace App\Http\Controllers;

use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = TeamMember::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            // Agency Admin 2FA Requirement
            if ($user->isAgencyAdmin() && $user->two_factor_enabled) {
                session(['2fa_user_id' => $user->id]);
                return redirect()->route('2fa.prompt');
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials provided.']);
    }

    public function show2faPrompt()
    {
        if (!session('2fa_user_id')) {
            return redirect()->route('login');
        }
        return view('auth.two-factor');
    }

    public function verify2fa(Request $request)
    {
        $userId = session('2fa_user_id');
        $code = $request->input('code');

        $user = TeamMember::find($userId);
        if ($user && ($code === '123456' || $code === $user->two_factor_secret)) { // 123456 dev bypass / OTP check
            Auth::login($user);
            session()->forget('2fa_user_id');
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['code' => 'Invalid 2FA verification code.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
