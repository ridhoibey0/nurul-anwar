<?php

/*
|--------------------------------------------------------------------------
| Pengaturan PPDB (Penerimaan Peserta Didik Baru)
|--------------------------------------------------------------------------
*/

return [

    /*
    | Empat nilai berikut diatur panitia dari Panel Admin > Pengaturan PPDB dan
    | disimpan di tabel "pengaturan". Nilai di sini hanya nilai bawaan selama
    | tabel tersebut masih kosong; lihat App\Models\Pengaturan.
    */

    'buka' => filter_var(env('PPDB_OPEN', true), FILTER_VALIDATE_BOOLEAN),

    'tahun_ajaran' => env('PPDB_YEAR', '2026/2027'),

    'dibuka_pada' => env('PPDB_OPENS_AT', '2026-01-05'),
    'ditutup_pada' => env('PPDB_CLOSES_AT', '2026-06-30'),

    'prefix_nomor' => 'PPDB',

    /*
    | Jenjang program dan status seleksi tidak lagi di sini: keduanya sudah
    | jadi tabel referensi (jenjang dan status_pendaftaran) supaya
    | pendaftaran_siswa menyimpan id-nya, bukan menyalin ulang teksnya di
    | tiap baris. Isinya dibuat saat migrasi.
    |
    | Lihat App\Models\Jenjang dan App\Models\StatusPendaftaran.
    */

    'berkas' => [
        'maks_kb' => 2048,
        'tipe' => ['jpg', 'jpeg', 'png', 'pdf'],
    ],

    'alur' => [
        [
            'judul' => 'Isi Formulir Online',
            'deskripsi' => 'Lengkapi data calon siswa, data orang tua, dan unggah berkas pendukung lewat formulir di website ini.',
        ],
        [
            'judul' => 'Verifikasi Berkas',
            'deskripsi' => 'Panitia memeriksa kelengkapan berkas maksimal 3 hari kerja, lalu menghubungi orang tua lewat WhatsApp.',
        ],
        [
            'judul' => 'Observasi & Wawancara',
            'deskripsi' => 'Anak dan orang tua diundang ke sekolah untuk perkenalan ringan bersama wali kelas.',
        ],
        [
            'judul' => 'Pengumuman & Daftar Ulang',
            'deskripsi' => 'Hasil dapat dicek mandiri lewat menu Cek Status menggunakan nomor pendaftaran.',
        ],
    ],

    'syarat' => [
        'Fotokopi Kartu Keluarga (KK)',
        'Fotokopi Akta Kelahiran anak',
        'Pas foto anak ukuran 3x4 (2 lembar)',
        'Fotokopi KTP ayah dan ibu',
        'Fotokopi Kartu Indonesia Pintar / KIS bila ada',
    ],

];
