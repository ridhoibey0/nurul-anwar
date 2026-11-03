@extends('layouts.admin')

@section('title', 'Ringkasan')

@section('content')
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-[1.75rem] bg-hijau-700 p-6 text-white">
            <p class="text-xs text-hijau-200">Total pendaftar</p>
            <p class="mt-2 font-display text-4xl">{{ $total }}</p>
            <p class="mt-1 text-xs text-hijau-200">Tahun ajaran {{ $tahunAjaran }}</p>
        </div>

        @foreach ($daftarStatus as $status)
            <div class="rounded-[1.75rem] bg-white p-6 bayang-lembut">
                <x-lencana-status :status="$status" />
                <p class="mt-3 font-display text-3xl text-tinta">{{ $perStatus[$status->kode] ?? 0 }}</p>
                <a
                    href="{{ route('admin.pendaftaran.index', ['status' => $status->kode]) }}"
                    class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-hijau-700 hover:gap-2"
                >
                    Lihat data
                    <x-ikon nama="panah-kanan" class="h-3.5 w-3.5" />
                </a>
            </div>
        @endforeach
    </div>

    {{-- Saklar buka/tutup pendaftaran --}}
    <div class="mt-6 flex flex-wrap items-center gap-4 rounded-[1.75rem] border-2 p-6 {{ config('ppdb.buka') ? 'border-hijau-100 bg-white' : 'border-koral-100 bg-koral-100/40' }}">
        <div class="min-w-0 flex-1">
            <p class="font-display text-base text-tinta">
                Pendaftaran {{ $tahunAjaran }} {{ config('ppdb.buka') ? 'sedang dibuka' : 'sedang ditutup' }}
            </p>
            <p class="mt-1 text-sm text-tinta-lembut">
                {{ config('ppdb.buka')
                    ? 'Formulir online aktif dan menerima pendaftaran baru.'
                    : 'Formulir online menolak pengiriman baru. Halaman cek status tetap bisa dipakai orang tua.' }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <form method="POST" action="{{ route('admin.pengaturan.toggle') }}">
                @csrf
                <x-tombol
                    type="submit"
                    :warna="config('ppdb.buka') ? 'garis' : 'koral'"
                    ukuran="sm"
                    :ikon="config('ppdb.buka') ? 'tutup' : 'centang'"
                >
                    {{ config('ppdb.buka') ? 'Tutup pendaftaran' : 'Buka pendaftaran' }}
                </x-tombol>
            </form>

            <x-tombol :href="route('admin.pengaturan.edit')" warna="putih" ukuran="sm" ikon="roda-gigi">
                Pengaturan
            </x-tombol>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_1.4fr]">
        {{-- Sebaran jenjang --}}
        <section class="rounded-[1.75rem] bg-white p-7 bayang-lembut">
            <h2 class="font-display text-lg text-tinta">Sebaran per jenjang</h2>

            <ul class="mt-6 grid gap-4">
                @foreach ($daftarJenjang as $item)
                    @php
                        $jumlah = $perJenjang[$item->kode] ?? 0;
                        $kuota = max(1, $item->kuota);
                        $persen = min(100, round($jumlah / $kuota * 100));
                    @endphp

                    <li>
                        <div class="flex items-baseline justify-between gap-3 text-sm">
                            <span class="font-semibold text-tinta">{{ $item->nama }}</span>
                            <span class="shrink-0 text-xs text-tinta-lembut">{{ $jumlah }} / {{ $item->kuota }}</span>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-hijau-100">
                            <div
                                class="h-full rounded-full {{ $persen >= 100 ? 'bg-koral-500' : 'bg-hijau-500' }}"
                                style="width: {{ $persen }}%"
                            ></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- Pendaftar terbaru --}}
        <section class="rounded-[1.75rem] bg-white p-7 bayang-lembut">
            <div class="flex items-center justify-between gap-4">
                <h2 class="font-display text-lg text-tinta">Pendaftar terbaru</h2>
                <a href="{{ route('admin.pendaftaran.index') }}" class="text-xs font-semibold text-hijau-700 hover:underline">
                    Lihat semua
                </a>
            </div>

            @if ($terbaru->isEmpty())
                <p class="mt-6 rounded-2xl bg-krem p-6 text-center text-sm text-tinta-lembut">
                    Belum ada pendaftar untuk tahun ajaran ini.
                </p>
            @else
                <ul class="mt-5 divide-y divide-hijau-100">
                    @foreach ($terbaru as $item)
                        <li class="flex items-center justify-between gap-4 py-3.5">
                            <div class="min-w-0">
                                <a href="{{ route('admin.pendaftaran.show', $item) }}" class="truncate text-sm font-semibold text-tinta hover:text-hijau-700">
                                    {{ $item->nama_lengkap }}
                                </a>
                                <p class="mt-0.5 truncate text-xs text-tinta-lembut">
                                    {{ $item->nomor_pendaftaran }} &middot; {{ $item->jenjang_label }}
                                </p>
                            </div>

                            <div class="flex shrink-0 flex-col items-end gap-1">
                                <x-lencana-status :status="$item->status" />
                                <span class="text-[11px] text-tinta-lembut">{{ tanggal_id($item->created_at) }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
