<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\KursiController;
use App\Http\Controllers\Admin\PasienController as AdminPasienController;
use App\Http\Controllers\Admin\PegawaiController;
use App\Http\Controllers\Admin\TimerController;
use App\Http\Controllers\Admin\TransaksiController as AdminTransaksiController;
use App\Http\Controllers\Admin\VoucherController as AdminVoucherController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Guest\DashboardController as GuestDashboardController;
use App\Http\Controllers\Guest\RegisterController as GuestRegisterController;
use App\Http\Controllers\Pegawai\DashboardController as PegawaiDashboardController;
use App\Http\Controllers\Pegawai\KursiController as PegawaiKursiController;
use App\Http\Controllers\Pegawai\PasienController as PegawaiPasienController;
use App\Http\Controllers\Pegawai\TimerController as PegawaiTimerController;
use App\Http\Controllers\Pegawai\TransaksiController as PegawaiTransaksiController;
use App\Http\Controllers\Pegawai\VoucherController as PegawaiVoucherController;
use Illuminate\Support\Facades\Route;

// Halaman utama: TV Display / Monitor Antrean & Kursi Terapi
Route::get('/', [GuestDashboardController::class, 'index'])->name('guest.dashboard');

// Pendaftaran pasien mandiri untuk tamu (guest)
Route::get('/register', [GuestRegisterController::class, 'create'])->name('guest.register');
Route::post('/register', [GuestRegisterController::class, 'store'])->name('guest.register.store');

// Redirect praktis bila ada yang mengakses tanpa/dengan prefix althe
Route::redirect('/login', '/althe/login');
Route::redirect('/althe/register', '/register');
Route::redirect('/admin', '/althe/admin/dashboard');
Route::redirect('/pegawai', '/althe/pegawai/dashboard');

// Semua rute autentikasi & internal menggunakan prefix 'althe'
Route::prefix('althe')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [LoginController::class, 'create'])->name('login');
        Route::post('/login', [LoginController::class, 'store']);
    });

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

    // Khusus role pegawai (daftar & edit pasien, jual & pakai voucher, lihat transaksi, kursi & timer read-only)
    Route::middleware(['auth', 'role:pegawai', 'prevent-back-history'])->prefix('pegawai')->group(function () {
        Route::get('/dashboard', [PegawaiDashboardController::class, 'index'])->name('pegawai.dashboard');

        Route::get('/pasien', [PegawaiPasienController::class, 'index'])->name('pegawai.pasien.index');
        Route::post('/pasien', [PegawaiPasienController::class, 'store'])->name('pegawai.pasien.store');
        Route::put('/pasien/{pasien}', [PegawaiPasienController::class, 'update'])->name('pegawai.pasien.update');
        Route::delete('/pasien/{pasien}', [PegawaiPasienController::class, 'destroy'])->name('pegawai.pasien.destroy');
        Route::post('/pasien/{pasien}/approve', [PegawaiPasienController::class, 'approve'])->name('pegawai.pasien.approve');
        Route::post('/pasien/{pasien}/reject', [PegawaiPasienController::class, 'reject'])->name('pegawai.pasien.reject');

        // Kursi & Timer (khusus pegawai: hanya lihat / read-only)
        Route::get('/kursi', [PegawaiKursiController::class, 'index'])->name('pegawai.kursi.index');
        Route::get('/timer', [PegawaiTimerController::class, 'index'])->name('pegawai.timer.index');

        Route::get('/voucher', [PegawaiVoucherController::class, 'index'])->name('pegawai.voucher.index');
        Route::post('/voucher', [PegawaiVoucherController::class, 'store'])->name('pegawai.voucher.store');
        Route::post('/voucher/{voucher}/pakai', [PegawaiVoucherController::class, 'pakai'])->name('pegawai.voucher.pakai');
        Route::get('/voucher/{voucher}/kartu', [PegawaiVoucherController::class, 'kartu'])->name('pegawai.voucher.kartu');

        // Laporan pemakaian voucher (hanya lihat)
        Route::get('/transaksi', [PegawaiTransaksiController::class, 'index'])->name('pegawai.transaksi.index');
    });

    // Khusus role admin
    Route::middleware(['auth', 'role:admin', 'prevent-back-history'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

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
        Route::post('/pasien/{pasien}/approve', [AdminPasienController::class, 'approve'])->name('admin.pasien.approve');
        Route::post('/pasien/{pasien}/reject', [AdminPasienController::class, 'reject'])->name('admin.pasien.reject');

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

        // Voucher: jual, pakai 1 sesi, cetak kartu, hapus (hanya yang belum pernah dipakai)
        Route::get('/voucher', [AdminVoucherController::class, 'index'])->name('admin.voucher.index');
        Route::post('/voucher', [AdminVoucherController::class, 'store'])->name('admin.voucher.store');
        Route::post('/voucher/{voucher}/pakai', [AdminVoucherController::class, 'pakai'])->name('admin.voucher.pakai');
        Route::get('/voucher/{voucher}/kartu', [AdminVoucherController::class, 'kartu'])->name('admin.voucher.kartu');
        Route::delete('/voucher/{voucher}', [AdminVoucherController::class, 'destroy'])->name('admin.voucher.destroy');

        // Laporan pemakaian voucher (hanya lihat)
        Route::get('/transaksi', [AdminTransaksiController::class, 'index'])->name('admin.transaksi.index');
    });
});
