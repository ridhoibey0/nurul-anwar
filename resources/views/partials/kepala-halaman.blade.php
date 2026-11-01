{{--
    Kepala halaman untuk sub-halaman (bukan beranda).
    Dipanggil dengan @include('partials.kepala-halaman', [...]).

    Variabel: judul (wajib, boleh berisi HTML), rintisan, teks, jejak.
--}}

@php
    $rintisan ??= null;
    $teks ??= null;
    $jejak ??= [];
@endphp

<section class="relative bg-hijau-700 pt-28 sm:pt-32">
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -left-20 top-0 h-64 w-64 rounded-full bg-hijau-600/60 blur-3xl"></div>
        <div class="absolute -right-16 top-10 h-64 w-64 rounded-full bg-hijau-800/70 blur-3xl"></div>
    </div>

    <div class="relative mx-auto w-full max-w-7xl px-5 pb-6 text-center sm:px-6 lg:px-10">
        @if (! empty($rintisan))
            <p class="teks-rintisan text-xs text-kuning-300">{{ $rintisan }}</p>
        @endif

        <h1 class="mx-auto mt-3 max-w-3xl font-display text-3xl leading-tight text-white sm:text-5xl">
            {!! $judul !!}
        </h1>

        @if (! empty($teks))
            <p class="mx-auto mt-5 max-w-xl text-sm leading-relaxed text-hijau-100 sm:text-base">
                {{ $teks }}
            </p>
        @endif

        @if (! empty($jejak))
            <nav class="mt-6 flex items-center justify-center gap-2 text-xs text-hijau-200" aria-label="Remah roti">
                <a href="{{ route('home') }}" class="hover:text-white">Beranda</a>
                @foreach ($jejak as $label => $url)
                    <x-ikon nama="chevron-kanan" class="h-3 w-3" />
                    @if ($url)
                        <a href="{{ $url }}" class="hover:text-white">{{ $label }}</a>
                    @else
                        <span class="text-white">{{ $label }}</span>
                    @endif
                @endforeach
            </nav>
        @endif
    </div>

    <x-awan class="mt-10 text-krem" />
</section>
