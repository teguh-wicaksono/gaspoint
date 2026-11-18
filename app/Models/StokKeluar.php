<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokKeluar extends Model
{
    protected $table = 'stok_keluar';
    protected $primaryKey = 'id_keluar';
    protected $fillable = ['id_barang', 'jumlah', 'tanggal'];
    protected $casts = ['tanggal' => 'date'];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }
}