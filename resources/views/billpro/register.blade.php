@extends('layouts.app')

@section('title', 'Register - PANDE MESARI Billpro')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-slate-100 px-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-8 border border-slate-200">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-black text-slate-900 font-luxury">DAFTAR AKUN</h2>
            <p class="text-xs text-slate-500 mt-1">Buat akun pengelola/admin baru</p>
        </div>

        <!-- FORM REGISTER -->
        <form action="{{ route('register.process') }}" method="POST" class="space-y-4">
            @csrf <!-- Wajib ada agar tidak error 419 -->

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" required class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Nama Anda">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                <input type="email" name="email" required class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="nama@email.com">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="••••••••">
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl text-sm shadow-lg shadow-blue-500/30 transition">
                Daftar Sekarang
            </button>
        </form>

        <div class="text-center mt-6">
            <p class="text-xs text-slate-500">Sudah punya akun? <a href="{{ route('login') }}" class="text-blue-600 font-bold hover:underline">Masuk disini</a></p>
        </div>
    </div>
</div>
@endsection