<?php

if (! function_exists('wa_link')) {
    /**
     * Membuat tautan WhatsApp ke nomor sekolah lengkap dengan pesan awal.
     */
    function wa_link(string $pesan = ''): string
    {
        $nomor = preg_replace('/[^0-9]/', '', (string) config('sekolah.whatsapp'));

        return 'https://wa.me/'.$nomor.($pesan !== '' ? '?text='.rawurlencode($pesan) : '');
    }
}

if (! function_exists('canonical_url')) {
    /**
     * URL kanonik halaman ini. Query string dibuang kecuali yang benar-benar
     * mengubah isi halaman (nomor halaman), supaya /blog?q=apa pun tidak
     * terindeks sebagai halaman kembar, tapi /blog?page=2 tetap berdiri
     * sendiri dengan daftar artikelnya yang berbeda.
     *
     * @param  array<int, string>  $simpan
     */
    function canonical_url(array $simpan = ['page']): string
    {
        $query = array_filter(
            request()->only($simpan),
            static fn ($nilai): bool => $nilai !== null && $nilai !== '' && $nilai !== '1',
        );

        return url()->current().($query ? '?'.http_build_query($query) : '');
    }
}

if (! function_exists('tel_link')) {
    /**
     * Mengubah nomor telepon yang ditulis untuk dibaca manusia, mis.
     * "(0265) 338279", menjadi tautan tel: yang bisa ditekan di ponsel.
     */
    function tel_link(): string
    {
        $nomor = preg_replace('/[^0-9]/', '', (string) config('sekolah.telepon'));

        // Nomor lokal yang diawali 0 diganti kode negara.
        if (str_starts_with((string) $nomor, '0')) {
            $nomor = '62'.substr((string) $nomor, 1);
        }

        return 'tel:+'.$nomor;
    }
}

if (! function_exists('peta_embed')) {
    /**
     * URL embed Google Maps. Memakai koordinat pin resmi bila tersedia agar
     * peta tidak bergantung pada hasil pencarian nama, yang bisa bergeser.
     */
    function peta_embed(): string
    {
        $lat = trim((string) config('sekolah.peta_lat'));
        $lng = trim((string) config('sekolah.peta_lng'));

        $kueri = ($lat !== '' && $lng !== '')
            ? $lat.','.$lng
            : (string) config('sekolah.peta_query');

        return 'https://maps.google.com/maps?q='
            .rawurlencode($kueri)
            .'&z=17&hl=id&output=embed';
    }
}

if (! function_exists('tanggal_id')) {
    /**
     * Format tanggal Indonesia, mis. "5 Januari 2026".
     */
    function tanggal_id(?\DateTimeInterface $tanggal, bool $denganJam = false): string
    {
        if (! $tanggal) {
            return '-';
        }

        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $hasil = $tanggal->format('j').' '.$bulan[(int) $tanggal->format('n')].' '.$tanggal->format('Y');

        return $denganJam ? $hasil.', '.$tanggal->format('H:i').' WIB' : $hasil;
    }
}
