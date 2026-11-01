<?php

/*
|--------------------------------------------------------------------------
| Konten Halaman Depan
|--------------------------------------------------------------------------
|
| Teks dan gambar untuk setiap bagian landing page, supaya bisa diganti
| tanpa menyentuh file Blade.
|
| Jangan menulis angka, kutipan, atau klaim yang belum dipastikan ke pihak
| sekolah. Website ini mewakili lembaga yang nyata.
|
*/

return [

    'jenjang' => [
        [
            'nama' => 'Kelompok Bermain',
            'usia' => '2 - 4 tahun',
            'deskripsi' => 'Pengenalan rutinitas, bermain sensorik, dan belajar berbagi bersama teman sebaya.',
            'gambar' => 'images/kiddies/kids-a.png',
            'warna' => 'kuning',
        ],
        [
            'nama' => 'RA Kelompok A',
            'usia' => '4 - 5 tahun',
            'deskripsi' => 'Mengenal huruf hijaiyah, angka, dan kebiasaan ibadah harian lewat kegiatan sentra.',
            'gambar' => 'images/kiddies/girl-scissors.png',
            'warna' => 'hijau',
        ],
        [
            'nama' => 'RA Kelompok B',
            'usia' => '5 - 6 tahun',
            'deskripsi' => 'Persiapan masuk SD/MI: membaca permulaan, menulis, berhitung, dan kemandirian.',
            'gambar' => 'images/kiddies/boy-pink.png',
            'warna' => 'koral',
        ],
        [
            'nama' => 'TPA & DTA',
            'usia' => '4 - 12 tahun',
            'deskripsi' => 'Program sore untuk mengaji, hafalan surat pendek, dan pendidikan akhlak.',
            'gambar' => 'images/kiddies/kids-b.png',
            'warna' => 'biru',
        ],
    ],

    'keunggulan' => [
        [
            'judul' => 'Lingkungan Aman',
            'deskripsi' => 'Area bermain tertutup dan penjemputan anak hanya oleh orang yang sudah terdaftar.',
            'ikon' => 'shield',
            'warna' => 'hijau',
        ],
        [
            'judul' => 'Guru yang Dikenal Anak',
            'deskripsi' => 'Jumlah guru yang tetap dari tahun ke tahun, jadi anak belajar dengan wajah yang sudah ia kenal.',
            'ikon' => 'badge',
            'warna' => 'kuning',
        ],
        [
            'judul' => 'Nilai Islami Sehari-hari',
            'deskripsi' => 'Pembiasaan salat duha, doa harian, dan adab sederhana yang dibawa pulang ke rumah.',
            'ikon' => 'heart',
            'warna' => 'koral',
        ],
    ],

    // Foto kegiatan tidak lagi di sini: dikelola dari Panel Admin > Galeri Foto.

    /*
    | Isi 'gambar' dengan path foto bila sudah tersedia, misalnya
    | 'images/guru/yani-dewi-fatimah.jpg'. Selama masih null, website
    | menampilkan inisial nama di atas lingkaran berwarna.
    */
    'guru' => [
        ['nama' => 'Yani Dewi Fatimah, S.Pd.', 'peran' => 'Kepala RA', 'gambar' => null],
        ['nama' => 'Mela Siti Maryam, S.Pd.', 'peran' => 'Guru', 'gambar' => null],
        ['nama' => 'Deti Kushendiani, S.Pd.', 'peran' => 'Guru', 'gambar' => null],
        ['nama' => 'Ida Komalasari', 'peran' => 'Guru', 'gambar' => null],
        ['nama' => 'Pipit Sopiah', 'peran' => 'Guru', 'gambar' => null],
        ['nama' => 'Iis Setiawati, S.Pd.I.', 'peran' => 'Guru', 'gambar' => null],
    ],

    /*
    | Kutipan orang tua yang sungguh-sungguh pernah mengatakannya, dan sudah
    | mengizinkan namanya dipakai. Selama daftar ini kosong, bagian testimoni
    | tidak ditampilkan di beranda. Jangan diisi dengan kutipan karangan.
    |
    | ['nama' => '...', 'peran' => 'Orang tua siswa RA B', 'kutipan' => '...', 'avatar' => null],
    */
    'testimoni' => [],

    // Blog tidak lagi di sini: artikel dikelola dari Panel Admin > Blog.

];
