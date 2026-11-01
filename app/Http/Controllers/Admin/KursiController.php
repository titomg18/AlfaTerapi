<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\KursiRequest;
use App\Models\Kursi;

class KursiController extends Controller
{
    // READ
    public function index()
    {
        // Urut natural supaya "2" tampil sebelum "10".
        $kursis = Kursi::all()
            ->sortBy('no_kursi', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return view('admin.kursi', compact('kursis'));
    }

    // CREATE
    public function store(KursiRequest $request)
    {
        $kursi = Kursi::create($request->validated());

        return redirect()
            ->route('admin.kursi.index')
            ->with('success', 'Kursi "' . $kursi->no_kursi . '" berhasil ditambahkan.');
    }

    // UPDATE
    public function update(KursiRequest $request, Kursi $kursi)
    {
        $kursi->update($request->validated());

        return redirect()
            ->route('admin.kursi.index')
            ->with('success', 'Kursi "' . $kursi->no_kursi . '" berhasil diperbarui.');
    }

    // DELETE
    public function destroy(Kursi $kursi)
    {
        // Jangan hapus kursi yang sedang dipakai pasien
        if ($kursi->sedangDipakai()) {
            return redirect()
                ->route('admin.kursi.index')
                ->with('error', 'Kursi "' . $kursi->no_kursi . '" sedang dipakai dan tidak bisa dihapus.');
        }

        $no = $kursi->no_kursi;
        $kursi->delete();

        return redirect()
            ->route('admin.kursi.index')
            ->with('success', 'Kursi "' . $no . '" berhasil dihapus.');
    }
}