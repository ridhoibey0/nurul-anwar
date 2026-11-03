<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Jenjang program sebagai tabel referensi.
 *
 * Sebelumnya tertulis sebagai array di config/ppdb.php. Dipindah ke tabel
 * supaya pendaftaran_siswa menyimpan jenjang_id, bukan menyalin ulang teks
 * jenjang di tiap barisnya.
 *
 * Isinya diisi langsung di migrasi ini, bukan lewat seeder, karena tanpa
 * jenjang formulir pendaftaran tidak punya pilihan apa pun -- data ini wajib
 * ada, bukan data contoh.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenjang', function (Blueprint $table): void {
            $table->id();
            $table->string('kode', 16)->unique();
            $table->string('nama', 100);
            $table->string('usia', 40)->nullable();
            $table->unsignedInteger('kuota')->default(0);
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });

        $sekarang = now();

        DB::table('jenjang')->insert([
            ['kode' => 'kb',   'nama' => 'Kelompok Bermain (KB)',                 'usia' => '2 - 4 tahun',  'kuota' => 25, 'urutan' => 1, 'created_at' => $sekarang, 'updated_at' => $sekarang],
            ['kode' => 'ra-a', 'nama' => 'RA Kelompok A',                         'usia' => '4 - 5 tahun',  'kuota' => 30, 'urutan' => 2, 'created_at' => $sekarang, 'updated_at' => $sekarang],
            ['kode' => 'ra-b', 'nama' => 'RA Kelompok B',                         'usia' => '5 - 6 tahun',  'kuota' => 30, 'urutan' => 3, 'created_at' => $sekarang, 'updated_at' => $sekarang],
            ['kode' => 'tpa',  'nama' => 'Taman Pendidikan Al-Quran (TPA)',       'usia' => '4 - 12 tahun', 'kuota' => 40, 'urutan' => 4, 'created_at' => $sekarang, 'updated_at' => $sekarang],
            ['kode' => 'dta',  'nama' => 'Diniyah Takmiliyah Awaliyah (DTA)',     'usia' => '7 - 12 tahun', 'kuota' => 40, 'urutan' => 5, 'created_at' => $sekarang, 'updated_at' => $sekarang],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('jenjang');
    }
};
