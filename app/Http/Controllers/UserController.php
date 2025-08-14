<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{   

    public function loginPage(){
        return view("login");
    }
    public function registerPage(){
        return view("register");
    }
    public function register(Request $request)
    {
        $userData = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
        ]);

        User::create($userData);

        return redirect()->route('user.login')->with('success', 'User registered successfully!');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if(Auth::attempt($credentials)){
            $request->session()->regenerate();
            $user = Auth::user();
            if($user->role == 'admin'){
                return redirect()->route('admin.dashboard');
            }else{
                return redirect()->route('user.dashboard');
            }
            // session(['user_id' => $user->id, 'user_name' => $user->name]);
        }else{
            return back()->with('error', 'Invalid credentials');
        }
    }

    public function logout()
    {
        session()->forget(['user_id', 'user_name']);
        return redirect()->route('user.login');
    }
}
