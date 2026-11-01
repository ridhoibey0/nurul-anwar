<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artikel', function (Blueprint $table): void {
            $table->id();

            $table->string('judul');
            $table->string('slug', 191)->unique();
            $table->string('kategori', 80)->nullable();
            $table->text('ringkasan');
            $table->longText('isi');
            $table->string('gambar')->nullable();

            $table->string('status', 16)->default('draf');
            $table->timestamp('terbit_pada')->nullable();

            $table->foreignId('penulis_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'terbit_pada']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artikel');
    }
};
