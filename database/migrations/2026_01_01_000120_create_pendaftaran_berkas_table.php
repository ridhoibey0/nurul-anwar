<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Berkas unggahan pendaftar sebagai baris, bukan kolom.
 *
 * Sebelumnya berkas_kk, berkas_akta, dan berkas_foto: satu hal yang sama
 * dinomori jadi tiga kolom. Menambah syarat berkas baru berarti mengubah
 * struktur tabel dan setiap tempat yang menyebut ketiganya.
 *
 * Jalur berkasnya tetap menunjuk ke disk privat (storage/app/private/ppdb),
 * bukan ke public/ -- isinya Kartu Keluarga dan akta kelahiran.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran_berkas', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('pendaftaran_id')
                ->constrained('pendaftaran_siswa')
                ->cascadeOnDelete();

            $table->string('jenis', 16);
            $table->string('path');

            $table->timestamps();

            // Satu jenis berkas sekali saja per pendaftaran; unggahan baru
            // menimpa yang lama, bukan menumpuk.
            $table->unique(['pendaftaran_id', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_berkas');
    }
};
