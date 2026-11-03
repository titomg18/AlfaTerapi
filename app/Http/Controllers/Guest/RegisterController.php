<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterPasienRequest;
use App\Models\Pasien;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class RegisterController extends Controller
{
    /**
     * Tampilkan formulir pendaftaran mandiri pasien.
     */
    public function create(): View
    {
        return view('guest.register');
    }

    /**
     * Simpan data pendaftaran pasien oleh tamu (status pending).
     */
    public function store(RegisterPasienRequest $request): RedirectResponse
    {
        $pasien = Pasien::create([
            ...$request->validated(),
            'status' => Pasien::STATUS_PENDING,
        ]);

        return redirect()
            ->route('guest.register')
            ->with('registered_pasien', [
                'nama' => $pasien->nama,
                'no_telepon' => $pasien->no_telepon,
            ])
            ->with('success', 'Pendaftaran Anda berhasil dikirim! Silakan tunggu konfirmasi (ACC) dari staf kami saat Anda tiba di klinik.');
    }
}
