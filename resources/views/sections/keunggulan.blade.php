<section id="keunggulan" class="relative bg-krem">
    <x-awan class="text-hijau-800" />

    <div class="bg-hijau-800 pb-4 pt-2 sm:pb-8">
        <div class="mx-auto w-full max-w-7xl px-5 sm:px-6 lg:px-10">
            <x-judul
                rintisan="Kenapa orang tua memilih kami"
                :gelap="true"
                teks="Tiga hal yang kami jaga sejak hari pertama anak masuk sekolah."
                class="muncul"
            >
                Ruang yang <span class="text-kuning-400">aman</span> untuk anak bersinar
            </x-judul>

            <div class="muncul mt-14 grid gap-6 lg:grid-cols-3">
                @foreach ($keunggulan as $item)
                    @php
                        $gaya = match ($item['warna']) {
                            'kuning' => 'bg-kuning-400 text-tinta',
                            'koral' => 'bg-koral-500 text-white',
                            default => 'bg-hijau-500 text-white',
                        };

                        $ikon = match ($item['ikon']) {
                            'badge' => 'lencana',
                            'heart' => 'hati',
                            default => 'perisai',
                        };
                    @endphp

                    <div class="rounded-[2.5rem] bg-white/5 p-8 ring-1 ring-white/10 backdrop-blur-sm">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl {{ $gaya }}">
                            <x-ikon :nama="$ikon" class="h-7 w-7" />
                        </span>

                        <h3 class="mt-6 font-display text-xl text-white">{{ $item['judul'] }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-hijau-100">{{ $item['deskripsi'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="muncul mt-12 flex flex-col items-center justify-center gap-4 rounded-[2.5rem] bg-white/5 p-8 text-center ring-1 ring-white/10 sm:flex-row sm:text-left">
                <img src="{{ asset('images/kiddies/mother-daughter.png') }}" alt="" class="h-20 w-20 rounded-full object-cover ring-4 ring-white/20" loading="lazy">
                <p class="max-w-xl text-sm leading-relaxed text-hijau-100">
                    Ingin melihat langsung suasana kelas? Kabari kami lewat WhatsApp untuk
                    mengatur waktu kunjungan.
                </p>
                <x-tombol
                    :href="wa_link('Assalamu\'alaikum, saya ingin menjadwalkan kunjungan ke '.config('sekolah.nama'))"
                    warna="kuning"
                    ukuran="sm"
                    ikon="chat"
                    :luar="true"
                    class="shrink-0"
                >
                    Jadwalkan Kunjungan
                </x-tombol>
            </div>
        </div>

        <x-awan class="mt-16 text-krem" />
    </div>
</section>
