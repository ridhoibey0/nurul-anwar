<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran_siswa', function (Blueprint $table) {
            $table->id();

            $table->string('nomor_pendaftaran', 32)->unique();
            $table->string('tahun_ajaran', 16);
            $table->string('jenjang', 16)->index();

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

            // Alamat
            $table->text('alamat');
            $table->string('kelurahan', 80)->nullable();
            $table->string('kecamatan', 80)->nullable();
            $table->string('kota', 80)->nullable();
            $table->string('kode_pos', 10)->nullable();

            // Riwayat
            $table->string('asal_sekolah')->nullable();
            $table->text('riwayat_kesehatan')->nullable();

            // Data ayah
            $table->string('nama_ayah');
            $table->string('nik_ayah', 16)->nullable();
            $table->string('pendidikan_ayah', 32)->nullable();
            $table->string('pekerjaan_ayah', 80)->nullable();
            $table->string('penghasilan_ayah', 40)->nullable();
            $table->string('telepon_ayah', 24)->nullable();

            // Data ibu
            $table->string('nama_ibu');
            $table->string('nik_ibu', 16)->nullable();
            $table->string('pendidikan_ibu', 32)->nullable();
            $table->string('pekerjaan_ibu', 80)->nullable();
            $table->string('penghasilan_ibu', 40)->nullable();
            $table->string('telepon_ibu', 24)->nullable();

            // Wali (opsional)
            $table->string('nama_wali')->nullable();
            $table->string('hubungan_wali', 40)->nullable();
            $table->string('telepon_wali', 24)->nullable();

            // Kontak utama
            $table->string('whatsapp', 24);
            $table->string('email')->nullable();

            // Berkas
            $table->string('berkas_kk')->nullable();
            $table->string('berkas_akta')->nullable();
            $table->string('berkas_foto')->nullable();

            $table->text('catatan_orang_tua')->nullable();

            // Proses seleksi
            $table->string('status', 24)->default('baru')->index();
            $table->text('catatan_admin')->nullable();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();

            $table->ipAddress('ip_pendaftar')->nullable();

            $table->timestamps();

            $table->index(['tahun_ajaran', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_siswa');
    }
};
