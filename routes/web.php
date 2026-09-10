<?php


use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;

// Auth Routes
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'registerProcess'])->name('register.process');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard Route
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }
    // Ambil semua data user dari database, urutkan dari yang terbaru
    $users = User::orderBy('created_at', 'desc')->get();
    
    return view('billpro.index', compact('users'));
})->name('dashboard');

// Route untuk Setujui Akun
Route::post('/users/approve/{id}', function($id) {
    if (Auth::user()->role !== 'admin') return back(); // Hanya admin yang boleh
    $user = User::findOrFail($id);
    $user->is_approved = true;
    $user->save();
    return back()->with('success_admin', 'Akun ' . $user->name . ' berhasil disetujui!');
})->name('users.approve');

// Route untuk Hapus Akun
Route::delete('/users/delete/{id}', function($id) {
    if (Auth::user()->role !== 'admin') return back(); 
    if (Auth::id() == $id) return back()->with('error_admin', 'Tidak bisa menghapus akun sendiri!');
    
    User::findOrFail($id)->delete();
    return back()->with('success_admin', 'Akun berhasil dihapus!');
})->name('users.delete');