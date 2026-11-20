<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate(
            [
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ],
            [
                'email.required' => 'The Email field is required.',
                'email.email' => 'The Email must be a valid email address.',
                'password.required' => 'The Password field is required.',
            ]
        );

        if (!Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            return redirect()->back()->withInput($request->only('email'))->with('error', 'These credentials do not match our records.');
        }

        $user = Auth::user();
        if ($user->is_enabled === 'N' || $user->is_deleted === 'Y') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->with('error', 'Account does not exist or has been deleted.');
        }

        $request->session()->regenerate();

        return redirect('admin/home');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
