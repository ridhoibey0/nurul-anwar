@extends('layouts.app')

@section('title', 'Cek Status Pendaftaran — '.config('sekolah.nama'))
@section('description', 'Cek status pendaftaran siswa baru '.config('sekolah.nama').' dengan nomor pendaftaran dan tanggal lahir anak.')

@section('content')
    @include('partials.kepala-halaman', [
        'rintisan' => 'PPDB '.config('ppdb.tahun_ajaran'),
        'judul' => 'Cek <span class="text-kuning-400">status</span> pendaftaran',
        'teks' => 'Masukkan nomor pendaftaran dan tanggal lahir anak untuk melihat perkembangan terbaru.',
        'jejak' => ['Cek Status' => null],
    ])

    <section class="bg-krem pb-16 sm:pb-24">
        <div class="mx-auto w-full max-w-2xl px-5 sm:px-6 lg:px-10">
            <form method="POST" action="{{ route('ppdb.cek.proses') }}" class="rounded-[2.5rem] bg-white p-6 bayang-lembut sm:p-10">
                @csrf

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-form.input
                        nama="nomor_pendaftaran"
                        label="Nomor pendaftaran"
                        :wajib="true"
                        placeholder="{{ config('ppdb.prefix_nomor') }}-2627-0001"
                        class="uppercase"
                    />

                    <x-form.input
                        nama="tanggal_lahir"
                        label="Tanggal lahir anak"
                        tipe="date"
                        :wajib="true"
                    />
                </div>

                <x-tombol type="submit" warna="hijau" ikon="cari" class="mt-7 w-full sm:w-auto">
                    Cek Status
                </x-tombol>
            </form>

            @if (! empty($tidakDitemukan))
                <div class="mt-8 flex items-start gap-3 rounded-[1.5rem] border-2 border-koral-500 bg-koral-100 p-6">
                    <x-ikon nama="peringatan" class="mt-0.5 h-5 w-5 shrink-0 text-koral-600" />
                    <div class="text-sm text-tinta">
                        <p class="font-semibold text-koral-600">Data tidak ditemukan</p>
                        <p class="mt-1 leading-relaxed">
                            Periksa kembali nomor pendaftaran dan tanggal lahir anak. Bila masih
                            belum ketemu,
                            <a href="{{ wa_link('Assalamu\'alaikum, saya kesulitan mengecek status pendaftaran.') }}" target="_blank" rel="noopener noreferrer" class="font-semibold underline">
                                hubungi panitia lewat WhatsApp</a>.
                        </p>
                    </div>
                </div>
            @endif

            @if ($pendaftaran)
                <div class="mt-8 rounded-[2.5rem] bg-white p-8 bayang-lembut sm:p-10">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="teks-rintisan text-[10px] text-tinta-lembut">Nomor pendaftaran</p>
                            <p class="mt-1 font-display text-2xl text-hijau-700">{{ $pendaftaran->nomor_pendaftaran }}</p>
                        </div>

                        <x-lencana-status :status="$pendaftaran->status" />
                    </div>

                    <dl class="mt-8 grid gap-4 sm:grid-cols-2">
                        @foreach ([
                            'Nama anak' => $pendaftaran->nama_lengkap,
                            'Jenjang' => $pendaftaran->jenjang_label,
                            'Tahun ajaran' => $pendaftaran->tahun_ajaran,
                            'Terakhir diperbarui' => tanggal_id($pendaftaran->diverifikasi_pada ?? $pendaftaran->updated_at, true),
                        ] as $label => $nilai)
                            <div class="rounded-2xl bg-krem p-4">
                                <dt class="text-xs text-tinta-lembut">{{ $label }}</dt>
                                <dd class="mt-1 text-sm font-semibold text-tinta">{{ $nilai }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    @if ($pendaftaran->catatan_admin)
                        <div class="mt-6 rounded-[1.5rem] bg-kuning-100 p-5">
                            <p class="text-xs font-semibold text-tinta">Catatan dari panitia</p>
                            <p class="mt-1.5 text-sm leading-relaxed text-tinta-lembut">{{ $pendaftaran->catatan_admin }}</p>
                        </div>
                    @endif

                    <p class="mt-6 text-xs leading-relaxed text-tinta-lembut">
                        Ada pertanyaan tentang status ini?
                        <a
                            href="{{ wa_link('Assalamu\'alaikum, saya ingin menanyakan status pendaftaran nomor '.$pendaftaran->nomor_pendaftaran.'.') }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="font-semibold text-hijau-700 underline"
                        >Hubungi panitia</a>.
                    </p>
                </div>
            @endif
        </div>
    </section>
@endsection
