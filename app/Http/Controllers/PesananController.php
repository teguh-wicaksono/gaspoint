<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Notifikasi;
use App\Models\Pesanan;
use App\Models\PesananDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PesananController extends Controller
{
    protected const ITEMS_PER_PAGE = 20;

    // Urutan status yang diizinkan: pending -> diproses -> selesai
    protected const NEXT_STATUS = [
        'pending'  => 'diproses',
        'diproses' => 'selesai',
    ];

    // ===============================
    // INDEX: daftar pesanan + cari + filter + paginasi
    // ===============================
    public function index(Request $request)
    {
        $query = Pesanan::withCount('detail')->orderByDesc('tanggal')->orderByDesc('id_pesanan');

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_pembeli', 'LIKE', "%{$search}%")
                    ->orWhere('no_hp', 'LIKE', "%{$search}%");

                $id = ltrim($search, '#0');
                if ($id !== '' && ctype_digit($id)) {
                    $q->orWhere('id_pesanan', (int) $id);
                }
            });
        }

        if (in_array($request->query('status'), ['pending', 'diproses', 'selesai'], true)) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->query('tanggal'));
        }

        $pesanan = $query->paginate(self::ITEMS_PER_PAGE)->withQueryString();

        return view('pesanan.index', compact('pesanan'));
    }

    // ===============================
    // CREATE: form kasir
    // ===============================
    public function create()
    {
        $barangs = Barang::orderBy('jenis')->orderBy('nama_barang')->get();

        return view('pesanan.create', compact('barangs'));
    }

    // ===============================
    // STORE: simpan pesanan, kurangi stok
    // ===============================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pembeli'       => 'required|string|max:100',
            'no_hp'              => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'alamat'             => 'required|string|max:500',
            'tanggal'            => 'required|date',
            'items'              => 'required|array|min:1',
            'items.*.id_barang'  => 'required|integer|exists:barang,id_barang',
            'items.*.jumlah'     => 'required|integer|min:1|max:9999',
        ], [
            'nama_pembeli.required'      => 'Nama pembeli wajib diisi.',
            'no_hp.required'             => 'Nomor HP wajib diisi.',
            'no_hp.regex'                => 'Nomor HP hanya boleh berisi angka, spasi, tanda + dan -, minimal 8 karakter.',
            'alamat.required'            => 'Alamat wajib diisi.',
            'tanggal.required'           => 'Tanggal wajib diisi.',
            'tanggal.date'               => 'Format tanggal tidak valid.',
            'items.required'             => 'Tambahkan minimal satu barang.',
            'items.min'                  => 'Tambahkan minimal satu barang.',
            'items.*.id_barang.required' => 'Pilih barang pada setiap baris.',
            'items.*.id_barang.exists'   => 'Barang yang dipilih tidak ditemukan.',
            'items.*.jumlah.required'    => 'Jumlah wajib diisi pada setiap baris.',
            'items.*.jumlah.integer'     => 'Jumlah harus berupa angka bulat.',
            'items.*.jumlah.min'         => 'Jumlah minimal 1.',
        ]);

        // Gabungkan baris dengan barang yang sama supaya pengecekan stok akurat.
        $lines = collect($validated['items'])
            ->groupBy('id_barang')
            ->map(fn ($rows, $id) => ['id_barang' => (int) $id, 'jumlah' => (int) $rows->sum('jumlah')])
            ->values();

        $pesanan = DB::transaction(function () use ($validated, $lines) {
            // Kunci semua barang yang dipakai agar stok tidak balapan antar transaksi.
            $barangs = Barang::whereIn('id_barang', $lines->pluck('id_barang'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id_barang');

            $errors = [];
            foreach ($lines as $line) {
                $barang = $barangs[$line['id_barang']];
                if ($barang->stok < $line['jumlah']) {
                    $errors['items'] = "Stok {$barang->nama_barang} hanya tersedia {$barang->stok} unit.";
                    break;
                }
            }
            if ($errors) {
                throw ValidationException::withMessages($errors);
            }

            $pesanan = Pesanan::create([
                'nama_pembeli' => $validated['nama_pembeli'],
                'no_hp'        => $validated['no_hp'],
                'alamat'       => $validated['alamat'],
                'tanggal'      => $validated['tanggal'],
                'total_harga'  => 0,
                'status'       => 'pending',
            ]);

            $total = 0;
            foreach ($lines as $line) {
                $barang = $barangs[$line['id_barang']];
                // Harga selalu diambil dari database, bukan dari input pengguna.
                $subtotal = $barang->harga * $line['jumlah'];

                PesananDetail::create([
                    'id_pesanan' => $pesanan->id_pesanan,
                    'id_barang'  => $barang->id_barang,
                    'jumlah'     => $line['jumlah'],
                    'harga'      => $barang->harga,
                    'subtotal'   => $subtotal,
                ]);

                $barang->decrement('stok', $line['jumlah']);
                $total += $subtotal;
            }

            $pesanan->update(['total_harga' => $total]);

            return $pesanan;
        });

        $this->notify($pesanan, 'Pesanan baru #' . $this->kode($pesanan) . ' dari ' . $pesanan->nama_pembeli);

        if ($request->input('dari') === 'kasir') {
            return redirect()->route('kasir.index')
                ->with('success', 'Pesanan #' . $this->kode($pesanan) . ' berhasil dibuat. Total Rp ' . number_format($pesanan->total_harga, 0, ',', '.'));
        }

        return redirect()->route('pesanan.show', $pesanan)
            ->with('success', 'Pesanan berhasil dibuat.');
    }

    // ===============================
    // SHOW: detail pesanan
    // ===============================
    public function show(Pesanan $pesanan)
    {
        $pesanan->load('detail.barang');

        return view('pesanan.show', [
            'pesanan'    => $pesanan,
            'nextStatus' => self::NEXT_STATUS[$pesanan->status] ?? null,
        ]);
    }

    // ===============================
    // UPDATE STATUS: pending -> diproses -> selesai
    // ===============================
    public function updateStatus(Pesanan $pesanan)
    {
        $next = self::NEXT_STATUS[$pesanan->status] ?? null;

        if ($next === null) {
            return back()->with('error', 'Pesanan yang sudah selesai tidak dapat diubah statusnya.');
        }

        $pesanan->update(['status' => $next]);

        $this->notify($pesanan, 'Pesanan #' . $this->kode($pesanan) . " berstatus {$next}");

        return back()->with('success', "Status pesanan diubah menjadi {$next}.");
    }

    // ===============================
    // DESTROY: batalkan pesanan, stok dikembalikan
    // ===============================
    public function destroy(Pesanan $pesanan)
    {
        if ($pesanan->status === 'selesai') {
            return back()->with('error', 'Pesanan yang sudah selesai tidak dapat dihapus.');
        }

        $kode = $this->kode($pesanan);

        DB::transaction(function () use ($pesanan) {
            $pesanan->load('detail');

            foreach ($pesanan->detail as $detail) {
                Barang::lockForUpdate()->find($detail->id_barang)?->increment('stok', $detail->jumlah);
            }

            $pesanan->delete(); // pesanan_detail ikut terhapus (cascade)
        });

        Notifikasi::create([
            'id_user'    => auth()->id(),
            'id_pesanan' => null,
            'pesan'      => "Pesanan #{$kode} dibatalkan, stok dikembalikan",
            'status'     => 'belum_dibaca',
            'tanggal'    => Carbon::now(),
        ]);

        return redirect()->route('pesanan.index')
            ->with('success', "Pesanan #{$kode} dibatalkan dan stok dikembalikan.");
    }

    private function kode(Pesanan $pesanan): string
    {
        return str_pad($pesanan->id_pesanan, 4, '0', STR_PAD_LEFT);
    }

    private function notify(Pesanan $pesanan, string $pesan): void
    {
        Notifikasi::create([
            'id_user'    => auth()->id(),
            'id_pesanan' => $pesanan->id_pesanan,
            'pesan'      => $pesan,
            'status'     => 'belum_dibaca',
            'tanggal'    => Carbon::now(),
        ]);
    }
}
