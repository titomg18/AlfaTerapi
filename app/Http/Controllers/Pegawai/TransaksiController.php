<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Concerns\MenyiapkanDataTransaksi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    use MenyiapkanDataTransaksi;

    public function index(Request $request)
    {
        return view('pegawai.transaksi', $this->dataTransaksi($request));
    }
}
