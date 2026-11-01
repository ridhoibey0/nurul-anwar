<section id="lokasi" class="bg-krem py-16 sm:py-24">
    <div class="mx-auto w-full max-w-7xl px-5 sm:px-6 lg:px-10">
        <x-judul
            rintisan="Kunjungi kami"
            teks="Kami senang menyambut kunjungan orang tua. Silakan datang langsung atau hubungi kami terlebih dahulu."
            class="muncul"
        >
            Lokasi & <span class="text-koral-500">kontak</span> sekolah
        </x-judul>

        <div class="muncul mt-12 grid gap-8 lg:grid-cols-2 lg:items-stretch">
            <div class="overflow-hidden rounded-[2.5rem] bayang-lembut">
                <iframe
                    title="Peta lokasi {{ config('sekolah.nama') }}"
                    src="{{ peta_embed() }}"
                    class="h-80 w-full lg:h-full lg:min-h-[400px]"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
            </div>

            <div class="grid content-start gap-4">
                @foreach ([
                    ['ikon' => 'peta', 'label' => 'Alamat', 'nilai' => config('sekolah.alamat'), 'url' => config('sekolah.peta_url')],
                    ['ikon' => 'jam', 'label' => 'Jam Operasional', 'nilai' => config('sekolah.jam_operasional'), 'url' => null],
                    ['ikon' => 'telepon', 'label' => 'Telepon', 'nilai' => config('sekolah.telepon'), 'url' => tel_link()],
                    ['ikon' => 'surel', 'label' => 'Email', 'nilai' => config('sekolah.email'), 'url' => 'mailto:'.config('sekolah.email')],
                ] as $kontak)
                    @if ($kontak['url'])
                        <a
                            href="{{ $kontak['url'] }}"
                            @if (str_starts_with($kontak['url'], 'http')) target="_blank" rel="noopener noreferrer" @endif
                            class="flex items-start gap-4 rounded-[1.75rem] bg-white p-6 bayang-lembut transition-transform hover:-translate-y-0.5"
                        >
                            @include('partials.baris-kontak', ['kontak' => $kontak])
                        </a>
                    @else
                        <div class="flex items-start gap-4 rounded-[1.75rem] bg-white p-6 bayang-lembut">
                            @include('partials.baris-kontak', ['kontak' => $kontak])
                        </div>
                    @endif
                @endforeach

                <x-tombol
                    :href="wa_link('Assalamu\'alaikum, saya ingin bertanya tentang '.config('sekolah.nama'))"
                    warna="hijau"
                    ikon="chat"
                    :luar="true"
                    class="mt-2"
                >
                    Chat via WhatsApp
                </x-tombol>
            </div>
        </div>
    </div>
</section>
