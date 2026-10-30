<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\PegawaiController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

// Halaman utama: arahkan sesuai status login & role.
Route::get('/', function () {
    $user = auth()->user();

    if (! $user) {
        return redirect()->route('login');
    }

    $home = $user->homeRoute();
    abort_if(! $home, 403, 'Akun Anda belum memiliki role.');

    return redirect()->route($home);
});

// Khusus role admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', fn () => view('admin.dashboard'))->name('admin.dashboard');
});

// Khusus role pegawai
Route::middleware(['auth', 'role:pegawai'])->prefix('pegawai')->group(function () {
    Route::get('/dashboard', fn () => view('pegawai.dashboard'))->name('pegawai.dashboard');
});

// Khusus role admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', fn () => view('admin.dashboard'))->name('admin.dashboard');

    Route::get('/pegawai', [PegawaiController::class, 'index'])->name('admin.pegawai.index');
    Route::post('/pegawai', [PegawaiController::class, 'store'])->name('admin.pegawai.store');
});