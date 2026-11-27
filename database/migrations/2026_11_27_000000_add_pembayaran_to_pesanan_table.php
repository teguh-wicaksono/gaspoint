<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * pembayaran: cash / qris (kasir), cod / transfer / qris (pembeli online). Pesanan lama: null.
     * menunggu_persetujuan: true untuk pesanan dari form pembeli sampai kasir menyetujui.
     * Default false, jadi pesanan lama dan pesanan buatan staf langsung dianggap disetujui.
     */
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->string('pembayaran', 10)->nullable()->after('metode');
            $table->boolean('menunggu_persetujuan')->default(false)->after('status')->index();
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropIndex(['menunggu_persetujuan']);
            $table->dropColumn(['pembayaran', 'menunggu_persetujuan']);
        });
    }
};
