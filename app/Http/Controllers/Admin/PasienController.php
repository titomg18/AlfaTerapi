<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasienRequest;
use App\Models\Pasien;
use Illuminate\Http\Request;

class PasienController extends Controller
{
    // READ
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $pasiens = Pasien::cari($q)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.pasien', compact('pasiens', 'q'));
    }

    // CREATE
    public function store(PasienRequest $request)
    {
        $pasien = Pasien::create($request->validated());

        return redirect()
            ->route('admin.pasien.index')
            ->with('success', 'Pasien "'.$pasien->nama.'" berhasil didaftarkan.');
    }

    // UPDATE
    public function update(PasienRequest $request, Pasien $pasien)
    {
        $pasien->update($request->validated());

        return redirect()
            ->route('admin.pasien.index')
            ->with('success', 'Data pasien "'.$pasien->nama.'" berhasil diperbarui.');
    }

    // DELETE (hanya admin)
    public function destroy(Pasien $pasien)
    {
        // Riwayat voucher tidak boleh hilang
        if ($pasien->vouchers()->exists()) {
            return redirect()
                ->route('admin.pasien.index')
                ->with('error', 'Pasien "'.$pasien->nama.'" tidak bisa dihapus karena sudah memiliki voucher.');
        }

        $nama = $pasien->nama;
        $pasien->delete();

        return redirect()
            ->route('admin.pasien.index')
            ->with('success', 'Pasien "'.$nama.'" berhasil dihapus.');
    }
}
