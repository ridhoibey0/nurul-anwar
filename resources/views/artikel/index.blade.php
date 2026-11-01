@extends('layouts.app')

@section('title', 'Blog — '.config('sekolah.nama'))
@section('description', 'Catatan kegiatan dan bacaan ringan untuk orang tua dari '.config('sekolah.nama').'.')

@section('content')
    @include('partials.kepala-halaman', [
        'rintisan' => 'Dari blog',
        'judul' => 'Bacaan ringan untuk <span class="text-kuning-300">orang tua</span>',
        'teks' => 'Catatan kegiatan sekolah, tips mendampingi anak, dan kabar dari ruang kelas.',
        'jejak' => ['Blog' => null],
    ])

    <section class="bg-krem pb-20 sm:pb-28">
        <div class="mx-auto w-full max-w-7xl px-5 sm:px-6 lg:px-10">
            {{-- Pencarian --}}
            <form method="GET" action="{{ route('blog.index') }}" class="mx-auto -mt-4 flex max-w-xl gap-3">
                <label for="q" class="sr-only">Cari artikel</label>
                <input
                    id="q"
                    name="q"
                    type="search"
                    value="{{ $kata }}"
                    placeholder="Cari judul atau kategori"
                    class="block w-full rounded-full border-2 border-hijau-100 bg-white px-5 py-3 text-sm text-tinta placeholder:text-tinta-lembut/60 focus:border-hijau-400 focus:outline-none"
                >
                <x-tombol type="submit" warna="hijau" ikon="cari" class="shrink-0">Cari</x-tombol>
            </form>

            @if ($daftar->isEmpty())
                <div class="mx-auto mt-14 max-w-lg rounded-[2.5rem] bg-white p-10 text-center bayang-lembut">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-hijau-100 text-hijau-700">
                        <x-ikon nama="berkas" class="h-6 w-6" />
                    </span>

                    <p class="mt-5 font-display text-xl text-tinta">
                        {{ filled($kata) ? 'Tidak ada artikel yang cocok' : 'Belum ada artikel' }}
                    </p>

                    <p class="mt-2 text-sm text-tinta-lembut">
                        {{ filled($kata)
                            ? 'Coba kata kunci lain, atau lihat seluruh artikel.'
                            : 'Tulisan pertama dari sekolah akan tampil di halaman ini.' }}
                    </p>

                    @if (filled($kata))
                        <x-tombol :href="route('blog.index')" warna="garis" ukuran="sm" class="mt-6">
                            Lihat semua artikel
                        </x-tombol>
                    @endif
                </div>
            @else
                <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($daftar as $i => $tulisan)
                        <x-kartu-artikel :artikel="$tulisan" :urutan="$i" />
                    @endforeach
                </div>

                @if ($daftar->hasPages())
                    <div class="mt-12">
                        {{ $daftar->links() }}
                    </div>
                @endif
            @endif
        </div>
    </section>
@endsection
