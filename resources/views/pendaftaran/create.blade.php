@extends('layouts.app')

@section('title', 'Formulir Pendaftaran Siswa Baru — '.config('sekolah.nama'))
@section('description', 'Isi formulir pendaftaran siswa baru '.config('sekolah.nama').' secara online. Hanya butuh sekitar 10 menit.')

@php
    // Dikunci dengan kode jenjang, bukan nomor urut: itu nilai yang dikirim
    // formulir dan yang dicocokkan aturan validasi exists:jenjang,kode.
    $opsiJenjang = $jenjang->mapWithKeys(fn ($j) => [$j->kode => $j->nama.' ('.$j->usia.')'])->all();

    $opsiPendidikan = [
        'Tidak sekolah' => 'Tidak sekolah',
        'SD/MI' => 'SD/MI',
        'SMP/MTs' => 'SMP/MTs',
        'SMA/SMK/MA' => 'SMA/SMK/MA',
        'D1-D3' => 'D1-D3',
        'S1/D4' => 'S1/D4',
        'S2' => 'S2',
        'S3' => 'S3',
    ];

    $opsiPenghasilan = [
        'Tidak berpenghasilan' => 'Tidak berpenghasilan',
        '< 1 juta' => 'Kurang dari Rp1 juta',
        '1 - 2 juta' => 'Rp1 - 2 juta',
        '2 - 5 juta' => 'Rp2 - 5 juta',
        '5 - 10 juta' => 'Rp5 - 10 juta',
        '> 10 juta' => 'Lebih dari Rp10 juta',
    ];

    // Buka langsung di langkah yang bermasalah ketika validasi server gagal.
    $kolomPerLangkah = [
        1 => ['jenjang', 'nama_lengkap', 'nama_panggilan', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'nik', 'anak_ke', 'jumlah_saudara', 'agama'],
        2 => ['alamat', 'kelurahan', 'kecamatan', 'kota', 'kode_pos', 'asal_sekolah', 'riwayat_kesehatan'],
        3 => ['nama_ayah', 'nik_ayah', 'pendidikan_ayah', 'pekerjaan_ayah', 'penghasilan_ayah', 'telepon_ayah', 'nama_ibu', 'nik_ibu', 'pendidikan_ibu', 'pekerjaan_ibu', 'penghasilan_ibu', 'telepon_ibu', 'nama_wali', 'hubungan_wali', 'telepon_wali', 'whatsapp', 'email'],
        4 => ['berkas_kk', 'berkas_akta', 'berkas_foto', 'catatan_orang_tua', 'persetujuan'],
    ];

    $langkahAwal = 1;

    foreach ($kolomPerLangkah as $nomor => $kolom) {
        if ($errors->hasAny($kolom)) {
            $langkahAwal = $nomor;
            break;
        }
    }

    $judulLangkah = [
        1 => 'Data Calon Siswa',
        2 => 'Alamat & Riwayat',
        3 => 'Orang Tua / Wali',
        4 => 'Berkas & Konfirmasi',
    ];
@endphp

@section('content')
    @include('partials.kepala-halaman', [
        'rintisan' => 'Tahun ajaran '.config('ppdb.tahun_ajaran'),
        'judul' => 'Formulir <span class="text-kuning-400">Pendaftaran</span> Siswa Baru',
        'teks' => 'Isi data berikut dengan lengkap. Tanda bintang menandakan isian yang wajib diisi.',
        'jejak' => ['Informasi PPDB' => route('ppdb.info'), 'Formulir' => null],
    ])

    <section class="bg-krem pb-16 sm:pb-24">
        <div class="mx-auto w-full max-w-4xl px-5 sm:px-6 lg:px-10">
            @if ($errors->any())
                <div class="mb-8 rounded-[1.5rem] border-2 border-koral-500 bg-koral-100 p-6">
                    <p class="flex items-center gap-2 font-display text-base text-koral-600">
                        <x-ikon nama="peringatan" class="h-5 w-5" />
                        Ada {{ $errors->count() }} isian yang perlu diperbaiki
                    </p>
                    <ul class="mt-3 list-inside list-disc space-y-1 text-sm text-tinta">
                        @foreach ($errors->all() as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                    <p class="mt-4 text-xs text-tinta-lembut">
                        Catatan: demi keamanan peramban, berkas yang sudah dipilih harus
                        dipilih ulang setelah halaman dimuat ulang.
                    </p>
                </div>
            @endif

            <form
                x-data="formulirPendaftaran({{ $langkahAwal }})"
                x-ref="wadah"
                method="POST"
                action="{{ route('ppdb.store') }}"
                enctype="multipart/form-data"
                x-on:submit="mengirim = true"
                class="rounded-[2.5rem] bg-white p-6 bayang-lembut sm:p-10"
            >
                @csrf

                {{-- Indikator langkah --}}
                <ol class="flex items-center gap-2 sm:gap-3">
                    @foreach ($judulLangkah as $nomor => $judul)
                        <li class="flex flex-1 flex-col gap-2">
                            <div
                                class="h-1.5 rounded-full transition-colors"
                                :class="langkah >= {{ $nomor }} ? 'bg-hijau-500' : 'bg-hijau-100'"
                            ></div>
                            <span
                                class="hidden text-xs font-semibold transition-colors sm:block"
                                :class="langkah >= {{ $nomor }} ? 'text-hijau-700' : 'text-tinta-lembut'"
                            >{{ $nomor }}. {{ $judul }}</span>
                        </li>
                    @endforeach
                </ol>

                <p class="mt-6 font-display text-2xl text-tinta sm:hidden" x-text="judulLangkah[langkah]"></p>

                {{-- LANGKAH 1 --}}
                {{-- Langkah 1 sengaja tidak memakai x-cloak agar formulir tetap tampil
                     seandainya berkas JavaScript gagal dimuat. --}}
                <fieldset x-ref="langkah1" x-show="langkah === 1" class="mt-8">
                    <legend class="sr-only">Data calon siswa</legend>

                    <h2 class="hidden font-display text-2xl text-tinta sm:block">Data Calon Siswa</h2>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <x-form.pilihan
                            nama="jenjang"
                            label="Jenjang yang dituju"
                            :opsi="$opsiJenjang"
                            :wajib="true"
                            class="sm:col-span-2"
                        />

                        <x-form.input nama="nama_lengkap" label="Nama lengkap anak" :wajib="true" placeholder="Sesuai akta kelahiran" />
                        <x-form.input nama="nama_panggilan" label="Nama panggilan" placeholder="Nama yang biasa dipakai" />

                        <x-form.pilihan
                            nama="jenis_kelamin"
                            label="Jenis kelamin"
                            :opsi="['L' => 'Laki-laki', 'P' => 'Perempuan']"
                            :wajib="true"
                        />

                        <x-form.pilihan
                            nama="agama"
                            label="Agama"
                            :opsi="['Islam' => 'Islam', 'Kristen' => 'Kristen', 'Katolik' => 'Katolik', 'Hindu' => 'Hindu', 'Buddha' => 'Buddha', 'Konghucu' => 'Konghucu']"
                            :wajib="true"
                            nilai="Islam"
                            :kosong="false"
                        />

                        <x-form.input nama="tempat_lahir" label="Tempat lahir" :wajib="true" placeholder="Kota kelahiran" />
                        <x-form.input nama="tanggal_lahir" label="Tanggal lahir" tipe="date" :wajib="true" />

                        <x-form.input
                            nama="nik"
                            label="NIK anak"
                            bantuan="16 digit, lihat Kartu Keluarga"
                            inputmode="numeric"
                            maxlength="16"
                            placeholder="Opsional"
                        />

                        <div class="grid grid-cols-2 gap-5">
                            <x-form.input nama="anak_ke" label="Anak ke-" tipe="number" min="1" max="20" />
                            <x-form.input nama="jumlah_saudara" label="Jumlah saudara" tipe="number" min="0" max="20" />
                        </div>
                    </div>
                </fieldset>

                {{-- LANGKAH 2 --}}
                <fieldset x-ref="langkah2" x-show="langkah === 2" class="mt-8" x-cloak>
                    <legend class="sr-only">Alamat dan riwayat</legend>

                    <h2 class="hidden font-display text-2xl text-tinta sm:block">Alamat & Riwayat</h2>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <x-form.area
                            nama="alamat"
                            label="Alamat tempat tinggal"
                            :wajib="true"
                            class="sm:col-span-2"
                            placeholder="Nama jalan, nomor rumah, RT/RW"
                        />

                        <x-form.input nama="kelurahan" label="Kelurahan / Desa" />
                        <x-form.input nama="kecamatan" label="Kecamatan" />
                        <x-form.input nama="kota" label="Kota / Kabupaten" />
                        <x-form.input nama="kode_pos" label="Kode pos" inputmode="numeric" maxlength="10" />

                        <x-form.input
                            nama="asal_sekolah"
                            label="Asal sekolah / PAUD"
                            bantuan="Kosongkan bila belum pernah sekolah"
                            class="sm:col-span-2"
                        />

                        <x-form.area
                            nama="riwayat_kesehatan"
                            label="Riwayat kesehatan / alergi"
                            bantuan="Informasi ini membantu guru mendampingi anak dengan tepat"
                            class="sm:col-span-2"
                        />
                    </div>
                </fieldset>

                {{-- LANGKAH 3 --}}
                <fieldset x-ref="langkah3" x-show="langkah === 3" class="mt-8" x-cloak>
                    <legend class="sr-only">Data orang tua dan wali</legend>

                    <h2 class="hidden font-display text-2xl text-tinta sm:block">Orang Tua / Wali</h2>

                    <h3 class="mt-6 font-display text-base text-hijau-700">Data Ayah</h3>
                    <div class="mt-4 grid gap-5 sm:grid-cols-2">
                        <x-form.input nama="nama_ayah" label="Nama ayah" :wajib="true" />
                        <x-form.input nama="nik_ayah" label="NIK ayah" inputmode="numeric" maxlength="16" />
                        <x-form.pilihan nama="pendidikan_ayah" label="Pendidikan terakhir" :opsi="$opsiPendidikan" />
                        <x-form.input nama="pekerjaan_ayah" label="Pekerjaan" />
                        <x-form.pilihan nama="penghasilan_ayah" label="Penghasilan per bulan" :opsi="$opsiPenghasilan" />
                        <x-form.input nama="telepon_ayah" label="Nomor telepon" inputmode="tel" placeholder="08xxxxxxxxxx" />
                    </div>

                    <h3 class="mt-10 font-display text-base text-hijau-700">Data Ibu</h3>
                    <div class="mt-4 grid gap-5 sm:grid-cols-2">
                        <x-form.input nama="nama_ibu" label="Nama ibu" :wajib="true" />
                        <x-form.input nama="nik_ibu" label="NIK ibu" inputmode="numeric" maxlength="16" />
                        <x-form.pilihan nama="pendidikan_ibu" label="Pendidikan terakhir" :opsi="$opsiPendidikan" />
                        <x-form.input nama="pekerjaan_ibu" label="Pekerjaan" />
                        <x-form.pilihan nama="penghasilan_ibu" label="Penghasilan per bulan" :opsi="$opsiPenghasilan" />
                        <x-form.input nama="telepon_ibu" label="Nomor telepon" inputmode="tel" placeholder="08xxxxxxxxxx" />
                    </div>

                    <h3 class="mt-10 font-display text-base text-hijau-700">
                        Wali <span class="font-sans text-xs font-normal text-tinta-lembut">(isi bila anak tinggal bersama wali)</span>
                    </h3>
                    <div class="mt-4 grid gap-5 sm:grid-cols-3">
                        <x-form.input nama="nama_wali" label="Nama wali" />
                        <x-form.input nama="hubungan_wali" label="Hubungan dengan anak" placeholder="Nenek, paman, ..." />
                        <x-form.input nama="telepon_wali" label="Nomor telepon" inputmode="tel" />
                    </div>

                    <h3 class="mt-10 font-display text-base text-hijau-700">Kontak yang bisa dihubungi</h3>
                    <div class="mt-4 grid gap-5 sm:grid-cols-2">
                        <x-form.input
                            nama="whatsapp"
                            label="Nomor WhatsApp aktif"
                            :wajib="true"
                            inputmode="tel"
                            placeholder="08xxxxxxxxxx"
                            bantuan="Panitia akan menghubungi nomor ini"
                        />
                        <x-form.input nama="email" label="Alamat email" tipe="email" placeholder="Opsional" />
                    </div>
                </fieldset>

                {{-- LANGKAH 4 --}}
                <fieldset x-ref="langkah4" x-show="langkah === 4" class="mt-8" x-cloak>
                    <legend class="sr-only">Berkas dan konfirmasi</legend>

                    <h2 class="hidden font-display text-2xl text-tinta sm:block">Berkas & Konfirmasi</h2>

                    <div class="mt-6 grid gap-5">
                        <x-form.berkas nama="berkas_kk" label="Kartu Keluarga" :wajib="true" />
                        <x-form.berkas nama="berkas_akta" label="Akta Kelahiran anak" :wajib="true" />
                        <x-form.berkas nama="berkas_foto" label="Pas foto anak" terima=".jpg,.jpeg,.png" bantuan="Opsional, latar bebas" />

                        <x-form.area
                            nama="catatan_orang_tua"
                            label="Catatan untuk panitia"
                            bantuan="Pertanyaan atau hal yang perlu kami ketahui"
                        />

                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl bg-hijau-50 p-5">
                            <input
                                type="checkbox"
                                name="persetujuan"
                                value="1"
                                required
                                @checked(old('persetujuan'))
                                class="mt-0.5 h-5 w-5 shrink-0 rounded border-2 border-hijau-300 text-hijau-600 focus:ring-hijau-400"
                            >
                            <span class="text-sm leading-relaxed text-tinta">
                                Saya menyatakan data dan berkas yang diisi adalah benar, serta
                                bersedia dihubungi panitia PPDB {{ config('sekolah.nama') }} untuk
                                proses verifikasi.
                            </span>
                        </label>

                        @error('persetujuan')
                            <p class="-mt-2 flex items-start gap-1.5 text-xs text-koral-600">
                                <x-ikon nama="peringatan" class="mt-px h-3.5 w-3.5 shrink-0" />
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </fieldset>

                {{-- Navigasi --}}
                <div class="mt-10 flex items-center justify-between gap-4 border-t border-hijau-100 pt-6">
                    <button
                        type="button"
                        x-show="langkah > 1"
                        @click="mundur()"
                        class="inline-flex items-center gap-2 rounded-full px-5 py-3 text-sm font-semibold text-tinta-lembut transition-colors hover:bg-hijau-50 hover:text-tinta"
                    >
                        <x-ikon nama="chevron-kanan" class="h-4 w-4 rotate-180" />
                        Kembali
                    </button>

                    <span x-show="langkah === 1" aria-hidden="true"></span>

                    <div class="flex items-center gap-3">
                        <span class="text-xs text-tinta-lembut" x-text="'Langkah ' + langkah + ' dari 4'"></span>

                        <x-tombol
                            type="button"
                            warna="hijau"
                            x-show="langkah < 4"
                            x-on:click="maju()"
                        >
                            Lanjut
                        </x-tombol>

                        <x-tombol
                            type="submit"
                            warna="koral"
                            ikon="centang"
                            x-show="langkah === 4"
                            x-bind:disabled="mengirim"
                            x-bind:class="mengirim ? 'cursor-wait opacity-70' : ''"
                        >
                            <span x-text="mengirim ? 'Mengirim...' : 'Kirim Pendaftaran'"></span>
                        </x-tombol>
                    </div>
                </div>
            </form>

            <p class="mt-6 text-center text-xs text-tinta-lembut">
                Butuh bantuan mengisi formulir?
                <a href="{{ wa_link('Assalamu\'alaikum, saya butuh bantuan mengisi formulir pendaftaran.') }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-hijau-700 underline">
                    Hubungi panitia lewat WhatsApp
                </a>
            </p>
        </div>
    </section>
@endsection
