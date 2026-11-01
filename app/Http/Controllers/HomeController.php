<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\Galeri;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            // Navbar melayang transparan di atas hero hijau.
            'navTransparan' => true,

            // Angka di bagian statistik diturunkan dari data yang memang ada,
            // supaya tidak bisa menyimpang dari isi website.
            // Kotak yang angkanya kosong dibuang, bukan ditampilkan melompong:
            // akreditasi bisa saja sengaja dikosongkan dari panel admin.
            'statistik' => array_values(array_filter([
                ['angka' => (string) count(config('konten.jenjang')), 'label' => 'Jenjang Program'],
                ['angka' => (string) count(config('konten.guru')), 'label' => 'Guru & Kepala RA'],
                ['angka' => (string) config('sekolah.akreditasi'), 'label' => 'Akreditasi'],
            ], fn (array $item): bool => filled($item['angka']))),
            'jenjang' => config('konten.jenjang'),
            'keunggulan' => config('konten.keunggulan'),
            // Foto kegiatan diurus dari Panel Admin > Galeri Foto; bagian
            // Keseharian menyembunyikan diri selama belum ada foto.
            'galeri' => Galeri::query()->tampil()->urut()->get(),
            'guru' => config('konten.guru'),
            // Bagian testimoni menyembunyikan diri selama daftarnya kosong.
            'testimoni' => config('konten.testimoni'),
            // Blog kini diisi dari panel admin; bagian ini disembunyikan
            // selama belum ada artikel yang terbit.
            'artikel' => Artikel::query()->terbit()->terbaru()->limit(3)->get(),
        ]);
    }
}
