<section id="program" class="bg-krem py-16 sm:py-24">
    <div class="mx-auto w-full max-w-7xl px-5 sm:px-6 lg:px-10">
        <div class="muncul flex flex-col items-start justify-between gap-6 lg:flex-row lg:items-end">
            <x-judul rintisan="Program & jenjang" rata="kiri" class="max-w-2xl">
                Program yang disesuaikan dengan <span class="text-koral-500">tiap tahap</span> tumbuh kembang
            </x-judul>

            <x-tombol :href="route('ppdb.info')" warna="garis" ukuran="sm" class="shrink-0">
                Lihat kuota & syarat
            </x-tombol>
        </div>

        <div class="muncul mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($jenjang as $item)
                @php
                    $gaya = match ($item['warna']) {
                        'kuning' => ['kartu' => 'bg-kuning-300', 'pil' => 'bg-kuning-500/20 text-kuning-500', 'teks' => 'text-tinta'],
                        'hijau' => ['kartu' => 'bg-hijau-700', 'pil' => 'bg-white/15 text-kuning-300', 'teks' => 'text-white'],
                        'koral' => ['kartu' => 'bg-koral-100', 'pil' => 'bg-koral-500/15 text-koral-600', 'teks' => 'text-tinta'],
                        default => ['kartu' => 'bg-langit-200', 'pil' => 'bg-hijau-700/10 text-hijau-700', 'teks' => 'text-tinta'],
                    };
                @endphp

                <article class="flex flex-col overflow-hidden rounded-[2.5rem] {{ $gaya['kartu'] }} bayang-lembut transition-transform duration-300 hover:-translate-y-1">
                    <div class="aspect-[4/3] overflow-hidden rounded-[2rem] bg-white/30 p-0">
                        <img
                            src="{{ asset($item['gambar']) }}"
                            alt="{{ $item['nama'] }}"
                            class="h-full w-full object-cover"
                            loading="lazy"
                        >
                    </div>

                    <div class="flex flex-1 flex-col p-6">
                        <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $gaya['pil'] }}">
                            {{ $item['usia'] }}
                        </span>

                        <h3 class="mt-3 font-display text-xl {{ $gaya['teks'] }}">{{ $item['nama'] }}</h3>

                        <p class="mt-2 flex-1 text-sm leading-relaxed {{ $gaya['teks'] }} opacity-80">
                            {{ $item['deskripsi'] }}
                        </p>

                        <a
                            href="{{ route('ppdb.create') }}"
                            class="mt-5 inline-flex text-sm font-semibold {{ $gaya['teks'] }} underline-offset-4 hover:underline"
                        >
                            Daftar jenjang ini
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
