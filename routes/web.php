<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\PegawaiController;
use App\Http\Controllers\Admin\KursiController;
use App\Http\Controllers\Admin\TimerController;
use App\Http\Controllers\Admin\PasienController as AdminPasienController;
use App\Http\Controllers\Pegawai\PasienController as PegawaiPasienController;

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

// Khusus role pegawai (bisa daftar & edit pasien, tidak bisa hapus)
Route::middleware(['auth', 'role:pegawai'])->prefix('pegawai')->group(function () {
    Route::get('/dashboard', fn () => view('pegawai.dashboard'))->name('pegawai.dashboard');

    Route::get('/pasien', [PegawaiPasienController::class, 'index'])->name('pegawai.pasien.index');
    Route::post('/pasien', [PegawaiPasienController::class, 'store'])->name('pegawai.pasien.store');
    Route::put('/pasien/{pasien}', [PegawaiPasienController::class, 'update'])->name('pegawai.pasien.update');
});

// Khusus role admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', fn () => view('admin.dashboard'))->name('admin.dashboard');

    // CRUD pengguna (tambah & edit lewat pop-up)
    Route::get('/pegawai', [PegawaiController::class, 'index'])->name('admin.pegawai.index');
    Route::post('/pegawai', [PegawaiController::class, 'store'])->name('admin.pegawai.store');
    Route::put('/pegawai/{user}', [PegawaiController::class, 'update'])->name('admin.pegawai.update');
    Route::delete('/pegawai/{user}', [PegawaiController::class, 'destroy'])->name('admin.pegawai.destroy');

    // CRUD pasien (tambah & edit lewat pop-up)
    Route::get('/pasien', [AdminPasienController::class, 'index'])->name('admin.pasien.index');
    Route::post('/pasien', [AdminPasienController::class, 'store'])->name('admin.pasien.store');
    Route::put('/pasien/{pasien}', [AdminPasienController::class, 'update'])->name('admin.pasien.update');
    Route::delete('/pasien/{pasien}', [AdminPasienController::class, 'destroy'])->name('admin.pasien.destroy');

    // CRUD kursi (khusus admin; tambah & edit lewat pop-up)
    Route::get('/kursi', [KursiController::class, 'index'])->name('admin.kursi.index');
    Route::post('/kursi', [KursiController::class, 'store'])->name('admin.kursi.store');
    Route::put('/kursi/{kursi}', [KursiController::class, 'update'])->name('admin.kursi.update');
    Route::delete('/kursi/{kursi}', [KursiController::class, 'destroy'])->name('admin.kursi.destroy');

    // CRUD timer (khusus admin; berlaku untuk semua kursi)
    Route::get('/timer', [TimerController::class, 'index'])->name('admin.timer.index');
    Route::post('/timer', [TimerController::class, 'store'])->name('admin.timer.store');
    Route::put('/timer/{timer}', [TimerController::class, 'update'])->name('admin.timer.update');
    Route::delete('/timer/{timer}', [TimerController::class, 'destroy'])->name('admin.timer.destroy');
});