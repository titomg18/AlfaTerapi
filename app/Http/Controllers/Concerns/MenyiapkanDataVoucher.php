<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Kursi;
use App\Models\Pasien;
use App\Models\Timer;
use App\Models\Voucher;
use Illuminate\Http\Request;

trait MenyiapkanDataVoucher
{
    protected function dataVoucher(Request $request): array
    {
        $q      = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        return [
            'vouchers' => Voucher::with(['pasien', 'kursi'])
                ->cari($q)
                ->filterStatus($status)
                ->latest()
                ->paginate(10)
                ->withQueryString(),

            // Kursi + voucher yang sedang berjalan (jika ada), urut natural
            'kursis' => Kursi::with('sesiAktif.pasien')
                ->get()
                ->sortBy('no_kursi', SORT_NATURAL | SORT_FLAG_CASE)
                ->values(),

            'timers'  => Timer::orderBy('durasi_menit')->get(),
            'pasiens' => Pasien::orderBy('nama')->get(['id', 'nama', 'no_telepon']),

            'q'      => $q,
            'status' => $status,
        ];
    }
}