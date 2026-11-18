<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_masuk', function (Blueprint $table) {
            $table->increments('id_masuk');
            $table->unsignedInteger('id_barang');
            $table->integer('jumlah');
            $table->date('tanggal');
            $table->timestamps();

            $table->foreign('id_barang')->references('id_barang')->on('barang')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_masuk');
    }
};