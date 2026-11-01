@extends('layouts.admin')

@section('title', 'Pengaturan PPDB')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1.6fr_1fr] lg:items-start">
        <form method="POST" action="{{ route('admin.pengaturan.update') }}" class="grid gap-6">
            @csrf
            @method('PATCH')

            {{-- Status pendaftaran --}}
            <section class="rounded-[1.75rem] bg-white p-7 bayang-lembut">
                <h2 class="font-display text-lg text-tinta">Status pendaftaran</h2>
                <p class="mt-1 text-sm text-tinta-lembut">
                    Saat ditutup, tombol pendaftaran di website berubah menjadi informasi dan formulir
                    online menolak pengiriman baru. Halaman cek status tetap bisa dipakai orang tua.
                </p>

                <div
                    x-data="{ buka: '{{ old('buka', $buka ? '1' : '0') }}' }"
                    class="mt-5 grid gap-3 sm:grid-cols-2"
                >
                    <label
                        class="flex cursor-pointer items-start gap-3 rounded-2xl border-2 p-4 transition-colors"
                        :class="buka === '1' ? 'border-hijau-700 bg-hijau-50' : 'border-hijau-100 hover:border-hijau-200'"
                    >
                        <input
                            type="radio"
                            name="buka"
                            value="1"
                            x-model="buka"
                            @checked(old('buka', $buka ? '1' : '0') === '1')
                            class="mt-0.5 h-4 w-4 accent-hijau-700"
                        >
                        <span>
                            <span class="block text-sm font-semibold text-tinta">Dibuka</span>
                            <span class="mt-0.5 block text-xs text-tinta-lembut">
                                Orang tua bisa mengisi formulir pendaftaran online.
                            </span>
                        </span>
                    </label>

                    <label
                        class="flex cursor-pointer items-start gap-3 rounded-2xl border-2 p-4 transition-colors"
                        :class="buka === '0' ? 'border-koral-500 bg-koral-100/50' : 'border-hijau-100 hover:border-hijau-200'"
                    >
                        <input
                            type="radio"
                            name="buka"
                            value="0"
                            x-model="buka"
                            @checked(old('buka', $buka ? '1' : '0') === '0')
                            class="mt-0.5 h-4 w-4 accent-koral-500"
                        >
                        <span>
                            <span class="block text-sm font-semibold text-tinta">Ditutup</span>
                            <span class="mt-0.5 block text-xs text-tinta-lembut">
                                Formulir dikunci, misalnya saat kuota sudah penuh.
                            </span>
                        </span>
                    </label>
                </div>

                @error('buka')
                    <p class="mt-2 flex items-start gap-1.5 text-xs text-koral-600">
                        <x-ikon nama="peringatan" class="mt-px h-3.5 w-3.5 shrink-0" />
                        {{ $message }}
                    </p>
                @enderror
            </section>

            {{-- Tahun ajaran & jadwal --}}
            <section class="rounded-[1.75rem] bg-white p-7 bayang-lembut">
                <h2 class="font-display text-lg text-tinta">Tahun ajaran &amp; jadwal</h2>
                <p class="mt-1 text-sm text-tinta-lembut">
                    Tahun ajaran dipakai di seluruh website, menjadi bagian nomor pendaftaran, dan
                    menentukan data mana yang tampil di halaman Ringkasan.
                </p>

                <div class="mt-5 grid gap-5">
                    <x-form.input
                        nama="tahun_ajaran"
                        label="Tahun ajaran"
                        :nilai="$tahunAjaran"
                        bantuan="Format 2027/2028 — dua tahun berurutan."
                        placeholder="2027/2028"
                        wajib
                        inputmode="numeric"
                        autocomplete="off"
                    />

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-form.input
                            nama="dibuka_pada"
                            label="Tanggal dibuka"
                            tipe="date"
                            :nilai="$dibukaPada"
                            wajib
                        />

                        <x-form.input
                            nama="ditutup_pada"
                            label="Tanggal ditutup"
                            tipe="date"
                            :nilai="$ditutupPada"
                            wajib
                        />
                    </div>
                </div>

                <p class="mt-4 text-xs text-tinta-lembut">
                    Kedua tanggal hanya ditampilkan sebagai jadwal di halaman informasi PPDB.
                    Yang menentukan formulir bisa diisi atau tidak adalah pilihan status di atas.
                </p>
            </section>

            <div class="flex flex-wrap items-center gap-3">
                <x-tombol type="submit" warna="koral" ikon="centang">
                    Simpan pengaturan
                </x-tombol>

                <x-tombol :href="route('ppdb.info')" warna="garis" :luar="true">
                    Lihat halaman PPDB
                </x-tombol>
            </div>
        </form>

        {{-- Panel samping --}}
        <div class="grid gap-6">
            <section class="rounded-[1.75rem] bg-hijau-900 p-7 text-white">
                <p class="text-xs text-hijau-200">Berlaku sekarang</p>

                <p class="mt-2 font-display text-3xl">{{ $tahunAjaran }}</p>

                <p class="mt-3 inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $buka ? 'bg-white/15 text-white' : 'bg-koral-500 text-white' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $buka ? 'bg-kuning-300' : 'bg-white' }}"></span>
                    {{ $buka ? 'Pendaftaran dibuka' : 'Pendaftaran ditutup' }}
                </p>

                <dl class="mt-6 grid gap-3 border-t border-white/10 pt-5 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-hijau-200">Dibuka</dt>
                        <dd class="font-semibold">{{ tanggal_id(\Illuminate\Support\Carbon::parse($dibukaPada)) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-hijau-200">Ditutup</dt>
                        <dd class="font-semibold">{{ tanggal_id(\Illuminate\Support\Carbon::parse($ditutupPada)) }}</dd>
                    </div>
                </dl>

                <form method="POST" action="{{ route('admin.pengaturan.toggle') }}" class="mt-6">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold transition-colors {{ $buka ? 'bg-koral-500 text-white hover:bg-koral-600' : 'bg-kuning-400 text-tinta hover:bg-kuning-500' }}"
                    >
                        <x-ikon :nama="$buka ? 'tutup' : 'centang'" class="h-4 w-4" />
                        {{ $buka ? 'Tutup pendaftaran sekarang' : 'Buka pendaftaran sekarang' }}
                    </button>
                </form>

                <p class="mt-2 text-xs text-hijau-200">
                    Saklar cepat ini hanya mengubah status, tanpa menyentuh tahun ajaran dan jadwal.
                </p>
            </section>

            <section class="rounded-[1.75rem] bg-white p-7 bayang-lembut">
                <h2 class="font-display text-base text-tinta">Pendaftar per tahun ajaran</h2>

                @if ($perTahun->isEmpty())
                    <p class="mt-3 text-sm text-tinta-lembut">Belum ada data pendaftar.</p>
                @else
                    <ul class="mt-4 grid gap-2">
                        @foreach ($perTahun as $tahun => $jumlah)
                            <li class="flex items-center justify-between gap-3 rounded-2xl bg-krem px-4 py-2.5 text-sm">
                                <span class="font-semibold text-tinta">
                                    {{ $tahun }}
                                    @if ($tahun === $tahunAjaran)
                                        <span class="ml-1 rounded-full bg-hijau-100 px-2 py-0.5 text-[0.65rem] font-semibold text-hijau-700">aktif</span>
                                    @endif
                                </span>
                                <a
                                    href="{{ route('admin.pendaftaran.index', ['tahun_ajaran' => $tahun]) }}"
                                    class="text-hijau-700 hover:underline"
                                >
                                    {{ $jumlah }} pendaftar
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <p class="mt-4 text-xs text-tinta-lembut">
                    Mengganti tahun ajaran tidak menghapus data lama. Pendaftar tahun sebelumnya tetap
                    tersimpan dan bisa dibuka lewat filter tahun di menu Data Pendaftar.
                </p>
            </section>
        </div>
    </div>
@endsection
