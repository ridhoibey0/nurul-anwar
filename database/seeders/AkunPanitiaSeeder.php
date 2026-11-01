<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Akun panitia bawaan, supaya panel admin bisa dibuka setelah pemasangan.
 *
 * Dipisah dari DatabaseSeeder agar bisa dipanggil sendiri di server produksi
 * tanpa ikut membawa data contoh:
 *
 *     php artisan db:seed --class=AkunPanitiaSeeder --force
 *
 * Untuk server sungguhan lebih baik pakai `php artisan admin:akun`, karena
 * perintah itu menanyakan email dan sandi asli alih-alih memakai sandi bawaan
 * yang sudah tertulis di README.
 */
class AkunPanitiaSeeder extends Seeder
{
    public function run(): void
    {
        // Jangan menambah akun bawaan bila panitia sudah punya akunnya sendiri,
        // misalnya yang dibuat lewat `php artisan admin:akun` saat deploy.
        if (User::query()->exists()) {
            $this->command?->info('Akun panitia sudah ada, dilewati.');

            return;
        }

        User::query()->create([
            'name' => 'Panitia PPDB',
            'email' => 'admin@nurulanwar.website',
            // GANTI sandi ini setelah login pertama: php artisan admin:akun
            'password' => 'password',
        ]);

        $this->command?->warn('Akun bawaan dibuat: admin@nurulanwar.website / password');
        $this->command?->warn('Ganti sekarang juga dengan: php artisan admin:akun');
    }
}
