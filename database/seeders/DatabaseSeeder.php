<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Tiap bagian berdiri sendiri supaya di server produksi bisa dipanggil
        // satu per satu dengan --class, tanpa ikut membawa data contoh.
        //
        // AkunPanitiaSeeder  : akun untuk masuk panel admin.
        // GaleriSeeder       : foto asli dari sekolah, aman untuk produksi.
        // PendaftaranSeeder  : 12 pendaftar fiktif, hanya untuk pengembangan.
        //
        // Artikel blog sengaja tidak diisi otomatis: panitia menulis sendiri
        // dari panel admin, supaya tidak ada tulisan karangan yang terbit
        // atas nama sekolah.
        $this->call([
            AkunPanitiaSeeder::class,
            GaleriSeeder::class,
            // PendaftaranSeeder::class,
        ]);
    }
}
