<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * metode: 'antar' (masuk antrean kurir) atau 'langsung' (dibeli di toko, langsung selesai).
     * Pesanan lama tetap 'antar' supaya perilakunya tidak berubah.
     */
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->string('metode', 10)->default('antar')->after('alamat')->index();
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropIndex(['metode']);
            $table->dropColumn('metode');
        });
    }
};
