<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasienRequest;
use App\Models\Pasien;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PasienController extends Controller
{
    /**
     * Tampilkan daftar pasien beserta filter status dan pencarian.
     */
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));

        $counts = [
            'total' => Pasien::count(),
            'pending' => Pasien::pending()->count(),
            'disetujui' => Pasien::disetujui()->count(),
            'ditolak' => Pasien::ditolak()->count(),
        ];

        $pasiens = Pasien::cari($q)
            ->filterStatus($status)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.pasien', compact('pasiens', 'q', 'status', 'counts'));
    }

    /**
     * Daftarkan pasien baru oleh admin (otomatis berstatus disetujui).
     */
    public function store(PasienRequest $request): RedirectResponse
    {
        $pasien = Pasien::create([
            ...$request->validated(),
            'status' => Pasien::STATUS_DISETUJUI,
        ]);

        return redirect()
            ->route('admin.pasien.index')
            ->with('success', 'Pasien "'.$pasien->nama.'" berhasil didaftarkan.');
    }

    /**
     * Perbarui data pasien.
     */
    public function update(PasienRequest $request, Pasien $pasien): RedirectResponse
    {
        $pasien->update($request->validated());

        return redirect()
            ->route('admin.pasien.index')
            ->with('success', 'Data pasien "'.$pasien->nama.'" berhasil diperbarui.');
    }

    /**
     * Setujui (ACC) pendaftaran pasien dari tamu.
     */
    public function approve(Pasien $pasien): RedirectResponse
    {
        $pasien->update(['status' => Pasien::STATUS_DISETUJUI]);

        return back()->with('success', 'Pasien "'.$pasien->nama.'" berhasil disetujui (ACC).');
    }

    /**
     * Tolak pendaftaran pasien.
     */
    public function reject(Request $request, Pasien $pasien): RedirectResponse
    {
        if ($pasien->vouchers()->exists()) {
            return back()->with('error', 'Pasien "'.$pasien->nama.'" tidak bisa ditolak karena sudah memiliki voucher.');
        }

        $pasien->update([
            'status' => Pasien::STATUS_DITOLAK,
            'catatan_penolakan' => $request->input('catatan_penolakan'),
        ]);

        return back()->with('success', 'Pendaftaran pasien "'.$pasien->nama.'" telah ditolak.');
    }

    /**
     * Hapus data pasien (hanya jika belum memiliki riwayat voucher).
     */
    public function destroy(Pasien $pasien): RedirectResponse
    {
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
