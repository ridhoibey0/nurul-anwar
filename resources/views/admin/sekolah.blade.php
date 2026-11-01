@extends('layouts.admin')

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

    <form method="POST" action="{{ route('admin.pengaturan.sekolah.update') }}" class="grid gap-6">
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

        <div class="flex flex-wrap items-center gap-3">
            <x-tombol type="submit" warna="koral" ikon="centang">Simpan perubahan</x-tombol>

            <x-tombol :href="route('home')" warna="garis" :luar="true">Lihat website</x-tombol>
        </div>
    </form>
@endsection
