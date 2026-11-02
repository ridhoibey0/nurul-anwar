@extends('layouts.admin')

{{-- Dipakai bagian "Logo & gambar" di bawah untuk menyebut ketiga keadaannya. --}}
@use('App\Support\Merek')

@section('title', 'Identitas Sekolah')

@section('content')
    @unless ($pernahDisimpan)
        <div class="mb-6 flex items-start gap-3 rounded-[1.75rem] bg-kuning-100 p-5">
            <x-ikon nama="info" class="mt-0.5 h-5 w-5 shrink-0 text-koral-600" />
            <p class="text-sm text-tinta">
                Isian di bawah masih memakai nilai bawaan dari berkas <code>.env</code>.
                Begitu Anda menekan Simpan, nilainya pindah ke database dan sejak itu
                mengubah <code>.env</code> tidak lagi berpengaruh.
            </p>
        </div>
    @endunless

    <form
        method="POST"
        action="{{ route('admin.pengaturan.sekolah.update') }}"
        enctype="multipart/form-data"
        class="grid gap-6"
    >
        @csrf
        @method('PATCH')

        {{-- Identitas --}}
        <section class="rounded-[1.75rem] bg-white p-7 bayang-lembut">
            <h2 class="font-display text-lg text-tinta">Identitas</h2>
            <p class="mt-1 text-sm text-tinta-lembut">
                Dipakai di judul tiap halaman, navbar, footer, dan hasil pencarian Google.
            </p>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <x-form.input
                    nama="nama"
                    label="Nama sekolah"
                    :nilai="$sekolah['nama']"
                    :wajib="true"
                    bantuan="Nama lengkap, dipakai di judul halaman."
                />

                <x-form.input
                    nama="nama_pendek"
                    label="Nama pendek"
                    :nilai="$sekolah['nama_pendek']"
                    :wajib="true"
                    bantuan="Dipakai di navbar dan tempat sempit lainnya."
                />

                <div class="sm:col-span-2">
                    <x-form.input
                        nama="tagline"
                        label="Tagline"
                        :nilai="$sekolah['tagline']"
                        :wajib="true"
                        bantuan="Kalimat pendek di belakang nama sekolah pada judul halaman."
                    />
                </div>

                <x-form.input
                    nama="tahun_berdiri"
                    label="Tahun berdiri"
                    tipe="number"
                    :nilai="$sekolah['tahun_berdiri']"
                    :wajib="true"
                    min="1900"
                    max="{{ date('Y') }}"
                />

                <x-form.input
                    nama="akreditasi"
                    label="Akreditasi"
                    :nilai="$sekolah['akreditasi']"
                    bantuan="Misal: A, B. Kosongkan bila belum ada - lencananya ikut hilang dari website."
                />
            </div>
        </section>

        {{-- Kontak --}}
        <section class="rounded-[1.75rem] bg-white p-7 bayang-lembut">
            <h2 class="font-display text-lg text-tinta">Kontak</h2>
            <p class="mt-1 text-sm text-tinta-lembut">
                Semua tombol WhatsApp di website memakai nomor di bawah ini.
            </p>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <x-form.input
                    nama="telepon"
                    label="Nomor telepon"
                    :nilai="$sekolah['telepon']"
                    :wajib="true"
                    bantuan="Boleh ditulis apa adanya, misalnya (0265) 338279."
                />

                <x-form.input
                    nama="whatsapp"
                    label="Nomor WhatsApp"
                    :nilai="$sekolah['whatsapp']"
                    :wajib="true"
                    bantuan="Tulis bebas: 0812-3456-7890 atau 6281234567890, nanti dirapikan sendiri."
                />

                <x-form.input
                    nama="email"
                    label="Alamat email"
                    tipe="email"
                    :nilai="$sekolah['email']"
                    :wajib="true"
                />

                <x-form.input
                    nama="jam_operasional"
                    label="Jam operasional"
                    :nilai="$sekolah['jam_operasional']"
                    :wajib="true"
                    bantuan="Misal: Senin - Jumat, 07.00 - 13.00 WIB."
                />
            </div>
        </section>

        {{-- Alamat & peta --}}
        <section class="rounded-[1.75rem] bg-white p-7 bayang-lembut">
            <h2 class="font-display text-lg text-tinta">Alamat &amp; peta</h2>
            <p class="mt-1 text-sm text-tinta-lembut">
                Kota dan provinsi dipisah supaya Google bisa membacanya sebagai data alamat,
                bukan sekadar satu baris teks.
            </p>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-form.input
                        nama="alamat"
                        label="Alamat"
                        :nilai="$sekolah['alamat']"
                        :wajib="true"
                        bantuan="Jalan, nomor, RT/RW, dan kelurahan."
                    />
                </div>

                <x-form.input nama="kota" label="Kota / kabupaten" :nilai="$sekolah['kota']" :wajib="true" />
                <x-form.input nama="provinsi" label="Provinsi" :nilai="$sekolah['provinsi']" :wajib="true" />

                <x-form.input
                    nama="peta_lat"
                    label="Garis lintang (latitude)"
                    :nilai="$sekolah['peta_lat']"
                    bantuan="Dari Google Maps: klik kanan pin sekolah, angka pertama."
                />

                <x-form.input
                    nama="peta_lng"
                    label="Garis bujur (longitude)"
                    :nilai="$sekolah['peta_lng']"
                    bantuan="Angka kedua. Bila dikosongkan, peta jatuh ke pencarian nama."
                />

                <div class="sm:col-span-2">
                    <x-form.input
                        nama="peta_url"
                        label="Tautan Google Maps"
                        :nilai="$sekolah['peta_url']"
                        bantuan="Tautan yang dibuka saat pengunjung menekan alamat di halaman depan."
                    />
                </div>
            </div>
        </section>

        {{-- Media sosial --}}
        <section class="rounded-[1.75rem] bg-white p-7 bayang-lembut">
            <h2 class="font-display text-lg text-tinta">Media sosial</h2>
            <p class="mt-1 text-sm text-tinta-lembut">
                Ikon yang tautannya dikosongkan tidak ditampilkan di footer. Akun yang belum
                diisi juga tidak diklaim sebagai milik sekolah di data terstruktur Google.
            </p>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <x-form.input
                    nama="instagram"
                    label="Nama akun Instagram"
                    :nilai="$sekolah['instagram']"
                    bantuan="Cukup namanya, tanda @ ditambahkan sendiri."
                />

                <x-form.input
                    nama="instagram_url"
                    label="Tautan Instagram"
                    :nilai="$sekolah['sosial']['instagram']"
                    bantuan="https://instagram.com/namaakun"
                />

                <x-form.input
                    nama="facebook_url"
                    label="Tautan Facebook"
                    :nilai="$sekolah['sosial']['facebook']"
                />

                <x-form.input
                    nama="youtube_url"
                    label="Tautan YouTube"
                    :nilai="$sekolah['sosial']['youtube']"
                />
            </div>
        </section>

        {{-- Logo & gambar --}}
        <section class="rounded-[1.75rem] bg-white p-7 bayang-lembut">
            <h2 class="font-display text-lg text-tinta">Logo &amp; gambar</h2>
            <p class="mt-1 text-sm text-tinta-lembut">
                Diatur dari sini, tanpa perlu menyentuh berkas di server. Tiap gambar boleh
                diunggah, dikembalikan ke gambar bawaan website, atau dikosongkan sama
                sekali.
            </p>

            <div class="mt-5 grid gap-7 sm:grid-cols-2">

                {{-- Logo --}}
                <div>
                    <p class="text-sm font-semibold text-tinta">Logo sekolah</p>
                    <p class="mt-0.5 text-xs text-tinta-lembut">
                        Dipakai di navbar, footer, panel admin ini, dan sebagai ikon tab
                        peramban.
                    </p>

                    @if (logo_url())
                        <img
                            src="{{ logo_url() }}"
                            alt="Logo yang sekarang dipakai"
                            class="mt-3 h-20 w-20 rounded-2xl bg-krem object-contain p-2"
                            width="80"
                            height="80"
                        >
                    @else
                        <p class="mt-3 flex h-20 w-20 items-center justify-center rounded-2xl border-2 border-dashed border-hijau-200 px-2 text-center text-[10px] leading-tight text-tinta-lembut">
                            Tanpa logo
                        </p>
                    @endif

                    @php
                        // Pilihan "yang sudah diunggah" cuma ditawarkan bila memang ada
                        // berkas unggahannya; kalau tidak, pilihan itu tak ada isinya.
                        $pilihanLogo = array_filter([
                            Merek::UNGGAHAN => $logoPilihan === Merek::UNGGAHAN ? 'Logo yang sudah diunggah' : null,
                            Merek::BAWAAN => 'Logo bawaan website',
                            Merek::KOSONG => 'Tanpa logo, cukup nama sekolah',
                        ]);
                    @endphp

                    <fieldset class="mt-4 grid gap-2">
                        <legend class="mb-1 text-xs font-semibold text-tinta">Logo yang dipakai</legend>

                        @foreach ($pilihanLogo as $nilai => $label)
                            <label class="flex cursor-pointer items-start gap-2 text-xs text-tinta-lembut">
                                <input
                                    type="radio"
                                    name="logo_pilihan"
                                    value="{{ $nilai }}" @checked(old('logo_pilihan', $logoPilihan) === $nilai)
                                    class="mt-px h-3.5 w-3.5 shrink-0 accent-koral-500"
                                >
                                {{ $label }}
                            </label>
                        @endforeach
                    </fieldset>

                    <div class="mt-4">
                        <x-form.berkas
                            nama="logo"
                            :label="$logoPilihan === Merek::UNGGAHAN ? 'Ganti logo' : 'Unggah logo'"
                            terima=".png,.jpg,.jpeg,.webp"
                            keterangan="PNG, JPG, atau WEBP · maksimal 2 MB"
                        />
                    </div>

                    <p class="mt-3 text-xs text-tinta-lembut">
                        Memilih berkas di atas otomatis memakai logo itu, apa pun pilihan
                        yang tercentang. Paling pas logo berbentuk persegi atau bundar, PNG
                        dengan latar transparan; logo yang memanjang tetap diterima, hanya
                        tampil lebih kecil karena dipasang di dalam kotak persegi. Berkas
                        SVG belum bisa diterima karena alasan keamanan.
                    </p>
                </div>

                {{-- Gambar bagikan --}}
                <div>
                    <p class="text-sm font-semibold text-tinta">Gambar bagikan</p>
                    <p class="mt-0.5 text-xs text-tinta-lembut">
                        Yang muncul saat tautan website dikirim lewat WhatsApp atau
                        dibagikan ke media sosial.
                    </p>

                    @if (gambar_bagikan_url())
                        <img
                            src="{{ gambar_bagikan_url() }}"
                            alt="Gambar bagikan yang sekarang dipakai"
                            class="mt-3 aspect-[1200/630] w-full rounded-2xl bg-krem object-cover"
                        >
                    @else
                        <p class="mt-3 flex aspect-[1200/630] w-full items-center justify-center rounded-2xl border-2 border-dashed border-hijau-200 text-center text-xs text-tinta-lembut">
                            Tanpa gambar bagikan
                        </p>
                    @endif

                    @php
                        $pilihanBagikan = array_filter([
                            Merek::UNGGAHAN => $bagikanPilihan === Merek::UNGGAHAN ? 'Gambar yang sudah diunggah' : null,
                            Merek::BAWAAN => 'Gambar bawaan website',
                            Merek::KOSONG => 'Tanpa gambar, pratinjau tautan tampil polos',
                        ]);
                    @endphp

                    <fieldset class="mt-4 grid gap-2">
                        <legend class="mb-1 text-xs font-semibold text-tinta">Gambar yang dipakai</legend>

                        @foreach ($pilihanBagikan as $nilai => $label)
                            <label class="flex cursor-pointer items-start gap-2 text-xs text-tinta-lembut">
                                <input
                                    type="radio"
                                    name="gambar_bagikan_pilihan"
                                    value="{{ $nilai }}" @checked(old('gambar_bagikan_pilihan', $bagikanPilihan) === $nilai)
                                    class="mt-px h-3.5 w-3.5 shrink-0 accent-koral-500"
                                >
                                {{ $label }}
                            </label>
                        @endforeach
                    </fieldset>

                    <div class="mt-4">
                        <x-form.berkas
                            nama="gambar_bagikan"
                            :label="$bagikanPilihan === Merek::UNGGAHAN ? 'Ganti gambar' : 'Unggah gambar'"
                            terima=".png,.jpg,.jpeg,.webp"
                            keterangan="PNG, JPG, atau WEBP · maksimal 4 MB"
                        />
                    </div>

                    <p class="mt-3 text-xs text-tinta-lembut">
                        Ukuran paling pas 1200 &times; 630 piksel. Inilah gambar pertama yang
                        dilihat orang tua sebelum membuka tautannya, jadi pilih foto yang
                        ramai dan jelas &mdash; atau kosongkan bila tidak ingin ada foto anak
                        yang ikut tampil tiap kali tautan dibagikan.
                    </p>
                </div>

            </div>
        </section>

        <div class="flex flex-wrap items-center gap-3">
            <x-tombol type="submit" warna="koral" ikon="centang">Simpan perubahan</x-tombol>

            <x-tombol :href="route('home')" warna="garis" :luar="true">Lihat website</x-tombol>
        </div>
    </form>
@endsection
