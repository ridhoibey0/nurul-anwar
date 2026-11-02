@extends('layouts.app')

@section('title', $artikel->judul.' — '.config('sekolah.nama'))
@section('description', $artikel->ringkasan)
@section('og_type', 'article')

@if ($artikel->gambar_url)
    @section('og_image', $artikel->gambar_url)
    @section('og_image_alt', $artikel->judul)
@endif

@section('kepala')
    @php
        /* Data terstruktur artikel: judul, tanggal, dan penerbit. */
        $skema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $artikel->judul,
            'description' => $artikel->ringkasan,
            'image' => $artikel->gambar_url ?: gambar_bagikan_url(),
            'datePublished' => $artikel->terbit_pada?->toAtomString(),
            'dateModified' => $artikel->updated_at?->toAtomString(),
            'author' => [
                '@type' => 'Organization',
                'name' => config('sekolah.nama'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('sekolah.nama'),
            ],
            'mainEntityOfPage' => route('blog.show', $artikel->slug),
        ]);
    @endphp

    <script type="application/ld+json">{!! json_encode($skema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@section('content')
    @include('partials.kepala-halaman', [
        'rintisan' => $artikel->kategori ?: 'Dari blog',
        'judul' => e($artikel->judul),
        'jejak' => ['Blog' => route('blog.index'), \Illuminate\Support\Str::limit($artikel->judul, 40) => null],
    ])

    <section class="bg-krem pb-20 sm:pb-28">
        <div class="mx-auto w-full max-w-3xl px-5 sm:px-6">
            <article class="-mt-4 overflow-hidden rounded-[2.5rem] bg-white bayang-lembut">
                @if ($artikel->gambar_url)
                    <img
                        src="{{ $artikel->gambar_url }}"
                        alt="{{ $artikel->judul }}"
                        class="aspect-[16/9] w-full object-cover"
                    >
                @endif

                <div class="p-7 sm:p-12">
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 border-b border-hijau-100 pb-6 text-xs text-tinta-lembut">
                        <span>{{ tanggal_id($artikel->terbit_pada) }}</span>
                        <span class="h-1 w-1 rounded-full bg-hijau-200" aria-hidden="true"></span>
                        <span>{{ $artikel->waktu_baca }} menit baca</span>

                        @if ($artikel->penulis)
                            <span class="h-1 w-1 rounded-full bg-hijau-200" aria-hidden="true"></span>
                            <span>Ditulis {{ $artikel->penulis->name }}</span>
                        @endif
                    </div>

                    <p class="mt-6 font-display text-lg leading-relaxed text-tinta">{{ $artikel->ringkasan }}</p>

                    <div class="prose-artikel mt-6 text-sm sm:text-base">
                        {!! $artikel->isi_html !!}
                    </div>

                    <div class="mt-10 flex flex-wrap items-center gap-3 border-t border-hijau-100 pt-8">
                        <x-tombol
                            :href="wa_link('Assalamu\'alaikum, saya ingin bertanya soal artikel \''.$artikel->judul.'\'.')"
                            warna="koral"
                            ukuran="sm"
                            ikon="chat"
                            :luar="true"
                        >
                            Tanya lewat WhatsApp
                        </x-tombol>

                        <x-tombol :href="route('blog.index')" warna="garis" ukuran="sm">
                            Artikel lainnya
                        </x-tombol>
                    </div>
                </div>
            </article>
        </div>

        @if ($lainnya->isNotEmpty())
            <div class="mx-auto mt-16 w-full max-w-7xl px-5 sm:px-6 lg:px-10">
                <h2 class="text-center font-display text-2xl text-tinta">Baca juga</h2>

                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($lainnya as $i => $tulisan)
                        <x-kartu-artikel :artikel="$tulisan" :urutan="$i" />
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endsection
