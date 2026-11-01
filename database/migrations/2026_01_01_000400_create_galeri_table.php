<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galeri', function (Blueprint $table): void {
            $table->id();
            $table->string('judul', 160)->nullable();
            $table->string('gambar');
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('tampil')->default(true);
            $table->timestamps();

            // Beranda selalu mengambil foto yang tampil, terurut.
            $table->index(['tampil', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galeri');
    }
};
