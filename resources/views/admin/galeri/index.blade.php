@extends('layouts.admin')

@section('title', 'Galeri Foto')

@section('content')
    {{-- Unggah --}}
    <form
        method="POST"
        action="{{ route('admin.galeri.store') }}"
        enctype="multipart/form-data"
        class="rounded-[1.75rem] bg-white p-6 bayang-lembut"
    >
        @csrf

        <h2 class="font-display text-lg text-tinta">Tambah foto</h2>
        <p class="mt-1 text-sm text-tinta-lembut">
            Bisa pilih beberapa foto sekaligus. Foto besar dari ponsel otomatis
            diperkecil supaya halaman tetap ringan dibuka orang tua.
        </p>

        <div class="mt-5 grid gap-4 sm:grid-cols-[1fr_auto] sm:items-end">
            <div>
                <label for="foto" class="block text-xs font-semibold text-tinta">
                    Pilih foto (JPG, PNG, atau WEBP &middot; maksimal 8 MB per foto)
                </label>
                <input
                    id="foto"
                    name="foto[]"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    multiple
                    required
                    class="mt-2 block w-full rounded-2xl border-2 border-dashed border-hijau-200 bg-hijau-50/50 px-4 py-3 text-sm text-tinta file:mr-4 file:rounded-full file:border-0 file:bg-hijau-700 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-hijau-800"
                >
            </div>

            <x-tombol type="submit" warna="koral" ikon="foto">Unggah</x-tombol>
        </div>

        @error('foto')
            <p class="mt-3 text-sm font-semibold text-koral-600">{{ $message }}</p>
        @enderror
        @error('foto.*')
            <p class="mt-3 text-sm font-semibold text-koral-600">{{ $message }}</p>
        @enderror
    </form>

    {{-- Daftar --}}
    @if ($daftar->isEmpty())
        <div class="mt-6 rounded-[1.75rem] bg-white p-12 text-center bayang-lembut">
            <p class="text-sm text-tinta-lembut">
                Belum ada foto. Foto yang diunggah di sini langsung tampil di bagian
                Keseharian pada halaman depan.
            </p>
        </div>
    @else
        <p class="mt-8 text-sm text-tinta-lembut">
            {{ $daftar->count() }} foto &middot;
            {{ $daftar->where('tampil', true)->count() }} tampil di beranda.
            Urutan di bawah ini sama dengan urutan di halaman depan.
        </p>

        <div class="mt-4 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($daftar as $i => $foto)
                <div class="overflow-hidden rounded-[1.75rem] bg-white bayang-lembut">
                    <div class="relative aspect-[4/3] bg-hijau-100">
                        <img
                            src="{{ $foto->gambar_url }}"
                            alt="{{ $foto->alt }}"
                            class="h-full w-full object-cover {{ $foto->tampil ? '' : 'opacity-40 grayscale' }}"
                            loading="lazy"
                        >

                        @unless ($foto->tampil)
                            <span class="absolute left-3 top-3 rounded-full bg-tinta/80 px-3 py-1 text-[11px] font-semibold text-white">
                                Disembunyikan
                            </span>
                        @endunless

                        {{-- Tombol geser dibuat form sendiri-sendiri: form tidak
                             boleh bersarang di dalam form judul di bawahnya. --}}
                        <div class="absolute right-3 top-3 flex gap-1.5">
                            @if ($i > 0)
                                <form method="POST" action="{{ route('admin.galeri.geser', $foto) }}">
                                    @csrf
                                    <input type="hidden" name="arah" value="naik">
                                    <button
                                        type="submit"
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-tinta transition-colors hover:bg-white"
                                    >
                                        <x-ikon nama="chevron-bawah" class="h-4 w-4 rotate-180" />
                                        <span class="sr-only">Geser ke depan</span>
                                    </button>
                                </form>
                            @endif

                            @if ($i < $daftar->count() - 1)
                                <form method="POST" action="{{ route('admin.galeri.geser', $foto) }}">
                                    @csrf
                                    <input type="hidden" name="arah" value="turun">
                                    <button
                                        type="submit"
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-tinta transition-colors hover:bg-white"
                                    >
                                        <x-ikon nama="chevron-bawah" class="h-4 w-4" />
                                        <span class="sr-only">Geser ke belakang</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.galeri.update', $foto) }}" class="p-5">
                        @csrf
                        @method('PUT')

                        <label for="judul-{{ $foto->id }}" class="block text-xs font-semibold text-tinta">
                            Judul foto
                        </label>
                        <input
                            id="judul-{{ $foto->id }}"
                            name="judul"
                            type="text"
                            value="{{ old('judul', $foto->judul) }}"
                            placeholder="Misal: Kegiatan menulis di kelas"
                            maxlength="160"
                            class="mt-2 block w-full rounded-2xl border-2 border-hijau-100 bg-white px-4 py-2.5 text-sm text-tinta focus:border-hijau-400 focus:outline-none"
                        >

                        <label class="mt-3 flex items-center gap-2 text-sm text-tinta">
                            <input
                                type="checkbox"
                                name="tampil"
                                value="1"
                                @checked($foto->tampil)
                                class="h-4 w-4 rounded border-hijau-200 accent-koral-500"
                            >
                            Tampilkan di halaman depan
                        </label>

                        <x-tombol type="submit" warna="hijau" ukuran="sm" ikon="centang" class="mt-4">
                            Simpan
                        </x-tombol>
                    </form>

                    <form
                        method="POST"
                        action="{{ route('admin.galeri.destroy', $foto) }}"
                        class="border-t border-hijau-100 px-5 py-3"
                        onsubmit="return confirm('Hapus foto ini? Berkasnya ikut terhapus dari server.')"
                    >
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-semibold text-koral-600 hover:underline">
                            <x-ikon nama="sampah" class="h-4 w-4" />
                            Hapus foto
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
@endsection
