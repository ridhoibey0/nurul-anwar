<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alamat tempat tinggal pendaftar.
 *
 * Dipisah agar lima kolom alamat tidak menumpuk di pendaftaran_siswa dan
 * bagian alamat punya bentuk yang sama di mana pun dipakai.
 *
 * Catatan: hubungannya satu-ke-satu dengan pendaftaran dan tidak dipakai
 * bersama antar pendaftar, jadi pemisahan ini lebih ke kerapian struktur
 * daripada menghilangkan perulangan data.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alamat', function (Blueprint $table): void {
            $table->id();
            $table->text('jalan');
            $table->string('kelurahan', 80)->nullable();
            $table->string('kecamatan', 80)->nullable();
            $table->string('kota', 80)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alamat');
    }
};
