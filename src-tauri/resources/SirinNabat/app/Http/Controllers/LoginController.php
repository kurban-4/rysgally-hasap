<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }
public function login(Request $request): RedirectResponse
{
    $credentials = $request->validate([
        'username' => ['required'],
        'password' => ['required'],
    ]);

    // Ищем по 'name', так как в SQLite мы создали Admin
    if (Auth::attempt(['name' => $request->username, 'password' => $request->password])) {
        $request->session()->regenerate();
        
        // Никаких условий, только в админку
return redirect()->route('welcome');
    }

    return back()->withErrors(['username' => 'Giriş ýalňyş.'])->onlyInput('username');
}
    protected function authenticated(Request $request, $user)
    {
        if ($user->role === 'admin' || $user->role === 'staff') {
            return redirect()->route('welcome');
        }

        return redirect()->route('home');
    }
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
