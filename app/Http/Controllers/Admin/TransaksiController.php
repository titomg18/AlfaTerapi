<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        // Parameter yang tidak valid diabaikan, tidak membuat halaman error.
        $in = Validator::make($request->query(), [
            'q'      => ['nullable', 'string', 'max:100'],
            'dari'   => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d'],
        ])->valid();

        $q      = trim((string) ($in['q'] ?? ''));
        $dari   = $in['dari'] ?? null;
        $sampai = $in['sampai'] ?? null;

        // Kalau terbalik, tukar otomatis
        if ($dari && $sampai && $dari > $sampai) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        // Transaksi = voucher yang sudah dipakai dan timernya sudah habis.
        // Tanggal yang dipakai untuk filter adalah tanggal terapi dimulai.
        $transaksis = Voucher::filterStatus('selesai')
            ->cari($q)
            ->when($dari, fn ($query) => $query->where('dipakai_pada', '>=', Carbon::parse($dari)->startOfDay()))
            ->when($sampai, fn ($query) => $query->where('dipakai_pada', '<=', Carbon::parse($sampai)->endOfDay()))
            ->with('pasien')
            ->latest('dipakai_pada')
            ->paginate(15)
            ->withQueryString();

        return view('admin.transaksi', compact('transaksis', 'q', 'dari', 'sampai'));
    }
}