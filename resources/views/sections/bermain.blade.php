<section class="bg-krem py-16 sm:py-24">
    <div class="mx-auto grid w-full max-w-7xl items-center gap-14 px-5 sm:px-6 lg:grid-cols-2 lg:gap-20 lg:px-10">
        {{-- Kolase foto --}}
        <div class="muncul relative order-last lg:order-first">
            <div class="grid grid-cols-2 gap-4 sm:gap-5">
                <div class="space-y-4 sm:space-y-5">
                    <div class="aspect-[3/4] overflow-hidden rounded-[2.5rem] bg-hijau-200 bayang-lembut">
                        <img src="{{ asset('images/kiddies/girl-scissors.png') }}" alt="Anak berkreasi dengan gunting dan kertas" class="h-full w-full object-cover" loading="lazy">
                    </div>
                    <div class="aspect-square overflow-hidden rounded-[2rem] bg-kuning-300 bayang-lembut">
                        <img src="{{ asset('images/kiddies/boy-pink.png') }}" alt="Anak tersenyum di kelas" class="h-full w-full object-cover" loading="lazy">
                    </div>
                </div>

                <div class="space-y-4 pt-10 sm:space-y-5">
                    <div class="aspect-square overflow-hidden rounded-[2rem] bg-langit-200 bayang-lembut">
                        <img src="{{ asset('images/kiddies/kids-a.png') }}" alt="Anak-anak bermain bersama" class="h-full w-full object-cover" loading="lazy">
                    </div>
                    <div class="aspect-[3/4] overflow-hidden rounded-[2.5rem] bg-koral-100 bayang-lembut">
                        <img src="{{ asset('images/kiddies/teacher-reading.png') }}" alt="Guru membacakan cerita" class="h-full w-full object-cover" loading="lazy">
                    </div>
                </div>
            </div>

            <img src="{{ asset('images/kiddies/star2.svg') }}" alt="" class="pointer-events-none absolute -left-6 top-1/3 hidden h-14 w-14 lg:block" aria-hidden="true">
        </div>

        {{-- Teks --}}
        <div class="muncul">
            <x-judul rintisan="Cara kami mengajar" rata="kiri">
                Belajar lewat bermain yang <span class="text-koral-500">penuh tawa</span>
            </x-judul>

            <p class="mt-5 max-w-xl text-base leading-relaxed text-tinta-lembut">
                Kegiatan disusun bertahap supaya anak berani mencoba, percaya diri
                mengambil keputusan kecil, dan terbiasa menyelesaikan tugasnya sendiri
                tanpa merasa sedang disuruh belajar.
            </p>

            <ul class="mt-8 grid gap-4 sm:grid-cols-2">
                @foreach ([
                    ['judul' => 'Belajar Berbasis Sentra', 'teks' => 'Anak memilih sentra sesuai minat: balok, peran, seni, atau bahan alam.'],
                    ['judul' => 'Pembiasaan Islami', 'teks' => 'Doa harian, hafalan surat pendek, dan adab yang dibawa pulang.'],
                ] as $poin)
                    <li class="flex gap-3 rounded-[1.5rem] bg-white p-5 bayang-lembut">
                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-hijau-100 text-hijau-700">
                            <x-ikon nama="centang" class="h-4 w-4" />
                        </span>
                        <div>
                            <p class="font-display text-base font-semibold text-tinta">{{ $poin['judul'] }}</p>
                            <p class="mt-1 text-sm leading-relaxed text-tinta-lembut">{{ $poin['teks'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>

            <x-tombol :href="route('ppdb.info')" warna="hijau" class="mt-9">
                Lihat Informasi PPDB
            </x-tombol>
        </div>
    </div>
</section>
