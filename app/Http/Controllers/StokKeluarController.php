<?php

namespace App\Http\Controllers;

use App\Models\StokKeluar;
use App\Models\Barang;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class StokKeluarController extends Controller
{
    // ===============================
// INDEX (WAJIB ADA)
// ===============================
    public function index()
    {
        $stokKeluar = StokKeluar::with('barang')
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('stok.keluar.index', compact('stokKeluar'));
    }

    // ===============================
    // CREATE
    // ===============================
    public function create()
    {
        $barangs = Barang::all();
        return view('stok.keluar.create', compact('barangs'));
    }

    // ===============================
    // EDIT
    // ===============================
    public function edit($id)
    {
        $stokKeluar = StokKeluar::findOrFail($id);
        $barangs = Barang::all();

        return view('stok.keluar.edit', compact('stokKeluar', 'barangs'));
    }

    // ===============================
    // STORE
    // ===============================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_barang' => 'required|exists:barang,id_barang',
            'jumlah'    => 'required|integer|min:1|max:9999',
            'tanggal'   => 'required|date',
        ]);

        $barang = DB::transaction(function () use ($validated) {
            $barang = Barang::lockForUpdate()->findOrFail($validated['id_barang']);

            if ($barang->stok < $validated['jumlah']) {
                throw ValidationException::withMessages([
                    'jumlah' => "Stok {$barang->nama_barang} hanya tersedia {$barang->stok} unit.",
                ]);
            }

            StokKeluar::create($validated);
            $barang->decrement('stok', $validated['jumlah']);

            return $barang;
        });

        Notifikasi::create([
            'id_user'    => auth()->id(),
            'id_pesanan' => null,
            'pesan'      => "Stok keluar baru: {$barang->nama_barang} (-{$validated['jumlah']})",
            'status'     => 'belum_dibaca',
            'tanggal'    => Carbon::now()
        ]);

        return redirect()->route('stok.keluar.index')
            ->with('success', 'Stok keluar berhasil ditambahkan');
    }

    // ===============================
    // UPDATE
    // ===============================
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_barang' => 'required|exists:barang,id_barang',
            'jumlah' => 'required|integer|min:1|max:9999',
            'tanggal' => 'required|date',
        ]);

        [$stokKeluar, $barang] = DB::transaction(function () use ($validated, $id) {
            $stokKeluar = StokKeluar::findOrFail($id);
            $oldBarang = Barang::lockForUpdate()->findOrFail($stokKeluar->id_barang);
            $barang = $oldBarang->id_barang === (int) $validated['id_barang']
                ? $oldBarang
                : Barang::lockForUpdate()->findOrFail($validated['id_barang']);

            $availableStock = $barang->stok + ($barang->is($oldBarang) ? $stokKeluar->jumlah : 0);
            if ($availableStock < $validated['jumlah']) {
                throw ValidationException::withMessages([
                    'jumlah' => "Stok {$barang->nama_barang} tidak mencukupi untuk transaksi ini.",
                ]);
            }

            if (! $barang->is($oldBarang)) {
                $oldBarang->increment('stok', $stokKeluar->jumlah);
            }

            $barang->decrement('stok', $validated['jumlah']);
            $stokKeluar->update($validated);

            return [$stokKeluar, $barang];
        });

        Notifikasi::create([
            'id_user'    => auth()->id(),
            'id_pesanan' => null,
            'pesan'      => "Update stok keluar: {$barang->nama_barang}",
            'status'     => 'belum_dibaca',
            'tanggal'    => Carbon::now()
        ]);

        return redirect()->route('stok.keluar.index')
            ->with('success', 'Stok keluar berhasil diperbarui');
    }

    // ===============================
    // DESTROY
    // ===============================
    public function destroy($id)
    {
        [$stokKeluar, $barang] = DB::transaction(function () use ($id) {
            $stokKeluar = StokKeluar::findOrFail($id);
            $barang = Barang::lockForUpdate()->findOrFail($stokKeluar->id_barang);
            $barang->increment('stok', $stokKeluar->jumlah);
            $stokKeluar->delete();

            return [$stokKeluar, $barang];
        });

        Notifikasi::create([
            'id_user'    => auth()->id(),
            'id_pesanan' => null,
            'pesan'      => "Hapus stok keluar: {$barang->nama_barang}",
            'status'     => 'belum_dibaca',
            'tanggal'    => Carbon::now()
        ]);

        return redirect()->route('stok.keluar.index')
            ->with('success', 'Stok keluar berhasil dihapus');
    }
}
