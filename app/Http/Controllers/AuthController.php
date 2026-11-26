<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Tampilkan form login (HANYA untuk pengelola)
    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->homeRoute());
        }

        // ?sebagai=kurir dikirim dari landing page "Masuk sebagai Kurir"
        $sebagai = $this->pintuMasuk($request->query('sebagai'));

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

        // Pintu masuk kasir/kurir: hanya akun dengan role tersebut yang boleh lewat.
        $sebagai = $this->pintuMasuk($request->input('sebagai'));
        if ($sebagai) {
            $credentials['role'] = $sebagai;
        }

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            return redirect()->route(auth()->user()->homeRoute())
                ->with('success', 'Login berhasil! Selamat datang ' . auth()->user()->name);
        }

        return back()->withErrors([
            'email' => $sebagai
                ? "Email atau password salah, atau akun ini bukan akun {$sebagai}."
                : 'Email atau password salah.',
        ])->withInput($request->except('password'));
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

    private function pintuMasuk(mixed $nilai): ?string
    {
        return in_array($nilai, [User::ROLE_KASIR, User::ROLE_KURIR], true) ? $nilai : null;
    }

    // Halaman portal pengelola
    public function welcome()
    {
        return view('welcome');
    }
}