@extends('layouts.app')

@section('title', 'Informasi PPDB '.config('ppdb.tahun_ajaran').' — '.config('sekolah.nama'))
@section('description', 'Jadwal, syarat, kuota, dan alur pendaftaran siswa baru '.config('sekolah.nama').' tahun ajaran '.config('ppdb.tahun_ajaran').'.')

@section('content')
    @include('partials.kepala-halaman', [
        'rintisan' => 'Tahun ajaran '.config('ppdb.tahun_ajaran'),
        'judul' => 'Penerimaan Peserta <span class="text-kuning-400">Didik Baru</span>',
        'teks' => 'Semua informasi yang perlu Anda ketahui sebelum mendaftarkan anak, mulai dari syarat berkas sampai alur seleksinya.',
        'jejak' => ['Informasi PPDB' => null],
    ])

    <section class="bg-krem pb-16 sm:pb-24">
        <div class="mx-auto w-full max-w-7xl px-5 sm:px-6 lg:px-10">
            @if (session('info'))
                <div class="mb-10 flex items-start gap-3 rounded-[1.5rem] bg-kuning-100 p-5 text-sm text-tinta">
                    <x-ikon nama="info" class="mt-0.5 h-5 w-5 shrink-0 text-kuning-500" />
                    {{ session('info') }}
                </div>
            @endif

            {{-- Status & jadwal --}}
            <div class="grid gap-6 sm:grid-cols-3">
                <div class="rounded-[2rem] bg-white p-7 bayang-lembut">
                    <p class="teks-rintisan text-[10px] text-tinta-lembut">Status</p>
                    <p class="mt-2 font-display text-2xl {{ config('ppdb.buka') ? 'text-hijau-700' : 'text-koral-600' }}">
                        {{ config('ppdb.buka') ? 'Dibuka' : 'Ditutup' }}
                    </p>
                </div>

                <div class="rounded-[2rem] bg-white p-7 bayang-lembut">
                    <p class="teks-rintisan text-[10px] text-tinta-lembut">Mulai</p>
                    <p class="mt-2 font-display text-2xl text-tinta">
                        {{ tanggal_id(\Illuminate\Support\Carbon::parse(config('ppdb.dibuka_pada'))) }}
                    </p>
                </div>

                <div class="rounded-[2rem] bg-white p-7 bayang-lembut">
                    <p class="teks-rintisan text-[10px] text-tinta-lembut">Berakhir</p>
                    <p class="mt-2 font-display text-2xl text-tinta">
                        {{ tanggal_id(\Illuminate\Support\Carbon::parse(config('ppdb.ditutup_pada'))) }}
                    </p>
                </div>
            </div>

            {{-- Kuota per jenjang --}}
            <div class="mt-16">
                <x-judul rintisan="Pilihan jenjang" rata="kiri">
                    Jenjang & <span class="text-koral-500">kuota</span> yang tersedia
                </x-judul>

                <div class="mt-8 overflow-hidden rounded-[2rem] bg-white bayang-lembut">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-hijau-100 text-tinta">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-semibold">Jenjang</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Usia</th>
                                <th scope="col" class="px-6 py-4 font-semibold">Kuota</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hijau-100">
                            @foreach ($jenjang as $item)
                                <tr>
                                    <td class="px-6 py-4 font-semibold text-tinta">{{ $item['label'] }}</td>
                                    <td class="px-6 py-4 text-tinta-lembut">{{ $item['usia'] }}</td>
                                    <td class="px-6 py-4 text-tinta-lembut">{{ $item['kuota'] }} anak</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Alur --}}
            <div class="mt-16">
                <x-judul rintisan="Alur pendaftaran" rata="kiri">
                    Empat langkah, <span class="text-koral-500">tanpa antre</span>
                </x-judul>

                <ol class="mt-8 grid gap-6 lg:grid-cols-4">
                    @foreach ($alur as $i => $langkah)
                        <li class="relative rounded-[2rem] bg-white p-7 bayang-lembut">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-hijau-700 font-display text-lg text-white">
                                {{ $i + 1 }}
                            </span>
                            <h3 class="mt-5 font-display text-lg text-tinta">{{ $langkah['judul'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-tinta-lembut">{{ $langkah['deskripsi'] }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>

            {{-- Syarat --}}
            <div class="mt-16 grid gap-10 lg:grid-cols-2 lg:items-center">
                <div>
                    <x-judul rintisan="Berkas yang disiapkan" rata="kiri">
                        Siapkan dokumen <span class="text-koral-500">berikut ini</span>
                    </x-judul>

                    <ul class="mt-8 grid gap-3">
                        @foreach ($syarat as $item)
                            <li class="flex items-start gap-3 rounded-[1.5rem] bg-white p-5 text-sm text-tinta bayang-lembut">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-hijau-100 text-hijau-700">
                                    <x-ikon nama="centang" class="h-3.5 w-3.5" />
                                </span>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-5 text-xs leading-relaxed text-tinta-lembut">
                        Berkas cukup difoto dengan ponsel asal terbaca jelas. Yang wajib diunggah
                        saat mengisi formulir hanya Kartu Keluarga dan Akta Kelahiran; sisanya
                        dibawa saat verifikasi di sekolah.
                    </p>
                </div>

                <div class="rounded-[2.5rem] bg-hijau-700 p-8 text-center sm:p-12">
                    <h3 class="font-display text-2xl text-white">Sudah siap mendaftar?</h3>
                    <p class="mx-auto mt-3 max-w-sm text-sm leading-relaxed text-hijau-100">
                        Pengisian formulir memakan waktu sekitar 10 menit. Anda akan langsung
                        menerima nomor pendaftaran setelah formulir terkirim.
                    </p>

                    <div class="mt-8 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
                        <x-tombol :href="route('ppdb.create')" warna="koral">
                            Isi Formulir
                        </x-tombol>

                        <x-tombol
                            :href="wa_link('Assalamu\'alaikum, saya ingin bertanya tentang PPDB '.config('ppdb.tahun_ajaran'))"
                            warna="garis-putih"
                            ikon="chat"
                            :luar="true"
                        >
                            Tanya Panitia
                        </x-tombol>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
