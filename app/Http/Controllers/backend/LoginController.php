<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index(){
        $sliders = [];
        return view('backend.auth.login', compact('sliders'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::with('role')
            ->where('email', $request->email)
            ->first();

        if (!$user || !password_verify($request->password, $user->password)) {
            return back()->with([
                'error' => 'The provided credentials do not match our records.'
            ])->onlyInput('email');
        }

        // Role deleted or missing
        if (!$user->role) {
            return back()->with([
                'error' => 'Your role has been removed. Please contact the administrator.'
            ])->onlyInput('email');
        }

        if($user->id != 1){
            if ($user->role->status != 1) {
                return back()->with([
                    'error' => 'Your role is inactive. Please contact the administrator.'
                ])->onlyInput('email');
            }
        }

        Auth::login($user);

        $request->session()->regenerate();

        session(['locked' => false]);
        session()->flash('success', 'Login Successfully!');

        return redirect()->intended('admin/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
