<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Data ayah, ibu, dan wali sebagai baris, bukan kolom.
 *
 * Sebelumnya ketiganya ditulis sebagai kolom bernomor di pendaftaran_siswa:
 * nama_ayah/nik_ayah/pendidikan_ayah/... lalu diulang lagi untuk ibu dan
 * wali. Delapan belas kolom dengan bentuk yang sama persis -- kelompok
 * berulang yang seharusnya jadi tabel tersendiri.
 *
 * Dengan bentuk ini, menambah peran baru (mis. "nenek" sebagai penanggung
 * jawab) tidak perlu mengubah struktur tabel sama sekali.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran_orang_tua', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('pendaftaran_id')
                ->constrained('pendaftaran_siswa')
                ->cascadeOnDelete();

            $table->string('peran', 16);

            $table->string('nama');
            $table->string('nik', 16)->nullable();
            $table->string('pendidikan', 32)->nullable();
            $table->string('pekerjaan', 80)->nullable();
            $table->string('penghasilan', 40)->nullable();
            $table->string('telepon', 24)->nullable();

            // Hanya terisi untuk peran wali, mis. "Paman", "Kakek".
            $table->string('hubungan', 40)->nullable();

            $table->timestamps();

            // Satu pendaftaran tidak boleh punya dua ayah.
            $table->unique(['pendaftaran_id', 'peran']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_orang_tua');
    }
};
