<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\MenyiapkanDataTransaksi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    use MenyiapkanDataTransaksi;

    public function index(Request $request)
    {
        return view('admin.transaksi', $this->dataTransaksi($request));
    }
}