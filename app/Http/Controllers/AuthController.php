<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function index()
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('billpro.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            // CEK APAKAH USER SUDAH DI-APPROVE OLEH ADMIN
            if (Auth::user()->is_approved == false) {
                Auth::logout(); // Keluarkan paksa
                return back()->with('error', 'Akun Anda belum diverifikasi. Silakan hubungi Admin Utama!');
            }

            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->with('error', 'Email atau password salah!');
    }

    public function register()
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('billpro.register');
    }

    public function registerProcess(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        // CEK APAKAH INI PENDAFTAR PERTAMA DI DATABASE
        $isFirstUser = User::count() === 0;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            // Jika pendaftar pertama, otomatis jadi Admin & disetujui (true)
            'is_approved' => $isFirstUser ? true : false, 
            'role' => $isFirstUser ? 'admin' : 'user',
        ]);

        // Jika pendaftar pertama, langsung login
        if ($isFirstUser) {
            Auth::login($user);
            return redirect()->route('dashboard');
        }

        // Jika bukan pendaftar pertama, lempar kembali ke halaman login dengan pesan sukses
        return redirect()->route('login')->with('success', 'Pendaftaran berhasil! Silakan tunggu verifikasi dari Admin Utama untuk bisa masuk.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}