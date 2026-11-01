@extends('layouts.admin')

@section('title', 'Data Pendaftar')

@section('content')
    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.pendaftaran.index') }}" class="rounded-[1.75rem] bg-white p-6 bayang-lembut">
        <div class="grid gap-4 lg:grid-cols-[1.4fr_1fr_1fr_1fr_auto] lg:items-end">
            <div>
                <label for="q" class="block text-xs font-semibold text-tinta">Cari</label>
                <input
                    id="q"
                    name="q"
                    type="search"
                    value="{{ $filter['q'] ?? '' }}"
                    placeholder="Nomor, nama anak, nama orang tua, atau WhatsApp"
                    class="mt-2 block w-full rounded-2xl border-2 border-hijau-100 bg-white px-4 py-2.5 text-sm text-tinta focus:border-hijau-400 focus:outline-none"
                >
            </div>

            <div>
                <label for="status" class="block text-xs font-semibold text-tinta">Status</label>
                <select id="status" name="status" class="mt-2 block w-full rounded-2xl border-2 border-hijau-100 bg-white px-4 py-2.5 text-sm text-tinta focus:border-hijau-400 focus:outline-none">
                    <option value="">Semua status</option>
                    @foreach (config('ppdb.status') as $kunci => $status)
                        <option value="{{ $kunci }}" @selected(($filter['status'] ?? '') === $kunci)>{{ $status['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="jenjang" class="block text-xs font-semibold text-tinta">Jenjang</label>
                <select id="jenjang" name="jenjang" class="mt-2 block w-full rounded-2xl border-2 border-hijau-100 bg-white px-4 py-2.5 text-sm text-tinta focus:border-hijau-400 focus:outline-none">
                    <option value="">Semua jenjang</option>
                    @foreach (config('ppdb.jenjang') as $kunci => $item)
                        <option value="{{ $kunci }}" @selected(($filter['jenjang'] ?? '') === $kunci)>{{ $item['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="tahun_ajaran" class="block text-xs font-semibold text-tinta">Tahun ajaran</label>
                <select id="tahun_ajaran" name="tahun_ajaran" class="mt-2 block w-full rounded-2xl border-2 border-hijau-100 bg-white px-4 py-2.5 text-sm text-tinta focus:border-hijau-400 focus:outline-none">
                    <option value="">Semua tahun</option>
                    @foreach ($tahunTersedia as $tahun)
                        <option value="{{ $tahun }}" @selected(($filter['tahun_ajaran'] ?? '') === $tahun)>{{ $tahun }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <x-tombol type="submit" warna="hijau" ukuran="sm" ikon="cari">Filter</x-tombol>

                <x-tombol
                    :href="route('admin.pendaftaran.export', request()->query())"
                    warna="garis"
                    ukuran="sm"
                    ikon="unduh"
                >
                    CSV
                </x-tombol>
            </div>
        </div>

        @if (array_filter($filter))
            <a href="{{ route('admin.pendaftaran.index') }}" class="mt-4 inline-block text-xs font-semibold text-koral-600 hover:underline">
                Hapus filter
            </a>
        @endif
    </form>

    {{-- Tabel --}}
    <div class="mt-6 overflow-hidden rounded-[1.75rem] bg-white bayang-lembut">
        @if ($daftar->isEmpty())
            <p class="p-12 text-center text-sm text-tinta-lembut">
                Tidak ada data pendaftar yang cocok dengan filter ini.
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] text-left text-sm">
                    <thead class="bg-hijau-100 text-tinta">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold">Nomor</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Nama Anak</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Jenjang</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Orang Tua</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Masuk</th>
                            <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                            <th scope="col" class="px-6 py-4"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-hijau-100">
                        @foreach ($daftar as $item)
                            <tr class="transition-colors hover:bg-krem">
                                <td class="whitespace-nowrap px-6 py-4 font-mono text-xs text-tinta-lembut">
                                    {{ $item->nomor_pendaftaran }}
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-tinta">{{ $item->nama_lengkap }}</p>
                                    <p class="text-xs text-tinta-lembut">{{ $item->jenis_kelamin_label }} &middot; {{ $item->usia }}</p>
                                </td>
                                <td class="px-6 py-4 text-tinta-lembut">{{ $item->jenjang_label }}</td>
                                <td class="px-6 py-4">
                                    <p class="text-tinta">{{ $item->nama_ayah }}</p>
                                    <p class="text-xs text-tinta-lembut">{{ $item->whatsapp }}</p>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-xs text-tinta-lembut">
                                    {{ tanggal_id($item->created_at) }}
                                </td>
                                <td class="px-6 py-4">
                                    <x-lencana-status :status="$item->status" />
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <a
                                        href="{{ route('admin.pendaftaran.show', $item) }}"
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-hijau-700 hover:gap-2"
                                    >
                                        Detail
                                        <x-ikon nama="panah-kanan" class="h-3.5 w-3.5" />
                                    </a>
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
