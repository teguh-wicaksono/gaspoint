<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokMasuk extends Model
{
    protected $table = 'stok_masuk';
    protected $primaryKey = 'id_masuk';
    protected $fillable = ['id_barang', 'jumlah', 'tanggal'];
    protected $casts = ['tanggal' => 'date'];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }
}