<?php

/*
|--------------------------------------------------------------------------
| Identitas Sekolah
|--------------------------------------------------------------------------
|
| Identitas sekolah. Hampir semuanya kini bisa diubah dari Panel Admin >
| Identitas Sekolah, dan nilainya disimpan di tabel "pengaturan".
|
| Nilai di bawah ini cuma dipakai sebagai nilai awal: begitu panitia menekan
| Simpan di panel admin, nilai dari databaselah yang berlaku. Jadi mengubah
| .env setelah itu tidak lagi berpengaruh.
|
*/

return [

    'nama' => env('SCHOOL_NAME', 'RA Nurul Anwar'),
    'nama_pendek' => env('SCHOOL_SHORT_NAME', 'RA Nurul Anwar'),
    'tagline' => env('SCHOOL_TAGLINE', 'Menumbuhkan Cinta Belajar Sejak Dini'),
    'tahun_berdiri' => (int) env('SCHOOL_FOUNDED_YEAR', 2005),
    'jumlah_guru' => env('SCHOOL_TEACHER_COUNT', '6'),
    'akreditasi' => env('SCHOOL_ACCREDITATION', 'B'),

    /*
    | Logo dan gambar bagikan (yang tampil saat tautan website dikirim lewat
    | WhatsApp). Keduanya diunggah dari Panel Admin > Identitas Sekolah dan
    | berisi jalur relatif terhadap public/.
    |
    | Dibiarkan kosong di sini dengan sengaja: nilai bawaannya ditulis di
    | App\Support\Merek::BAWAAN, dan dibaca lewat logo_url() serta
    | gambar_bagikan_url().
    */
    'logo' => env('SCHOOL_LOGO'),
    'gambar_bagikan' => env('SCHOOL_SHARE_IMAGE'),

    // Alamat dan telepon mengikuti papan nama di gedung sekolah.
    'alamat' => env('SCHOOL_ADDRESS', 'Jl. Winaya Utama No. 20 RT 04 RW 10, Perum Cikunten Indah, Kelurahan Kahuripan, Kec. Tawang, Kota Tasikmalaya'),
    // Dipisah supaya data terstruktur untuk Google bisa menyebut kota dan
    // provinsi sebagai bidang tersendiri, bukan satu baris alamat panjang.
    'kota' => env('SCHOOL_CITY', 'Kota Tasikmalaya'),
    'provinsi' => env('SCHOOL_PROVINCE', 'Jawa Barat'),
    // Koordinat diambil dari pin Google Maps resmi sekolah. Bila keduanya diisi,
    // peta memakai koordinat (pin tepat); bila kosong, jatuh ke pencarian nama.
    'peta_query' => env('SCHOOL_MAP_QUERY', 'KB RA TPA DTA Nurul Anwar'),
    'peta_lat' => env('SCHOOL_MAP_LAT', '-7.3562979'),
    'peta_lng' => env('SCHOOL_MAP_LNG', '108.2236263'),
    'peta_url' => env('SCHOOL_MAP_URL', 'https://maps.app.goo.gl/VT6PuUPbryvVJp3c7'),
    'jam_operasional' => env('SCHOOL_HOURS', 'Senin - Jumat, 07.00 - 13.00 WIB'),

    'telepon' => env('SCHOOL_PHONE', '(0265) 338279'),

    // Format 62xxxxxxxxxx. Diisi dari panel admin; yang di sini cuma contoh.
    'whatsapp' => env('SCHOOL_WHATSAPP', '6281234567890'),

    'email' => env('SCHOOL_EMAIL', 'info@ranurulanwar.sch.id'),
    'instagram' => env('SCHOOL_INSTAGRAM', '@ra.nurulanwar'),

    'sosial' => [
        'instagram' => env('SCHOOL_INSTAGRAM_URL', 'https://instagram.com/'),
        'facebook' => env('SCHOOL_FACEBOOK_URL', 'https://facebook.com/'),
        'youtube' => env('SCHOOL_YOUTUBE_URL', 'https://youtube.com/'),
    ],

    'navigasi' => [
        ['url' => '/#beranda', 'label' => 'Beranda'],
        ['url' => '/#program', 'label' => 'Program'],
        ['url' => '/#keseharian', 'label' => 'Keseharian'],
        // Mengarah ke halaman blog penuh, bukan jangkar di beranda: bagian itu
        // tersembunyi selama belum ada artikel yang terbit.
        ['url' => '/blog', 'label' => 'Blog'],
        ['url' => '/#lokasi', 'label' => 'Kontak'],
    ],

];
