<section id="beranda" class="relative overflow-hidden bg-hijau-700 pb-0 pt-28 sm:pt-32">
    {{-- Ornamen latar --}}
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="absolute -left-24 top-10 h-72 w-72 rounded-full bg-hijau-600/60 blur-3xl"></div>
        <div class="absolute -right-20 top-40 h-80 w-80 rounded-full bg-hijau-800/70 blur-3xl"></div>
        <img src="{{ asset('images/kiddies/star.svg') }}" alt="" class="absolute left-[8%] top-24 h-8 w-8 opacity-70 sm:h-10 sm:w-10">
        <img src="{{ asset('images/kiddies/star4.svg') }}" alt="" class="absolute right-[10%] top-36 h-10 w-10 opacity-60">
        <img src="{{ asset('images/kiddies/swirl-union.svg') }}" alt="" class="absolute left-[4%] top-1/2 hidden h-16 w-16 opacity-40 lg:block">
        <img src="{{ asset('images/kiddies/dot-small.svg') }}" alt="" class="absolute right-[6%] top-1/2 hidden h-12 w-12 opacity-50 lg:block">
    </div>

    <div class="relative mx-auto w-full max-w-7xl px-5 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-3xl text-center">
            {{-- Lencana ikut hilang bila akreditasi dikosongkan dari panel admin. --}}
            @if (filled(config('sekolah.akreditasi')))
                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-xs font-semibold text-kuning-300 ring-1 ring-white/20">
                    Akreditasi {{ config('sekolah.akreditasi') }}
                </span>
            @endif

            <h1 class="mt-6 font-display text-4xl leading-[1.1] text-white sm:text-5xl lg:text-6xl">
                Tempat anak <span class="text-kuning-400">bermimpi besar</span>
                dan tumbuh ceria setiap hari
            </h1>

            <p class="mx-auto mt-6 max-w-xl text-base leading-relaxed text-hijau-100">
                {{ config('sekolah.nama') }} memadukan nilai-nilai keislaman dengan cara belajar
                yang menyenangkan, agar anak tumbuh percaya diri, mandiri, dan cinta belajar
                sejak usia dini.
            </p>

            <div class="mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <x-tombol :href="route('ppdb.create')" warna="koral" ukuran="lg">
                    Daftar Siswa Baru
                </x-tombol>

                <x-tombol href="#program" warna="garis-putih" ukuran="lg" ikon="chevron-bawah">
                    Lihat Program
                </x-tombol>
            </div>

            <p class="mt-5 text-xs text-hijau-200">
                Pendaftaran tahun ajaran {{ config('ppdb.tahun_ajaran') }}
                @if (config('ppdb.buka'))
                    sedang dibuka
                @else
                    sedang ditutup
                @endif
            </p>
        </div>

        {{-- Foto utama + bingkai dekoratif --}}
        <div class="relative mx-auto mt-14 max-w-3xl sm:mt-16">
            <div class="pointer-events-none absolute -left-4 top-6 hidden h-32 w-28 -rotate-12 overflow-hidden rounded-[2.5rem] bg-langit-400 bayang-kartu sm:block lg:-left-16 lg:h-40 lg:w-36">
                <img src="{{ asset('images/kiddies/kids-b.png') }}" alt="Anak-anak belajar bersama" class="h-full w-full object-cover" loading="lazy">
            </div>

            <div class="pointer-events-none absolute -right-4 top-20 hidden h-32 w-28 rotate-6 overflow-hidden rounded-[2.5rem] bg-kuning-400 bayang-kartu sm:block lg:-right-16 lg:h-40 lg:w-36">
                <img src="{{ asset('images/kiddies/kids-a.png') }}" alt="Anak-anak bermain di kelas" class="h-full w-full object-cover" loading="lazy">
            </div>

            <div class="relative mx-auto aspect-[4/3] w-full max-w-xl overflow-hidden rounded-[3rem] bg-kuning-300 bayang-kartu ring-8 ring-white/10 sm:aspect-[16/10]">
                <img
                    src="{{ asset('images/kiddies/girl-red-thumb.png') }}"
                    alt="Siswa {{ config('sekolah.nama_pendek') }} tersenyum"
                    class="h-full w-full object-cover object-top"
                    fetchpriority="high"
                >
            </div>
        </div>
    </div>

    <x-awan class="mt-16 text-krem" />
</section>
