<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pesanan extends Model
{
    protected $table = 'pesanan';
    protected $primaryKey = 'id_pesanan';
    protected $fillable = ['nama_pembeli', 'no_hp', 'alamat', 'metode', 'tanggal', 'total_harga', 'status'];
    protected $casts = ['tanggal' => 'date'];

    /** Hanya pesanan yang diantar kurir (bukan beli langsung di toko). */
    public function scopeAntar($query)
    {
        return $query->where('metode', 'antar');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(PesananDetail::class, 'id_pesanan', 'id_pesanan');
    }
}