@props(['nama', 'isi' => false])

{{--
    Kumpulan ikon garis (stroke) sederhana agar tidak perlu memuat pustaka ikon.
    Pakai: <x-ikon nama="telepon" class="h-5 w-5" />
--}}

<svg
    {{ $attributes->merge(['class' => 'h-5 w-5', 'aria-hidden' => 'true']) }}
    viewBox="0 0 24 24"
    fill="{{ $isi ? 'currentColor' : 'none' }}"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
>
    @switch($nama)
        @case('panah-kanan')
            <path d="M5 12h14M13 6l6 6-6 6" />
            @break

        @case('chevron-kanan')
            <path d="M9 6l6 6-6 6" />
            @break

        @case('chevron-bawah')
            <path d="M6 9l6 6 6-6" />
            @break

        @case('menu')
            <path d="M4 7h16M4 12h16M4 17h16" />
            @break

        @case('tutup')
            <path d="M6 6l12 12M18 6L6 18" />
            @break

        @case('telepon')
            <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z" />
            @break

        @case('surel')
            <path d="M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
            <path d="m3 7 9 6 9-6" />
            @break

        @case('peta')
            <path d="M12 21s7-5.6 7-11a7 7 0 1 0-14 0c0 5.4 7 11 7 11Z" />
            <circle cx="12" cy="10" r="2.5" />
            @break

        @case('jam')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l3 2" />
            @break

        @case('chat')
            <path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 9 9 0 0 1-3.7-.8L3 21l1.9-5a8.4 8.4 0 0 1-.8-3.6 8.4 8.4 0 0 1 8.4-8.4h.5a8.4 8.4 0 0 1 8 8Z" />
            @break

        @case('bintang')
            <path d="m12 3 2.6 5.6 6 .8-4.4 4.2 1.1 6.1L12 16.8 6.7 19.7l1.1-6.1L3.4 9.4l6-.8L12 3Z" />
            @break

        @case('perisai')
            <path d="M12 3 5 6v6c0 4.4 3 8.3 7 9 4-.7 7-4.6 7-9V6l-7-3Z" />
            <path d="m9 12 2 2 4-4" />
            @break

        @case('lencana')
            <circle cx="12" cy="9" r="5.5" />
            <path d="m8.5 13.5-1 7.5 4.5-2.5 4.5 2.5-1-7.5" />
            @break

        @case('hati')
            <path d="M12 20s-7-4.3-7-9.2A4 4 0 0 1 12 8a4 4 0 0 1 7 2.8c0 4.9-7 9.2-7 9.2Z" />
            @break

        @case('centang')
            <path d="m5 12.5 4.5 4.5L19 7.5" />
            @break

        @case('centang-lingkaran')
            <circle cx="12" cy="12" r="9" />
            <path d="m8.5 12.3 2.4 2.4 4.6-4.9" />
            @break

        @case('berkas')
            <path d="M14 3H7a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V7l-4-4Z" />
            <path d="M14 3v4h4" />
            @break

        @case('unduh')
            <path d="M12 4v10m0 0 4-4m-4 4-4-4" />
            <path d="M5 19h14" />
            @break

        @case('cari')
            <circle cx="11" cy="11" r="6.5" />
            <path d="m20 20-4-4" />
            @break

        @case('pengguna')
            <circle cx="12" cy="8" r="4" />
            <path d="M4.5 20a7.5 7.5 0 0 1 15 0" />
            @break

        @case('keluar')
            <path d="M15 4h3a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-3" />
            <path d="M10 8 6 12l4 4M6 12h9" />
            @break

        @case('sampah')
            <path d="M4 7h16M9 7V5h6v2M6 7l1 13h10l1-13" />
            @break

        @case('dashboard')
            <path d="M4 4h7v7H4zM13 4h7v4h-7zM13 11h7v9h-7zM4 14h7v6H4z" />
            @break

        @case('daftar')
            <path d="M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01" />
            @break

        @case('foto')
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            @break

        @case('pensil')
            <path d="M12 20h9" />
            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />
            @break

        @case('roda-gigi')
            <circle cx="12" cy="12" r="3" />
            <path d="M12 2.5v2.2M12 19.3v2.2M4.9 4.9l1.6 1.6M17.5 17.5l1.6 1.6M2.5 12h2.2M19.3 12h2.2M4.9 19.1l1.6-1.6M17.5 6.5l1.6-1.6" />
            @break

        @case('info')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 11v5M12 8h.01" />
            @break

        @case('peringatan')
            <path d="M12 4 2.5 20h19L12 4Z" />
            <path d="M12 10v4M12 17h.01" />
            @break

        @default
            <circle cx="12" cy="12" r="9" />
    @endswitch
</svg>
