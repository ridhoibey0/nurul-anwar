<footer class="relative mt-24">
    <x-awan class="text-hijau-900" />

    <div class="bg-hijau-900 pb-10 pt-6 text-hijau-100">
        <div class="mx-auto w-full max-w-7xl px-5 sm:px-6 lg:px-10">
            <div class="grid gap-12 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <div class="flex items-center gap-2.5 text-white">
                        <img src="{{ logo_url() }}" alt="" class="h-8 w-8" width="32" height="32">
                        <span class="font-display text-xl font-semibold">{{ config('sekolah.nama_pendek') }}</span>
                    </div>

                    <p class="mt-4 max-w-[280px] text-sm leading-relaxed text-hijau-200">
                        {{ config('sekolah.alamat') }}
                    </p>

                    <p class="mt-3 text-sm font-semibold text-white">{{ config('sekolah.jam_operasional') }}</p>

                    <div class="mt-5 flex gap-3">
                        @foreach ([
                            ['url' => config('sekolah.sosial.instagram'), 'label' => 'Instagram', 'berkas' => 'social-ig.svg'],
                            ['url' => config('sekolah.sosial.facebook'), 'label' => 'Facebook', 'berkas' => 'social-fb.svg'],
                            ['url' => config('sekolah.sosial.youtube'), 'label' => 'YouTube', 'berkas' => 'social-tw.svg'],
                        ] as $sosial)
                            @continue (blank($sosial['url']))

                            <a
                                href="{{ $sosial['url'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="{{ $sosial['label'] }}"
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 transition-colors hover:bg-white/20"
                            >
                                <img src="{{ asset('images/kiddies/'.$sosial['berkas']) }}" alt="" class="h-5 w-5" width="20" height="20">
                            </a>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h3 class="font-display text-lg font-semibold text-white">Tentang Kami</h3>
                    <ul class="mt-5 grid gap-3 text-sm">
                        @foreach ([
                            ['label' => 'Program & Jenjang', 'url' => '/#program'],
                            ['label' => 'Keunggulan', 'url' => '/#keunggulan'],
                            ['label' => 'Keseharian Anak', 'url' => '/#keseharian'],
                            ['label' => 'Guru & Fasilitas', 'url' => '/#guru'],
                        ] as $tautan)
                            <li>
                                <a href="{{ $tautan['url'] }}" class="inline-flex items-center gap-2 text-hijau-200 transition-colors hover:text-white">
                                    <x-ikon nama="chevron-kanan" class="h-3.5 w-3.5" />
                                    {{ $tautan['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h3 class="font-display text-lg font-semibold text-white">Pendaftaran</h3>
                    <ul class="mt-5 grid gap-3 text-sm">
                        @foreach ([
                            ['label' => 'Informasi PPDB', 'url' => route('ppdb.info')],
                            ['label' => 'Formulir Pendaftaran', 'url' => route('ppdb.create')],
                            ['label' => 'Cek Status Pendaftaran', 'url' => route('ppdb.cek')],
                            ['label' => 'Login Panitia', 'url' => route('admin.login')],
                        ] as $tautan)
                            <li>
                                <a href="{{ $tautan['url'] }}" class="inline-flex items-center gap-2 text-hijau-200 transition-colors hover:text-white">
                                    <x-ikon nama="chevron-kanan" class="h-3.5 w-3.5" />
                                    {{ $tautan['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h3 class="font-display text-lg font-semibold text-white">Hubungi Kami</h3>

                    <p class="mt-4 max-w-[260px] text-sm text-hijau-200">
                        Ada pertanyaan seputar pendaftaran atau kegiatan sekolah? Chat kami langsung.
                    </p>

                    <div class="mt-4 grid gap-2 text-sm">
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', config('sekolah.telepon')) }}" class="inline-flex items-center gap-2 text-white hover:text-kuning-300">
                            <x-ikon nama="telepon" class="h-4 w-4" />
                            {{ config('sekolah.telepon') }}
                        </a>
                        <a href="mailto:{{ config('sekolah.email') }}" class="inline-flex items-center gap-2 text-white hover:text-kuning-300">
                            <x-ikon nama="surel" class="h-4 w-4" />
                            {{ config('sekolah.email') }}
                        </a>
                    </div>

                    <x-tombol
                        :href="wa_link('Assalamu\'alaikum, saya ingin bertanya tentang '.config('sekolah.nama'))"
                        warna="kuning"
                        ukuran="sm"
                        ikon="chat"
                        :luar="true"
                        class="mt-5"
                    >
                        Chat WhatsApp
                    </x-tombol>
                </div>
            </div>

            <div class="mt-14 flex flex-col items-center justify-between gap-3 border-t border-white/10 pt-6 sm:flex-row">
                <p class="text-xs text-hijau-200">
                    &copy; {{ date('Y') }} {{ config('sekolah.nama') }}. Seluruh hak cipta.
                </p>
                <a href="#" class="inline-flex items-center gap-2 text-xs font-semibold text-hijau-200 hover:text-white">
                    Kembali ke atas
                    <x-ikon nama="chevron-bawah" class="h-4 w-4 rotate-180" />
                </a>
            </div>
        </div>
    </div>
</footer>
