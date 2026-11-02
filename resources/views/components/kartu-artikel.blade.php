@props([
    'artikel',
    'urutan' => 0,
])

@php
    $latar = ['bg-hijau-200', 'bg-kuning-300', 'bg-langit-200'][$urutan % 3];
@endphp

<article class="flex flex-col overflow-hidden rounded-[2.5rem] bg-white bayang-lembut transition-transform duration-300 hover:-translate-y-1">
    <a href="{{ route('blog.show', $artikel->slug) }}" class="block aspect-[16/10] overflow-hidden {{ $latar }}">
        @if ($artikel->gambar_url)
            <img
                src="{{ $artikel->gambar_url }}"
                alt="{{ $artikel->judul }}"
                class="h-full w-full object-cover"
                loading="lazy"
            >
        @else
            {{-- Belum ada gambar sampul: blok warna dengan logo sekolah. --}}
            <span class="flex h-full w-full items-center justify-center">
                <img
                    src="{{ logo_url() }}"
                    alt=""
                    class="h-12 w-12 opacity-40"
                    loading="lazy"
                >
            </span>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-7">
        <div class="flex items-center gap-2">
            @if ($artikel->kategori)
                <span class="teks-rintisan text-[10px] text-koral-500">{{ $artikel->kategori }}</span>
                <span class="h-1 w-1 rounded-full bg-hijau-200" aria-hidden="true"></span>
            @endif
            <span class="text-[11px] text-tinta-lembut">{{ tanggal_id($artikel->terbit_pada) }}</span>
        </div>

        <h3 class="mt-2 font-display text-lg leading-snug text-tinta">
            <a href="{{ route('blog.show', $artikel->slug) }}" class="hover:text-hijau-700">
                {{ $artikel->judul }}
            </a>
        </h3>

        <p class="mt-2 flex-1 text-sm leading-relaxed text-tinta-lembut">{{ $artikel->ringkasan }}</p>

        <a
            href="{{ route('blog.show', $artikel->slug) }}"
            class="mt-5 inline-flex text-sm font-semibold text-hijau-700 underline-offset-4 hover:underline"
        >
            Baca selengkapnya
        </a>
    </div>
</article>
