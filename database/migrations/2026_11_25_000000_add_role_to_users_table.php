<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom role. Pembeli tidak punya akun, jadi hanya 3 role:
     * pengelola, kasir, kurir. User lama otomatis menjadi pengelola
     * (default) agar tidak kehilangan akses.
     */
    public function up(): void
    {
        // Database lama sudah punya kolom role (enum admin/pembeli/kurir) dari migrasi
        // 2026_11_19 yang sekarang dihapus: ubah jadi string dan petakan 'admin' ke 'pengelola'.
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 20)->default('pengelola')->change();
            });
            DB::table('users')->where('role', 'admin')->update(['role' => 'pengelola']);

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('pengelola')->after('password')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
