@php
    /*
    | Data terstruktur sekolah untuk Google. Isinya dibangun sebagai array PHP
    | lalu di-encode, bukan ditulis tangan sebagai JSON di dalam Blade, supaya
    | tanda kutip pada nama atau alamat tidak merusak dokumennya.
    |
    | Hanya data yang sudah dipastikan yang dimasukkan. Tautan media sosial
    | masih mengarah ke domain polos (https://instagram.com/), jadi sengaja
    | dilewati sampai ada alamat akun yang asli.
    */
    $sosial = collect(config('sekolah.sosial'))
        ->filter(fn (?string $url): bool => filled($url) && trim(parse_url($url, PHP_URL_PATH) ?: '/', '/') !== '')
        ->values()
        ->all();

    $data = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'EducationalOrganization',
        'name' => config('sekolah.nama'),
        'url' => url('/'),
        'image' => asset('images/og.jpg'),
        'description' => config('sekolah.tagline'),
        'telephone' => config('sekolah.telepon'),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => config('sekolah.alamat'),
            'addressLocality' => config('sekolah.kota'),
            'addressRegion' => config('sekolah.provinsi'),
            'addressCountry' => 'ID',
        ],
        'geo' => filled(config('sekolah.peta_lat')) ? [
            '@type' => 'GeoCoordinates',
            'latitude' => config('sekolah.peta_lat'),
            'longitude' => config('sekolah.peta_lng'),
        ] : null,
        'hasMap' => config('sekolah.peta_url'),
        'sameAs' => $sosial ?: null,
    ]);
@endphp

<script type="application/ld+json">{!! json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
