<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@ranurulanwar.sch.id'],
            [
                'name' => 'Panitia PPDB',
                // GANTI password ini setelah login pertama.
                'password' => 'password',
            ]
        );

        // GaleriSeeder mendaftarkan foto asli dari sekolah yang sudah ada di
        // public/images/galeri, jadi aman dijalankan di server sungguhan.
        //
        // Artikel blog sengaja tidak diisi otomatis: panitia menulis sendiri
        // dari panel admin, supaya tidak ada tulisan karangan yang terbit
        // atas nama sekolah.
        $this->call([
            GaleriSeeder::class,
            // PendaftaranSeeder::class,
        ]);
    }
}
