<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Kursi;
use App\Models\Pasien;
use App\Models\Sesi;
use App\Models\Voucher;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    private const BATAS_SISA_SESI = 5;

    private const BATAS_HARI = 14;

    public function index(): View
    {
        // Kursi + sesi yang sedang berjalan (jika ada), urut natural
        $kursis = Kursi::with('sesiAktif.pasien')
            ->get()
            ->sortBy('no_kursi', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $kursiTerpakai = $kursis->filter(fn ($k) => $k->sesiAktif)->count();

        $hitung = '(select count(*) from sesis where sesis.voucher_id = vouchers.id)';

        $hampirHabis = Voucher::filterStatus('aktif')
            ->where(function ($w) use ($hitung) {
                $w->whereRaw("(vouchers.jumlah_sesi - {$hitung}) <= ?", [self::BATAS_SISA_SESI])
                    ->orWhere('berlaku_sampai', '<=', now()->addDays(self::BATAS_HARI)->endOfDay());
            });

        $voucherList = (clone $hampirHabis)
            ->with('pasien')
            ->withCount('sesis')
            ->orderByRaw("(vouchers.jumlah_sesi - {$hitung}) asc")
            ->orderBy('berlaku_sampai')
            ->limit(5)
            ->get();

        return view('pegawai.dashboard', [
            'totalPasien' => Pasien::count(),
            'pasienMingguIni' => Pasien::where('created_at', '>=', now()->startOfWeek())->count(),
            'kursis' => $kursis,
            'kursiTerpakai' => $kursiTerpakai,
            'kursiKosong' => max(0, $kursis->count() - $kursiTerpakai),
            'voucherAktif' => Voucher::filterStatus('aktif')->count(),
            'voucherHampirHabis' => (clone $hampirHabis)->count(),
            'voucherList' => $voucherList,
            'transaksiHariIni' => Sesi::selesai()->where('dimulai_pada', '>=', now()->startOfDay())->count(),
            'transaksiTerbaru' => Sesi::selesai()
                ->with(['pasien', 'voucher'])
                ->latest('dimulai_pada')
                ->limit(5)
                ->get(),
        ]);
    }
}
