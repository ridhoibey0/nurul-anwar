{{--
    Ikon tab peramban.

    Begitu panitia mengunggah logo dari Panel Admin > Identitas Sekolah, logo
    itu sekaligus jadi ikon tab, jadi tidak perlu ada isian unggahan tersendiri
    untuk favicon. Berkas favicon.ico tetap dipasang sebagai cadangan untuk
    peramban lama yang belum mengenal favicon PNG, dan sebagai satu-satunya
    ikon bila logonya dikosongkan.
--}}

<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

@if (\App\Support\Merek::pilihan('logo') === \App\Support\Merek::UNGGAHAN)
    <link rel="icon" href="{{ logo_url() }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ logo_url() }}">
@endif
