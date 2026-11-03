<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Status seleksi sebagai tabel referensi.
 *
 * Kolom "warna" dipakai lencana status di panel admin dan halaman cek status,
 * jadi label maupun warnanya bisa diubah tanpa menyentuh kode.
 *
 * Seperti jenjang, isinya diisi langsung di migrasi: tanpa status "baru",
 * pendaftaran yang masuk tidak punya status awal untuk dipasang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_pendaftaran', function (Blueprint $table): void {
            $table->id();
            $table->string('kode', 24)->unique();
            $table->string('label', 60);
            $table->string('warna', 20)->default('slate');
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });

        $sekarang = now();

        DB::table('status_pendaftaran')->insert([
            ['kode' => 'baru',         'label' => 'Menunggu Verifikasi',  'warna' => 'amber', 'urutan' => 1, 'created_at' => $sekarang, 'updated_at' => $sekarang],
            ['kode' => 'diverifikasi', 'label' => 'Berkas Terverifikasi', 'warna' => 'sky',   'urutan' => 2, 'created_at' => $sekarang, 'updated_at' => $sekarang],
            ['kode' => 'diterima',     'label' => 'Diterima',             'warna' => 'green', 'urutan' => 3, 'created_at' => $sekarang, 'updated_at' => $sekarang],
            ['kode' => 'ditolak',      'label' => 'Belum Diterima',       'warna' => 'rose',  'urutan' => 4, 'created_at' => $sekarang, 'updated_at' => $sekarang],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('status_pendaftaran');
    }
};
