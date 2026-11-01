@extends('layouts.app')

@section('title', 'Pendaftaran Terkirim — '.config('sekolah.nama'))
{{-- Halaman ini memuat nomor pendaftaran anak. Jangan sampai masuk hasil
     pencarian, walaupun isinya hanya bisa dibuka lewat session. --}}
@section('robots', 'noindex, nofollow')

@section('content')
    @include('partials.kepala-halaman', [
        'rintisan' => 'Alhamdulillah',
        'judul' => 'Pendaftaran <span class="text-kuning-400">berhasil</span> dikirim',
        'teks' => 'Simpan nomor pendaftaran di bawah ini. Nomor tersebut dipakai untuk mengecek status seleksi.',
    ])

    <section class="bg-krem pb-16 sm:pb-24">
        <div class="mx-auto w-full max-w-3xl px-5 sm:px-6 lg:px-10">
            <div class="rounded-[2.5rem] bg-white p-8 text-center bayang-lembut sm:p-12">
                <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-hijau-100 text-hijau-700">
                    <x-ikon nama="centang-lingkaran" class="h-8 w-8" />
                </span>

                <p class="mt-6 teks-rintisan text-[10px] text-tinta-lembut">Nomor pendaftaran</p>

                <p class="mt-2 font-display text-3xl tracking-wide text-hijau-700 sm:text-4xl">
                    {{ $pendaftaran->nomor_pendaftaran }}
                </p>

                <div class="mt-4 flex justify-center">
                    <x-lencana-status :status="$pendaftaran->status" />
                </div>

                <dl class="mt-10 grid gap-4 text-left sm:grid-cols-2">
                    @foreach ([
                        'Nama anak' => $pendaftaran->nama_lengkap,
                        'Jenjang' => $pendaftaran->jenjang_label,
                        'Tanggal lahir' => tanggal_id($pendaftaran->tanggal_lahir),
                        'WhatsApp' => $pendaftaran->whatsapp,
                        'Tahun ajaran' => $pendaftaran->tahun_ajaran,
                        'Dikirim pada' => tanggal_id($pendaftaran->created_at, true),
                    ] as $label => $nilai)
                        <div class="rounded-2xl bg-krem p-4">
                            <dt class="text-xs text-tinta-lembut">{{ $label }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-tinta">{{ $nilai }}</dd>
                        </div>
                    @endforeach
                </dl>

                <div class="mt-10 rounded-[1.5rem] bg-kuning-100 p-6 text-left">
                    <h2 class="font-display text-base text-tinta">Apa selanjutnya?</h2>
                    <ol class="mt-3 grid gap-2 text-sm leading-relaxed text-tinta-lembut">
                        <li>1. Panitia memeriksa berkas Anda maksimal 3 hari kerja.</li>
                        <li>2. Kami menghubungi nomor WhatsApp di atas untuk menjadwalkan observasi.</li>
                        <li>3. Status terbaru bisa dicek sendiri kapan saja lewat menu Cek Status.</li>
                    </ol>
                </div>

                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <x-tombol :href="route('ppdb.cek')" warna="hijau" ikon="cari">
                        Cek Status Pendaftaran
                    </x-tombol>

                    <x-tombol
                        :href="wa_link('Assalamu\'alaikum, saya baru mengirim pendaftaran dengan nomor '.$pendaftaran->nomor_pendaftaran.' atas nama '.$pendaftaran->nama_lengkap.'.')"
                        warna="garis"
                        ikon="chat"
                        :luar="true"
                    >
                        Konfirmasi ke Panitia
                    </x-tombol>
                </div>

            </div>

            <p class="mt-6 text-center text-xs text-tinta-lembut">
                Halaman ini hanya tampil sekali setelah formulir terkirim. Pastikan nomor
                pendaftaran sudah Anda catat atau difoto.
            </p>
        </div>
    </section>
@endsection
