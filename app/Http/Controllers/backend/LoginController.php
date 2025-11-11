<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index(){
        $sliders = [];
        return view('backend.auth.login', compact('sliders'));
    }

    public function login(Request $request){
       $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);


        if (Auth::attempt($credentials)) {
            session(['locked' => false]);
            $request->session()->regenerate();
            session()->flash('success','Login Successfully!!!');
            return redirect()->intended('admin/dashboard');
        }

        return back()->with(['error'=> 'The provided credentials do not match our records.!'])->onlyInput('email');

    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
