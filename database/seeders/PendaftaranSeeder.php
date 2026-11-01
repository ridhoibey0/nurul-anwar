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

        Pendaftaran::factory()->count(12)->create();
    }
}
