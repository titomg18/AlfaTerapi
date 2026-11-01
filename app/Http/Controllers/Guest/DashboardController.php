<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Kursi;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $kursis = Kursi::with('sesiAktif.pasien')
            ->get()
            ->sortBy('no_kursi', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $kursiTerpakai = $kursis->filter(fn ($k) => $k->sesiAktif)->count();
        $kursiKosong = max(0, $kursis->count() - $kursiTerpakai);

        if ($request->wantsJson() || $request->has('json')) {
            return response()->json([
                'kursis' => $kursis->map(fn ($k) => [
                    'id' => $k->id,
                    'no_kursi' => $k->no_kursi,
                    'keterangan' => $k->keterangan,
                    'sedang_terapi' => (bool) $k->sesiAktif,
                    'nama_pasien' => $k->sesiAktif?->pasien?->nama,
                    'nomor_sesi' => $k->sesiAktif?->nomor,
                    'selesai_pada_iso' => $k->sesiAktif?->selesai_pada?->toIso8601String(),
                ]),
                'kursiTerpakai' => $kursiTerpakai,
                'kursiKosong' => $kursiKosong,
                'totalKursi' => $kursis->count(),
                'serverTime' => now()->toIso8601String(),
            ]);
        }

        return view('guest.dashboard', [
            'kursis' => $kursis,
            'kursiTerpakai' => $kursiTerpakai,
            'kursiKosong' => $kursiKosong,
            'totalKursi' => $kursis->count(),
        ]);
    }
}
