<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pendaftaran peserta didik baru.
 *
 * Tabel ini hanya memuat data yang memang atribut tunggal milik satu
 * pendaftaran. Yang bentuknya berulang atau punya struktur sendiri dipindah
 * ke tabelnya masing-masing:
 *
 *   jenjang            -> jenjang_id
 *   status seleksi     -> status_id
 *   alamat             -> alamat_id
 *   ayah / ibu / wali  -> pendaftaran_orang_tua
 *   berkas unggahan    -> pendaftaran_berkas
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran_siswa', function (Blueprint $table) {
            $table->id();

            $table->string('nomor_pendaftaran', 32)->unique();
            $table->string('tahun_ajaran', 16);

            // Jenjang dibatasi restrictOnDelete: jenjang yang sudah dipakai
            // pendaftar tidak boleh hilang, karena riwayat pendaftarannya
            // jadi tidak bisa dibaca lagi.
            $table->foreignId('jenjang_id')
                ->constrained('jenjang')
                ->restrictOnDelete();

            // Data calon siswa
            $table->string('nama_lengkap');
            $table->string('nama_panggilan', 60)->nullable();
            $table->char('jenis_kelamin', 1);
            $table->string('tempat_lahir', 80);
            $table->date('tanggal_lahir');
            $table->string('nik', 16)->nullable();
            $table->unsignedTinyInteger('anak_ke')->nullable();
            $table->unsignedTinyInteger('jumlah_saudara')->nullable();
            $table->string('agama', 32)->default('Islam');

            // Alamat tempat tinggal. Nullable supaya baris pendaftaran tetap
            // bisa dihapus alamatnya tanpa ikut menghapus pendaftarannya.
            $table->foreignId('alamat_id')
                ->nullable()
                ->constrained('alamat')
                ->nullOnDelete();

            // Riwayat
            $table->string('asal_sekolah')->nullable();
            $table->text('riwayat_kesehatan')->nullable();

            // Kontak utama yang dihubungi panitia
            $table->string('whatsapp', 24);
            $table->string('email')->nullable();

            $table->text('catatan_orang_tua')->nullable();

            // Proses seleksi
            $table->foreignId('status_id')
                ->constrained('status_pendaftaran')
                ->restrictOnDelete();
            $table->text('catatan_admin')->nullable();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();

            $table->ipAddress('ip_pendaftar')->nullable();

            $table->timestamps();

            $table->index(['tahun_ajaran', 'status_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_siswa');
    }
};
