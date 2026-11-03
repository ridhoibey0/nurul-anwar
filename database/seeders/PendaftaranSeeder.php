<?php

namespace Database\Seeders;

use App\Models\Pendaftaran;
use Illuminate\Database\Seeder;

class PendaftaranSeeder extends Seeder
{
    /**
     * Data contoh agar panel admin tidak kosong saat pertama dibuka.
     * Aman dihapus sebelum website dipakai sungguhan.
     */
    public function run(): void
    {
        if (Pendaftaran::query()->exists()) {
            return;
        }

        // lengkap() ikut membuat data ayah, ibu, dan berkasnya, supaya
        // halaman detail pendaftar tidak tampil separuh kosong.
        Pendaftaran::factory()->count(12)->lengkap()->create();
    }
}
