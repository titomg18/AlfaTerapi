<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Kursi;
use App\Models\Timer;
use Illuminate\Contracts\View\View;

class TimerController extends Controller
{
    public function index(): View
    {
        return view('pegawai.timer', [
            'timers' => Timer::orderBy('durasi_menit')->get(),
            'jumlahKursi' => Kursi::count(),
        ]);
    }
}
