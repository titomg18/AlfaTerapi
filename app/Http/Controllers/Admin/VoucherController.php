<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\MenyiapkanDataVoucher;
use App\Http\Controllers\Controller;
use App\Http\Requests\JualVoucherRequest;
use App\Http\Requests\PakaiVoucherRequest;
use App\Models\Kursi;
use App\Models\Pasien;
use App\Models\Timer;
use App\Models\Voucher;
use DomainException;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    use MenyiapkanDataVoucher;

    // READ
    public function index(Request $request)
    {
        return view('admin.voucher', $this->dataVoucher($request));
    }

    // CREATE: pasien membeli voucher
    public function store(JualVoucherRequest $request)
    {
        $pasien = Pasien::findOrFail($request->validated('pasien_id'));
        $timer  = Timer::findOrFail($request->validated('timer_id'));

        $voucher = Voucher::jual($pasien, $timer, $request->user());

        return redirect()
            ->route('admin.voucher.index')
            ->with('success', 'Voucher ' . $voucher->kode . ' (' . $voucher->nama_timer . ') untuk ' . $pasien->nama . ' berhasil dibuat.');
    }

    // PAKAI: voucher dipakai di kursi kosong
    public function pakai(PakaiVoucherRequest $request, Voucher $voucher)
    {
        $kursi = Kursi::findOrFail($request->validated('kursi_id'));

        try {
            $voucher->pakaiDi($kursi);
        } catch (DomainException $e) {
            return redirect()->route('admin.voucher.index')->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.voucher.index')
            ->with('success', 'Voucher ' . $voucher->kode . ' dipakai di kursi ' . $kursi->no_kursi . ' selama ' . $voucher->durasi_label . '.');
    }

    // DELETE: hanya voucher yang belum dipakai (hanya admin)
    public function destroy(Voucher $voucher)
    {
        if ($voucher->dipakai_pada) {
            return redirect()
                ->route('admin.voucher.index')
                ->with('error', 'Voucher yang sudah dipakai tidak bisa dihapus.');
        }

        $kode = $voucher->kode;
        $voucher->delete();

        return redirect()
            ->route('admin.voucher.index')
            ->with('success', 'Voucher ' . $kode . ' berhasil dihapus.');
    }
}