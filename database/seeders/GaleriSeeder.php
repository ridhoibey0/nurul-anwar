<?php

namespace Database\Seeders;

use App\Models\Galeri;
use Illuminate\Database\Seeder;

/**
 * Foto asli dari pihak sekolah, sudah diperkecil dan ikut di dalam repo pada
 * public/images/galeri. Berbeda dengan data pendaftar contoh, isi seeder ini
 * memang untuk dipakai sungguhan.
 *
 * Judul di bawah ditulis sekadar menerangkan apa yang terlihat di foto.
 * Silakan perbaiki lewat Panel Admin > Galeri Foto bila ada acara atau
 * kegiatan yang namanya lebih tepat.
 */
class GaleriSeeder extends Seeder
{
    public function run(): void
    {
        $foto = [
            // Urutannya mengikuti bentuk tiap foto: pada kolase, slot ke-1
            // tiap kelompok enam memanjang ke bawah (pas untuk potret) dan
            // slot ke-3 melebar dua kolom (pas untuk lanskap).
            ['menulis-di-kelas.jpg', 'Latihan menulis di kelas'],
            ['pelepasan-siswa-ra.jpg', 'Acara pelepasan siswa RA Nurul Anwar'],
            ['karya-mewarnai-ikan.jpg', 'Hasil karya mewarnai di kelas'],
            ['kebersamaan-di-halaman-sekolah.jpg', 'Siswa dan guru di halaman sekolah'],
            ['kegiatan-di-halaman-sekolah.jpg', 'Kegiatan bersama siswa di halaman sekolah'],
            ['foto-bersama-wisuda.jpg', 'Foto bersama peserta wisuda'],
        ];

        foreach ($foto as $i => [$berkas, $judul]) {
            Galeri::query()->firstOrCreate(
                ['gambar' => 'images/galeri/'.$berkas],
                [
                    'judul' => $judul,
                    'urutan' => $i + 1,
                    'tampil' => true,
                ]
            );
        }
    }
}
