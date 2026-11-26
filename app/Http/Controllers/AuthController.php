<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Tampilkan form login (HANYA untuk pengelola)
    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->homeRoute());
        }

        // ?sebagai=kasir dikirim dari landing page "Masuk sebagai Kasir"
        $sebagai = $request->query('sebagai') === User::ROLE_KASIR ? User::ROLE_KASIR : null;

        return view('auth.login', compact('sebagai'));
    }

    // Proses login (HANYA untuk pengelola)
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password wajib diisi'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        // Pintu masuk kasir: hanya akun dengan role kasir yang boleh lewat.
        $sebagai = $request->input('sebagai') === User::ROLE_KASIR ? User::ROLE_KASIR : null;
        if ($sebagai) {
            $credentials['role'] = User::ROLE_KASIR;
        }

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            return redirect()->route(auth()->user()->homeRoute())
                ->with('success', 'Login berhasil! Selamat datang ' . auth()->user()->name);
        }

        return back()->withErrors([
            'email' => $sebagai
                ? 'Email atau password salah, atau akun ini bukan akun kasir.'
                : 'Email atau password salah.',
        ])->withInput($request->except('password'));
    }

    // Tampilkan form register (HANYA untuk pengelola baru)
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->homeRoute());
        }
        return view('auth.register');
    }

    // Proses register (HANYA untuk pengelola baru) - TANPA usertype
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|min:3',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|confirmed|min:3',
        ], [
            'name.required' => 'Nama lengkap wajib diisi',
            'name.min' => 'Nama minimal 3 karakter',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 3 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            // Daftar mandiri = kasir. Role pengelola/kurir hanya bisa diberikan
            // oleh pengelola lewat menu Kelola Pengguna.
            'role' => User::ROLE_KASIR,
        ]);

        Auth::login($user);
        
        return redirect()->route($user->homeRoute())
            ->with('success', 'Registrasi berhasil! Selamat datang ' . $user->name);
    }

    // Logout (untuk pengelola)
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('welcome')
            ->with('status', 'Anda telah berhasil logout.');
    }

    // Halaman portal pengelola
    public function welcome()
    {
        return view('welcome');
    }
}