<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\BillController;
use Illuminate\Support\Facades\Auth;

// Auth Routes
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'registerProcess'])->name('register.process');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Tenant Routes
Route::get('/api/tenants', [TenantController::class, 'index'])->name('api.tenants');
Route::post('/tenants/store', [TenantController::class, 'store'])->name('tenants.store');
Route::delete('/tenants/{id}', [TenantController::class, 'destroy'])->name('tenants.destroy');

// Bill Routes (RUTE DATABASE TAGIHAN)
Route::get('/api/bills', [BillController::class, 'index'])->name('api.bills');
Route::post('/bills/store', [BillController::class, 'store'])->name('bills.store');
Route::post('/bills/pay/{id}', [BillController::class, 'pay'])->name('bills.pay'); // Rute Baru
Route::delete('/bills/{id}', [BillController::class, 'destroy'])->name('bills.destroy');


// Dashboard Route
Route::get('/', function () {
    if (!Auth::check()) return redirect()->route('login');
    $users = User::orderBy('created_at', 'desc')->get();
    return view('billpro.index', compact('users'));
})->name('dashboard');

// Admin & User Routes
Route::post('/users/approve/{id}', function($id) {
    if (Auth::user()->role !== 'admin') return back();
    $user = User::findOrFail($id);
    $user->is_approved = true;
    $user->save();
    return back()->with('success_admin', 'Akun disetujui!');
})->name('users.approve');

Route::delete('/users/delete/{id}', function($id) {
    if (Auth::user()->role !== 'admin') return back();
    User::findOrFail($id)->delete();
    return back()->with('success_admin', 'Akun dihapus!');
})->name('users.delete');