<section id="pendaftaran" class="bg-krem py-16 sm:py-24">
    <div class="mx-auto w-full max-w-7xl px-5 sm:px-6 lg:px-10">
        <div class="muncul relative overflow-hidden rounded-[3rem] bg-koral-500 px-6 py-14 sm:px-12 lg:px-16">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-20 left-10 h-56 w-56 rounded-full bg-white/10"></div>
            </div>

            <div class="relative grid items-center gap-12 lg:grid-cols-[1.2fr_0.8fr]">
                <div>
                    <p class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-xs font-semibold text-white">
                        <x-ikon nama="telepon" class="h-4 w-4" />
                        Hubungi kami: {{ config('sekolah.telepon') }}
                    </p>

                    <h2 class="mt-6 max-w-2xl font-display text-3xl leading-tight text-white sm:text-[2.6rem]">
                        Pendaftaran siswa baru tahun ajaran
                        <span class="text-kuning-300">{{ config('ppdb.tahun_ajaran') }}</span>
                        @if (config('ppdb.buka'))
                            sudah dibuka
                        @else
                            akan segera dibuka
                        @endif
                    </h2>

                    <p class="mt-4 max-w-xl text-sm leading-relaxed text-white/85">
                        Isi formulir online kurang dari 10 menit. Berkas bisa diunggah langsung
                        dari ponsel, dan status pendaftaran dapat dicek kapan saja dengan nomor
                        pendaftaran yang Anda terima.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <x-tombol :href="route('ppdb.create')" warna="putih" ukuran="lg">
                            Isi Formulir Pendaftaran
                        </x-tombol>

                        <x-tombol :href="route('ppdb.cek')" warna="garis-putih" ukuran="lg" ikon="cari">
                            Cek Status
                        </x-tombol>
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-[260px]">
                    <div class="aspect-[4/5] overflow-hidden rounded-[2.5rem] bg-kuning-300 bayang-kartu">
                        <img
                            src="{{ asset('images/kiddies/teacher-mom-hero.png') }}"
                            alt="Guru {{ config('sekolah.nama_pendek') }}"
                            class="h-full w-full object-cover"
                            loading="lazy"
                        >
                    </div>

                    <div class="absolute -bottom-4 -left-4 rounded-2xl bg-white px-4 py-3 bayang-kartu">
                        <p class="font-display text-lg text-hijau-700">{{ config('sekolah.jumlah_guru') }} guru</p>
                        <p class="text-[11px] text-tinta-lembut">siap mendampingi</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
