<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Notifikasi;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class PembeliController extends Controller
{
    // Formulir pesanan untuk pembeli (tanpa login).
    public function index()
    {
        return view('pembeli.pesan', [
            'barangs' => Barang::orderBy('jenis')->orderBy('nama_barang')->get(),
        ]);
    }

    // Pesanan masuk berstatus menunggu persetujuan kasir; stok langsung dipesan (dikembalikan bila ditolak).
    public function store(Request $request)
    {
        // Form mengirim jumlah per barang (jumlah[id_barang]); baris berjumlah 0 dibuang.
        $request->merge(['items' => collect($request->input('jumlah', []))
            ->filter(fn ($q) => is_numeric($q) && (int) $q > 0)
            ->map(fn ($q, $id) => ['id_barang' => $id, 'jumlah' => $q])
            ->values()->all()]);

        $data = $request->validate([
            'nama_pembeli'      => 'required|string|max:100',
            'no_hp'             => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'alamat'            => 'required|string|max:500',
            'pembayaran'        => 'required|in:cod,transfer,qris',
            'items'             => 'required|array|min:1',
            'items.*.id_barang' => 'required|integer|exists:barang,id_barang',
            'items.*.jumlah'    => 'required|integer|min:1|max:9999',
        ], [
            'nama_pembeli.required'      => 'Nama wajib diisi.',
            'no_hp.required'             => 'Nomor HP wajib diisi.',
            'no_hp.regex'                => 'Nomor HP hanya boleh berisi angka, spasi, tanda + dan -, minimal 8 karakter.',
            'alamat.required'            => 'Alamat pengantaran wajib diisi.',
            'pembayaran.required'        => 'Pilih metode pembayaran.',
            'pembayaran.in'              => 'Metode pembayaran tidak valid.',
            'items.required'             => 'Pilih minimal satu barang.',
            'items.min'                  => 'Pilih minimal satu barang.',
            'items.*.id_barang.exists'   => 'Barang yang dipilih tidak ditemukan.',
            'items.*.jumlah.integer'     => 'Jumlah harus berupa angka bulat.',
        ]);

        $pesanan = Pesanan::buat([
            'nama_pembeli'         => $data['nama_pembeli'],
            'no_hp'                => $data['no_hp'],
            'alamat'               => $data['alamat'],
            'metode'               => 'antar',
            'pembayaran'           => $data['pembayaran'],
            'tanggal'              => today()->toDateString(),
            'status'               => 'pending',
            'menunggu_persetujuan' => true,
        ], $data['items']);

        Notifikasi::create([
            'id_user'    => null,
            'id_pesanan' => $pesanan->id_pesanan,
            'pesan'      => "Pesanan online #{$pesanan->kode} dari {$pesanan->nama_pembeli} menunggu persetujuan",
            'status'     => 'belum_dibaca',
            'tanggal'    => now(),
        ]);

        return redirect()->route('dashboard.pembeli')->with('pesanan_baru', [
            'kode'       => $pesanan->kode,
            'total'      => $pesanan->total_harga,
            'pembayaran' => $pesanan->pembayaran,
        ]);
    }
}
