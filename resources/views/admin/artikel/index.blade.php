@extends('layouts.admin')

@section('title', 'Blog')

@section('content')
    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.artikel.index') }}" class="rounded-[1.75rem] bg-white p-6 bayang-lembut">
        <div class="grid gap-4 lg:grid-cols-[2fr_1fr_auto] lg:items-end">
            <div>
                <label for="q" class="block text-xs font-semibold text-tinta">Cari</label>
                <input
                    id="q"
                    name="q"
                    type="search"
                    value="{{ $filter['q'] ?? '' }}"
                    placeholder="Judul, kategori, atau ringkasan"
                    class="mt-2 block w-full rounded-2xl border-2 border-hijau-100 bg-white px-4 py-2.5 text-sm text-tinta focus:border-hijau-400 focus:outline-none"
                >
            </div>

            <div>
                <label for="status" class="block text-xs font-semibold text-tinta">Status</label>
                <select id="status" name="status" class="mt-2 block w-full rounded-2xl border-2 border-hijau-100 bg-white px-4 py-2.5 text-sm text-tinta focus:border-hijau-400 focus:outline-none">
                    <option value="">Semua status</option>
                    <option value="terbit" @selected(($filter['status'] ?? '') === 'terbit')>Terbit</option>
                    <option value="draf" @selected(($filter['status'] ?? '') === 'draf')>Draf</option>
                </select>
            </div>

            <div class="flex gap-2">
                <x-tombol type="submit" warna="hijau" ukuran="sm" ikon="cari">Filter</x-tombol>

                <x-tombol :href="route('admin.artikel.create')" warna="koral" ukuran="sm" ikon="pensil">
                    Tulis
                </x-tombol>
            </div>
        </div>

        @if (array_filter($filter))
            <a href="{{ route('admin.artikel.index') }}" class="mt-4 inline-block text-xs font-semibold text-koral-600 hover:underline">
                Hapus filter
            </a>
        @endif
    </form>

    {{-- Tabel --}}
    <div class="mt-6 overflow-hidden rounded-[1.75rem] bg-white bayang-lembut">
        @if ($daftar->isEmpty())
            <div class="p-12 text-center">
                <p class="text-sm text-tinta-lembut">
                    {{ array_filter($filter) ? 'Tidak ada artikel yang cocok dengan filter ini.' : 'Belum ada artikel. Tulisan pertama akan tampil di halaman blog setelah diterbitkan.' }}
                </p>

                @unless (array_filter($filter))
                    <x-tombol :href="route('admin.artikel.create')" warna="koral" ukuran="sm" ikon="pensil" class="mt-6">
                        Tulis artikel pertama
                    </x-tombol>
                @endunless
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="bg-hijau-100 text-tinta">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold">Artikel</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Kategori</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Terbit</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                            <th scope="col" class="px-6 py-4"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-hijau-100">
                        @foreach ($daftar as $item)
                            <tr class="transition-colors hover:bg-krem">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-hijau-100">
                                            @if ($item->gambar_url)
                                                <img src="{{ $item->gambar_url }}" alt="" class="h-full w-full object-cover">
                                            @else
                                                <x-ikon nama="berkas" class="h-4 w-4 text-hijau-700" />
                                            @endif
                                        </span>

                                        <div class="min-w-0">
                                            <p class="font-semibold text-tinta">{{ $item->judul }}</p>
                                            <p class="truncate text-xs text-tinta-lembut">{{ \Illuminate\Support\Str::limit($item->ringkasan, 70) }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-tinta-lembut">{{ $item->kategori ?: '—' }}</td>

                                <td class="whitespace-nowrap px-6 py-4 text-xs text-tinta-lembut">
                                    {{ tanggal_id($item->terbit_pada) }}
                                </td>

                                <td class="px-6 py-4">
                                    @php
                                        $gaya = match ($item->status_label) {
                                            'Terbit' => 'bg-hijau-100 text-hijau-700',
                                            'Terjadwal' => 'bg-langit-200 text-hijau-800',
                                            default => 'bg-koral-100 text-koral-600',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $gaya }}">
                                        {{ $item->status_label }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        @if ($item->sudah_terbit)
                                            <a
                                                href="{{ route('blog.show', $item->slug) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="text-xs font-semibold text-tinta-lembut hover:text-hijau-700"
                                            >
                                                Lihat
                                            </a>
                                        @endif

                                        <a
                                            href="{{ route('admin.artikel.edit', $item) }}"
                                            class="inline-flex items-center gap-1 text-xs font-semibold text-hijau-700 hover:gap-2"
                                        >
                                            Ubah
                                            <x-ikon nama="panah-kanan" class="h-3.5 w-3.5" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($daftar->hasPages())
        <div class="mt-6">
            {{ $daftar->links() }}
        </div>
    @endif
@endsection
