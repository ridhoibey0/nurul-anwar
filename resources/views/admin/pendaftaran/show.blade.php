@extends('layouts.admin')

@section('title', 'Detail Pendaftar')

@php
    $alamat = $pendaftaran->alamat;
    $ayah = $pendaftaran->orangTuaPeran(\App\Models\OrangTua::AYAH);
    $ibu = $pendaftaran->orangTuaPeran(\App\Models\OrangTua::IBU);
    $wali = $pendaftaran->orangTuaPeran(\App\Models\OrangTua::WALI);

    $kelompok = [
        'Data Calon Siswa' => [
            'Nama lengkap' => $pendaftaran->nama_lengkap,
            'Nama panggilan' => $pendaftaran->nama_panggilan,
            'Jenis kelamin' => $pendaftaran->jenis_kelamin_label,
            'Tempat, tanggal lahir' => $pendaftaran->tempat_lahir.', '.tanggal_id($pendaftaran->tanggal_lahir),
            'Usia saat ini' => $pendaftaran->usia,
            'NIK' => $pendaftaran->nik,
            'Anak ke' => $pendaftaran->anak_ke,
            'Jumlah saudara' => $pendaftaran->jumlah_saudara,
            'Agama' => $pendaftaran->agama,
            'Asal sekolah' => $pendaftaran->asal_sekolah,
            'Riwayat kesehatan' => $pendaftaran->riwayat_kesehatan,
        ],
        'Alamat' => [
            'Alamat' => $alamat?->jalan,
            'Kelurahan' => $alamat?->kelurahan,
            'Kecamatan' => $alamat?->kecamatan,
            'Kota / Kabupaten' => $alamat?->kota,
            'Kode pos' => $alamat?->kode_pos,
        ],
        'Data Ayah' => [
            'Nama' => $ayah?->nama,
            'NIK' => $ayah?->nik,
            'Pendidikan' => $ayah?->pendidikan,
            'Pekerjaan' => $ayah?->pekerjaan,
            'Penghasilan' => $ayah?->penghasilan,
            'Telepon' => $ayah?->telepon,
        ],
        'Data Ibu' => [
            'Nama' => $ibu?->nama,
            'NIK' => $ibu?->nik,
            'Pendidikan' => $ibu?->pendidikan,
            'Pekerjaan' => $ibu?->pekerjaan,
            'Penghasilan' => $ibu?->penghasilan,
            'Telepon' => $ibu?->telepon,
        ],
        'Wali & Kontak' => [
            'Nama wali' => $wali?->nama,
            'Hubungan' => $wali?->hubungan,
            'Telepon wali' => $wali?->telepon,
            'WhatsApp utama' => $pendaftaran->whatsapp,
            'Email' => $pendaftaran->email,
            'Catatan orang tua' => $pendaftaran->catatan_orang_tua,
        ],
    ];
@endphp

@section('content')
    <a href="{{ route('admin.pendaftaran.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-tinta-lembut hover:text-tinta">
        <x-ikon nama="chevron-kanan" class="h-4 w-4 rotate-180" />
        Kembali ke daftar
    </a>

    <div class="mt-5 grid gap-6 lg:grid-cols-[1.6fr_1fr] lg:items-start">
        {{-- Detail data --}}
        <div class="grid gap-6">
            <div class="rounded-[1.75rem] bg-white p-7 bayang-lembut">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="font-mono text-xs text-tinta-lembut">{{ $pendaftaran->nomor_pendaftaran }}</p>
                        <h2 class="mt-1 font-display text-2xl text-tinta">{{ $pendaftaran->nama_lengkap }}</h2>
                        <p class="mt-1 text-sm text-tinta-lembut">
                            {{ $pendaftaran->jenjang_label }} &middot; tahun ajaran {{ $pendaftaran->tahun_ajaran }}
                        </p>
                    </div>

                    <x-lencana-status :status="$pendaftaran->status" />
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <x-tombol
                        :href="wa_link('Assalamu\'alaikum, kami dari panitia PPDB '.config('sekolah.nama').' ingin menindaklanjuti pendaftaran nomor '.$pendaftaran->nomor_pendaftaran.'.')"
                        warna="hijau"
                        ukuran="sm"
                        ikon="chat"
                        :luar="true"
                    >
                        Hubungi orang tua
                    </x-tombol>

                    @if ($pendaftaran->email)
                        <x-tombol :href="'mailto:'.$pendaftaran->email" warna="garis" ukuran="sm" ikon="surel">
                            Kirim email
                        </x-tombol>
                    @endif
                </div>
            </div>

            @foreach ($kelompok as $judul => $baris)
                <section class="rounded-[1.75rem] bg-white p-7 bayang-lembut">
                    <h3 class="font-display text-lg text-tinta">{{ $judul }}</h3>

                    <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                        @foreach ($baris as $label => $nilai)
                            <div class="rounded-2xl bg-krem p-4">
                                <dt class="text-xs text-tinta-lembut">{{ $label }}</dt>
                                <dd class="mt-1 text-sm font-semibold text-tinta">
                                    {{ filled($nilai) ? $nilai : '—' }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endforeach
        </div>

        {{-- Panel aksi --}}
        <div class="grid gap-6 lg:sticky lg:top-24">
            <section class="rounded-[1.75rem] bg-white p-7 bayang-lembut">
                <h3 class="font-display text-lg text-tinta">Perbarui status</h3>

                <form method="POST" action="{{ route('admin.pendaftaran.status', $pendaftaran) }}" class="mt-5 grid gap-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="status" class="block text-xs font-semibold text-tinta">Status</label>
                        <select id="status" name="status" class="mt-2 block w-full rounded-2xl border-2 border-hijau-100 bg-white px-4 py-2.5 text-sm text-tinta focus:border-hijau-400 focus:outline-none">
                            @foreach ($daftarStatus as $status)
                                <option value="{{ $status->kode }}" @selected(old('status', $pendaftaran->status_kode) === $status->kode)>
                                    {{ $status->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="catatan_admin" class="block text-xs font-semibold text-tinta">
                            Catatan panitia
                        </label>
                        <p class="mt-0.5 text-[11px] text-tinta-lembut">
                            Catatan ini ikut tampil di halaman Cek Status milik orang tua.
                        </p>
                        <textarea
                            id="catatan_admin"
                            name="catatan_admin"
                            rows="4"
                            class="mt-2 block w-full rounded-2xl border-2 border-hijau-100 bg-white px-4 py-3 text-sm text-tinta focus:border-hijau-400 focus:outline-none"
                        >{{ old('catatan_admin', $pendaftaran->catatan_admin) }}</textarea>
                        @error('catatan_admin')
                            <p class="mt-1.5 text-xs text-koral-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-tombol type="submit" warna="koral" ukuran="sm" ikon="centang" class="w-full">
                        Simpan Perubahan
                    </x-tombol>
                </form>

                @if ($pendaftaran->diverifikasi_pada)
                    <p class="mt-4 text-[11px] text-tinta-lembut">
                        Terakhir diperbarui {{ tanggal_id($pendaftaran->diverifikasi_pada, true) }}
                        @if ($pendaftaran->verifikator)
                            oleh {{ $pendaftaran->verifikator->name }}
                        @endif
                    </p>
                @endif
            </section>

            <section class="rounded-[1.75rem] bg-white p-7 bayang-lembut">
                <h3 class="font-display text-lg text-tinta">Berkas</h3>

                <ul class="mt-5 grid gap-3">
                    @foreach ($pendaftaran->daftarBerkas() as $item)
                        @php
                            $jenis = $item['jenis'];
                            $label = $item['label'];
                            $berkas = $item['berkas'];
                        @endphp

                        <li>
                            @if ($berkas)
                                <a
                                    href="{{ route('admin.pendaftaran.berkas', [$pendaftaran, $jenis]) }}"
                                    target="_blank"
                                    class="flex items-center gap-3 rounded-2xl bg-krem p-4 transition-colors hover:bg-hijau-100"
                                >
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-hijau-700 text-white">
                                        <x-ikon nama="berkas" class="h-4 w-4" />
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-semibold text-tinta">{{ $label }}</span>
                                        <span class="block truncate text-[11px] text-tinta-lembut">{{ basename($berkas->path) }}</span>
                                    </span>
                                    <x-ikon nama="unduh" class="h-4 w-4 shrink-0 text-tinta-lembut" />
                                </a>
                            @else
                                <div class="flex items-center gap-3 rounded-2xl border-2 border-dashed border-hijau-100 p-4">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-krem-tua text-tinta-lembut">
                                        <x-ikon nama="berkas" class="h-4 w-4" />
                                    </span>
                                    <span class="text-sm text-tinta-lembut">{{ $label }} — tidak diunggah</span>
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="rounded-[1.75rem] border-2 border-koral-100 bg-white p-7">
                <h3 class="font-display text-lg text-koral-600">Hapus pendaftaran</h3>
                <p class="mt-2 text-sm leading-relaxed text-tinta-lembut">
                    Data dan seluruh berkas yang diunggah akan dihapus permanen dan tidak
                    bisa dikembalikan.
                </p>

                <form
                    method="POST"
                    action="{{ route('admin.pendaftaran.destroy', $pendaftaran) }}"
                    class="mt-5"
                    onsubmit="return confirm('Hapus pendaftaran {{ $pendaftaran->nomor_pendaftaran }} beserta berkasnya? Tindakan ini tidak bisa dibatalkan.')"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-full border-2 border-koral-500 px-5 py-2.5 text-sm font-semibold text-koral-600 transition-colors hover:bg-koral-500 hover:text-white"
                    >
                        <x-ikon nama="sampah" class="h-4 w-4" />
                        Hapus permanen
                    </button>
                </form>
            </section>
        </div>
    </div>
@endsection
