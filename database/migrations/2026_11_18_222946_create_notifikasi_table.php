<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->increments('id_notifikasi');
            $table->unsignedBigInteger('id_user')->nullable();
            $table->unsignedInteger('id_pesanan')->nullable();
            $table->string('pesan', 255);
            $table->enum('status', ['belum_dibaca', 'dibaca'])->default('belum_dibaca');
            $table->dateTime('tanggal');
            $table->timestamps();

            $table->foreign('id_user')->references('id')->on('users')->nullOnDelete();
            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanan')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};