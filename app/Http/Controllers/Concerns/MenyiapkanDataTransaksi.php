<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Sesi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait MenyiapkanDataTransaksi
{
    protected function dataTransaksi(Request $request): array
    {
        // Parameter yang tidak valid diabaikan, tidak membuat halaman error.
        $in = Validator::make($request->query(), [
            'q' => ['nullable', 'string', 'max:100'],
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d'],
        ])->valid();

        $q = trim((string) ($in['q'] ?? ''));
        $dari = $in['dari'] ?? null;
        $sampai = $in['sampai'] ?? null;

        // Kalau terbalik, tukar otomatis
        if ($dari && $sampai && $dari > $sampai) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        // Transaksi = sesi terapi yang timernya sudah habis.
        // Tanggal yang dipakai untuk filter adalah tanggal terapi dimulai.
        $base = Sesi::selesai()
            ->cari($q)
            ->when($dari, fn ($query) => $query->where('dimulai_pada', '>=', Carbon::parse($dari)->startOfDay()))
            ->when($sampai, fn ($query) => $query->where('dimulai_pada', '<=', Carbon::parse($sampai)->endOfDay()));

        return [
            'transaksis' => (clone $base)->with(['pasien', 'voucher'])->latest('dimulai_pada')->paginate(15)->withQueryString(),
            'totalSesi' => (clone $base)->count(),
            'totalMenit' => (int) (clone $base)->sum('durasi_menit'),
            'totalPasien' => (clone $base)->distinct()->count('pasien_id'),
            'q' => $q,
            'dari' => $dari,
            'sampai' => $sampai,
        ];
    }
}
