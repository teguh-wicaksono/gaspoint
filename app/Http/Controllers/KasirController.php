<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Pesanan;

class KasirController extends Controller
{
    // Halaman kasir (POS). Simpan pesanan memakai PesananController@store.
    public function index()
    {
        return view('kasir.index', [
            'barangs'        => Barang::orderBy('jenis')->orderBy('nama_barang')->get(),
            'pesananHariIni' => Pesanan::whereDate('tanggal', today())->count(),
            'omzetHariIni'   => (int) Pesanan::whereDate('tanggal', today())->sum('total_harga'),
            'terbaru'        => Pesanan::latest('id_pesanan')->limit(5)->get(),
            'online'         => $this->antreanOnline(),
        ]);
    }

    // Potongan HTML daftar pesanan online; dipanggil berkala oleh halaman kasir.
    public function online()
    {
        return view('kasir._online', ['online' => $this->antreanOnline()]);
    }

    private function antreanOnline()
    {
        return Pesanan::menungguPersetujuan()->with('detail.barang')->orderBy('id_pesanan')->get();
    }
}
