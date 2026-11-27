<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Notifikasi;
use App\Models\Pesanan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        // Tanpa pilihan metode (form lama) dianggap diantar, seperti sebelumnya.
        $request->mergeIfMissing(['metode' => 'antar']);

        // Beli langsung (di toko): data pembeli opsional, pembayaran wajib cash/QRIS.
        // Diantar (buatan staf): nama, HP, dan alamat wajib; pembayaran boleh kosong.
        $pembayaran = $request->input('metode') === 'langsung' ? 'required|in:cash,qris' : 'nullable|in:cod,transfer,qris';

        $validated = $request->validate([
            'metode'             => 'required|in:antar,langsung',
            'pembayaran'         => $pembayaran,
            'nama_pembeli'       => 'required_if:metode,antar|nullable|string|max:100',
            'no_hp'              => ['required_if:metode,antar', 'nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'alamat'             => 'required_if:metode,antar|nullable|string|max:500',
            'tanggal'            => 'required|date',
            'items'              => 'required|array|min:1',
            'items.*.id_barang'  => 'required|integer|exists:barang,id_barang',
            'items.*.jumlah'     => 'required|integer|min:1|max:9999',
        ], [
            'nama_pembeli.required_if'   => 'Nama pembeli wajib diisi.',
            'no_hp.required_if'          => 'Nomor HP wajib diisi.',
            'no_hp.regex'                => 'Nomor HP hanya boleh berisi angka, spasi, tanda + dan -, minimal 8 karakter.',
            'alamat.required_if'         => 'Alamat wajib diisi.',
            'pembayaran.required'        => 'Pilih metode pembayaran (cash atau QRIS).',
            'pembayaran.in'              => 'Metode pembayaran tidak valid.',
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

        $antar = $validated['metode'] === 'antar';

        $pesanan = Pesanan::buat([
            'nama_pembeli' => $validated['nama_pembeli'] ?? 'Pembeli langsung',
            'no_hp'        => $validated['no_hp'] ?? '-',
            'alamat'       => $antar ? $validated['alamat'] : '-',
            'metode'       => $validated['metode'],
            'pembayaran'   => $validated['pembayaran'] ?? null,
            'tanggal'      => $validated['tanggal'],
            // Beli langsung sudah dibayar dan dibawa pulang, jadi langsung selesai.
            'status'       => $antar ? 'pending' : 'selesai',
        ], $validated['items']);

        $this->notify($pesanan, 'Pesanan baru #' . $this->kode($pesanan) . ' dari ' . $pesanan->nama_pembeli);

        if ($request->input('dari') === 'kasir') {
            return redirect()->route('kasir.index')
                ->with('success', 'Pesanan #' . $this->kode($pesanan) . ' berhasil dibuat (' . $pesanan->pembayaran_label . '). Total Rp ' . number_format($pesanan->total_harga, 0, ',', '.'));
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
        if ($pesanan->menunggu_persetujuan) {
            return back()->with('error', 'Pesanan online ini belum disetujui kasir.');
        }

        $next = self::NEXT_STATUS[$pesanan->status] ?? null;

        if ($next === null) {
            return back()->with('error', 'Pesanan yang sudah selesai tidak dapat diubah statusnya.');
        }

        $pesanan->update(['status' => $next]);

        $this->notify($pesanan, 'Pesanan #' . $this->kode($pesanan) . " berstatus {$next}");

        return back()->with('success', "Status pesanan diubah menjadi {$next}.");
    }

    // ===============================
    // SETUJUI: kasir menyetujui pesanan online, lalu masuk antrean kurir
    // ===============================
    public function setujui(Pesanan $pesanan)
    {
        if (! $pesanan->menunggu_persetujuan) {
            return back()->with('error', 'Pesanan ini tidak sedang menunggu persetujuan.');
        }

        $pesanan->update(['menunggu_persetujuan' => false]);

        $this->notify($pesanan, 'Pesanan #' . $this->kode($pesanan) . ' disetujui, diteruskan ke kurir');

        return back()->with('success', 'Pesanan #' . $this->kode($pesanan) . ' disetujui dan diteruskan ke kurir.');
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
