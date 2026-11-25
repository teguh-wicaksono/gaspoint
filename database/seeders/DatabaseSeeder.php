<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Rumah Gas',
            'email' => 'admin@rumahgas.test',
            'password' => 'password123',
            'role' => User::ROLE_PENGELOLA,
        ]);

        Barang::create(['nama_barang' => 'Gas LPG 3 kg', 'jenis' => 'gas', 'harga' => 22000, 'stok' => 50]);
        Barang::create(['nama_barang' => 'Gas LPG 12 kg', 'jenis' => 'gas', 'harga' => 190000, 'stok' => 20]);
        Barang::create(['nama_barang' => 'Galon 19 liter', 'jenis' => 'galon', 'harga' => 20000, 'stok' => 30]);
    }
}