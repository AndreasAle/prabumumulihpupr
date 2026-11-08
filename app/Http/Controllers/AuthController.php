<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;

class AuthController extends Controller
{
    public function login()
    {
        return view('admin.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (Auth::attempt([...$credentials, 'is_active' => true], $request->boolean('remember'))) {
            $request->session()->regenerate();
            $request->user()->update(['last_login_at' => now()]);
            AuditLog::create(['user_id' => $request->user()->id, 'action' => 'LOGIN', 'module' => 'authentication', 'record_id' => $request->user()->id, 'description' => 'Administrator masuk ke CMS.', 'ip_address' => $request->ip()]);

            return redirect()->intended('/admin');
        }

        return back()->withErrors(['email' => 'Email, kata sandi, atau status akun tidak sesuai.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
