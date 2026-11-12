<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Auth::guard('admin')->check() ? redirect()->route('admin.dashboard') : view('admin.login');
    }

    /** Throttled by the 'admin-login' rate limiter (5 attempts per minute per IP + email). */
    public function login(Request $request)
    {
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required|string']);

        if (! Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'These details do not match our records.'])->onlyInput('email');
        }
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
