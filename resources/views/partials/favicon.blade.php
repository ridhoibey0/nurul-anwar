{{--
    Ikon tab peramban.

    Begitu panitia mengunggah logo dari Panel Admin > Identitas Sekolah, logo
    itu sekaligus jadi ikon tab, jadi tidak perlu ada isian unggahan tersendiri
    untuk favicon. Berkas favicon.ico tetap dipasang sebagai cadangan untuk
    peramban lama yang belum mengenal favicon PNG.
--}}

<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

@unless (\App\Support\Merek::bawaan('logo'))
    <link rel="icon" href="{{ logo_url() }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ logo_url() }}">
@endunless
