<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimerRequest;
use App\Models\Kursi;
use App\Models\Timer;

class TimerController extends Controller
{
    // READ
    public function index()
    {
        return view('admin.timer', [
            'timers' => Timer::orderBy('durasi_menit')->get(),
            // Setiap timer berlaku di semua kursi, jadi cukup dihitung jumlah kursinya.
            'jumlahKursi' => Kursi::count(),
        ]);
    }

    // CREATE
    public function store(TimerRequest $request)
    {
        $timer = Timer::create($request->validated());

        return redirect()
            ->route('admin.timer.index')
            ->with('success', 'Timer "'.$timer->nama.'" berhasil dibuat dan otomatis tersedia di semua kursi.');
    }

    // UPDATE
    public function update(TimerRequest $request, Timer $timer)
    {
        $timer->update($request->validated());

        return redirect()
            ->route('admin.timer.index')
            ->with('success', 'Timer "'.$timer->nama.'" berhasil diperbarui.');
    }

    // DELETE
    public function destroy(Timer $timer)
    {
        $nama = $timer->nama;
        $timer->delete();

        return redirect()
            ->route('admin.timer.index')
            ->with('success', 'Timer "'.$nama.'" berhasil dihapus dari semua kursi.');
    }
}
