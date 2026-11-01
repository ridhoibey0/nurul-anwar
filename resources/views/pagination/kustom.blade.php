{{--
    Paginasi sendiri, bukan bawaan Laravel.

    View bawaan ada di dalam vendor/, dan Tailwind v4 hanya memindai folder yang
    terdaftar di @source pada resources/css/app.css — jadi kelas di view bawaan
    tidak pernah ikut ter-compile dan paginasi tampil tanpa gaya.
--}}

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-wrap items-center justify-center gap-2">
        {{-- Sebelumnya --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex h-10 items-center gap-1.5 rounded-full border-2 border-hijau-100 px-4 text-sm font-semibold text-tinta-lembut/60">
                <x-ikon nama="chevron-kanan" class="h-4 w-4 rotate-180" />
                Sebelumnya
            </span>
        @else
            <a
                href="{{ $paginator->previousPageUrl() }}"
                rel="prev"
                class="inline-flex h-10 items-center gap-1.5 rounded-full border-2 border-hijau-700 px-4 text-sm font-semibold text-hijau-700 transition-colors hover:bg-hijau-700 hover:text-white"
            >
                <x-ikon nama="chevron-kanan" class="h-4 w-4 rotate-180" />
                Sebelumnya
            </a>
        @endif

        {{-- Nomor halaman --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="inline-flex h-10 w-10 items-center justify-center text-sm text-tinta-lembut">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $halaman => $url)
                    @if ($halaman == $paginator->currentPage())
                        <span aria-current="page" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-hijau-700 text-sm font-semibold text-white">
                            {{ $halaman }}
                        </span>
                    @else
                        <a
                            href="{{ $url }}"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-sm font-semibold text-tinta transition-colors hover:bg-hijau-100 hover:text-hijau-700"
                        >
                            {{ $halaman }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Berikutnya --}}
        @if ($paginator->hasMorePages())
            <a
                href="{{ $paginator->nextPageUrl() }}"
                rel="next"
                class="inline-flex h-10 items-center gap-1.5 rounded-full border-2 border-hijau-700 px-4 text-sm font-semibold text-hijau-700 transition-colors hover:bg-hijau-700 hover:text-white"
            >
                Berikutnya
                <x-ikon nama="chevron-kanan" class="h-4 w-4" />
            </a>
        @else
            <span class="inline-flex h-10 items-center gap-1.5 rounded-full border-2 border-hijau-100 px-4 text-sm font-semibold text-tinta-lembut/60">
                Berikutnya
                <x-ikon nama="chevron-kanan" class="h-4 w-4" />
            </span>
        @endif
    </nav>
@endif
