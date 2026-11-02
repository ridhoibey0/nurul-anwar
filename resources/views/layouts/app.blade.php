<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('sekolah.nama').' — '.config('sekolah.tagline'))</title>

    <meta name="description" content="@yield('description', 'Website resmi '.config('sekolah.nama').', Raudhatul Athfal yang mengutamakan pendidikan karakter, nilai keislaman, dan tumbuh kembang anak usia dini lewat belajar sambil bermain.')">

    {{-- Alamat tunggal tiap halaman: tanpa ini /blog?q=apa pun terindeks
         sebagai halaman kembar. --}}
    <link rel="canonical" href="{{ canonical_url() }}">

    @hasSection('robots')
        <meta name="robots" content="@yield('robots')">
    @endif

    <meta property="og:site_name" content="{{ config('sekolah.nama') }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:title" content="@yield('title', config('sekolah.nama'))">
    <meta property="og:description" content="@yield('description', config('sekolah.tagline'))">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ canonical_url() }}">
    {{-- Tautan yang dibagikan orang tua lewat WhatsApp tampil dengan gambar
         ini bila halamannya tidak punya gambar sendiri.

         Bila gambar bagikan dikosongkan dari panel admin, tag-nya tidak
         ditulis sama sekali: og:image yang isinya kosong membuat sebagian
         aplikasi menampilkan kotak gambar rusak, bukan pratinjau tanpa
         gambar. --}}
    @php($ogGambar = \Illuminate\Support\Facades\View::getSection('og_image') ?: gambar_bagikan_url())

    @if ($ogGambar)
        <meta property="og:image" content="{{ $ogGambar }}">
        <meta property="og:image:alt" content="@yield('og_image_alt', 'Siswa dan guru '.config('sekolah.nama'))">
        <meta name="twitter:card" content="summary_large_image">
    @else
        <meta name="twitter:card" content="summary">
    @endif

    @yield('kepala')

    @include('partials.favicon')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col font-sans">
    @include('partials.navbar')

    <main class="flex-1">
        @yield('content')
    </main>

    @include('partials.footer')
</body>
</html>
