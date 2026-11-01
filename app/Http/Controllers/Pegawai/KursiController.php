<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Kursi;
use Illuminate\Contracts\View\View;

class KursiController extends Controller
{
    public function index(): View
    {
        // Urut natural supaya "2" tampil sebelum "10"
        $kursis = Kursi::with('sesiAktif.pasien')
            ->get()
            ->sortBy('no_kursi', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return view('pegawai.kursi', compact('kursis'));
    }
}
