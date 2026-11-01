<?php

namespace App\Http\Controllers\Pegawai;

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
        return view('pegawai.voucher', $this->dataVoucher($request));
    }

    // CREATE: pasien membeli paket voucher
    public function store(JualVoucherRequest $request)
    {
        $pasien = Pasien::findOrFail($request->validated('pasien_id'));
        $timer = Timer::findOrFail($request->validated('timer_id'));

        $voucher = Voucher::jual($pasien, $timer, $request->user());

        return redirect()
            ->route('pegawai.voucher.index')
            ->with('success', 'Voucher '.$voucher->kode.' untuk '.$pasien->nama.' berhasil dibuat: '
                .$voucher->jumlah_sesi.' sesi, berlaku sampai '
                .$voucher->berlaku_sampai->locale('id')->translatedFormat('j F Y').'.');
    }

    // PAKAI: satu sesi dipakai di kursi kosong
    public function pakai(PakaiVoucherRequest $request, Voucher $voucher)
    {
        $kursi = Kursi::findOrFail($request->validated('kursi_id'));

        try {
            $sesi = $voucher->pakaiDi($kursi, $request->user());
        } catch (DomainException $e) {
            return redirect()->route('pegawai.voucher.index')->with('error', $e->getMessage());
        }

        return redirect()
            ->route('pegawai.voucher.index')
            ->with('success', 'Sesi ke-'.$sesi->nomor.' dari '.$voucher->jumlah_sesi.' ('.$voucher->kode.') dimulai di kursi '
                .$kursi->no_kursi.' selama '.$voucher->durasi_label.'.');
    }

    // Kartu kontrol terapi (siap cetak)
    public function kartu(Voucher $voucher)
    {
        $voucher->load(['pasien', 'sesis']);

        return view('voucher.kartu', ['voucher' => $voucher, 'prefix' => 'pegawai']);
    }
}
