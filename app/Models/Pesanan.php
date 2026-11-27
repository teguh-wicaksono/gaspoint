<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Pesanan extends Model
{
    public const PEMBAYARAN = ['cash' => 'Cash', 'qris' => 'QRIS', 'cod' => 'COD (bayar di tempat)', 'transfer' => 'Transfer'];

    protected $table = 'pesanan';
    protected $primaryKey = 'id_pesanan';
    protected $fillable = ['nama_pembeli', 'no_hp', 'alamat', 'metode', 'pembayaran', 'tanggal', 'total_harga', 'status', 'menunggu_persetujuan'];
    protected $casts = ['tanggal' => 'date', 'menunggu_persetujuan' => 'boolean'];

    /** Hanya pesanan yang diantar kurir (bukan beli langsung di toko). */
    public function scopeAntar($query)
    {
        return $query->where('metode', 'antar');
    }

    /** Pesanan online yang belum disetujui kasir. */
    public function scopeMenungguPersetujuan($query)
    {
        return $query->where('menunggu_persetujuan', true);
    }

    /** Antrean kurir: pesanan antar yang sudah disetujui kasir. */
    public function scopeUntukKurir($query)
    {
        return $query->antar()->where('menunggu_persetujuan', false);
    }

    public function getPembayaranLabelAttribute(): string
    {
        return self::PEMBAYARAN[$this->pembayaran] ?? '-';
    }

    public function getKodeAttribute(): string
    {
        return str_pad($this->id_pesanan, 4, '0', STR_PAD_LEFT);
    }

    public function detail(): HasMany
    {
        return $this->hasMany(PesananDetail::class, 'id_pesanan', 'id_pesanan');
    }

    /**
     * Buat pesanan + detail, hitung total dari harga di database, kurangi stok.
     * $items: [['id_barang' => 1, 'jumlah' => 2], ...] (sudah divalidasi). Dipakai form kasir dan form pembeli.
     *
     * @throws ValidationException bila stok tidak cukup
     */
    public static function buat(array $attributes, array $items): self
    {
        // Gabungkan baris dengan barang yang sama supaya pengecekan stok akurat.
        $lines = collect($items)
            ->groupBy('id_barang')
            ->map(fn ($rows, $id) => ['id_barang' => (int) $id, 'jumlah' => (int) $rows->sum('jumlah')])
            ->values();

        return DB::transaction(function () use ($attributes, $lines) {
            // Kunci semua barang yang dipakai agar stok tidak balapan antar transaksi.
            $barangs = Barang::whereIn('id_barang', $lines->pluck('id_barang'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id_barang');

            foreach ($lines as $line) {
                $barang = $barangs[$line['id_barang']];
                if ($barang->stok < $line['jumlah']) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$barang->nama_barang} hanya tersedia {$barang->stok} unit.",
                    ]);
                }
            }

            $pesanan = self::create($attributes + ['total_harga' => 0]);

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
    }
}
