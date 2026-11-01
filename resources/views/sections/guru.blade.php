<section id="guru" class="bg-krem py-16 sm:py-24">
    <div class="mx-auto w-full max-w-7xl px-5 sm:px-6 lg:px-10">
        <div class="rounded-[3rem] bg-white px-6 py-14 bayang-lembut sm:px-12">
            <x-judul
                rintisan="Tim pendidik"
                teks="Satu kepala RA dan lima guru yang mendampingi anak-anak setiap hari."
                class="muncul"
            >
                Guru yang <span class="text-koral-500">sabar</span> dan telaten
            </x-judul>

            <div class="muncul mt-14 grid grid-cols-2 gap-8 sm:grid-cols-3 lg:grid-cols-6">
                @foreach ($guru as $i => $orang)
                    @php
                        // Selama foto guru belum tersedia, tampilkan inisial nama.
                        $inisial = collect(explode(' ', $orang['nama']))
                            ->take(2)
                            ->map(fn (string $kata): string => mb_strtoupper(mb_substr($kata, 0, 1)))
                            ->implode('');

                        $latar = [
                            'bg-hijau-100 text-hijau-700',
                            'bg-kuning-100 text-koral-600',
                            'bg-koral-100 text-koral-600',
                            'bg-langit-200 text-hijau-800',
                        ][$i % 4];
                    @endphp

                    <div class="flex flex-col items-center text-center">
                        <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full ring-4 ring-hijau-200 sm:h-28 sm:w-28 {{ $orang['gambar'] ? 'bg-hijau-100' : $latar }}">
                            @if ($orang['gambar'])
                                <img src="{{ asset($orang['gambar']) }}" alt="{{ $orang['nama'] }}" class="h-full w-full object-cover" loading="lazy">
                            @else
                                <span class="font-display text-2xl sm:text-3xl" aria-hidden="true">{{ $inisial }}</span>
                            @endif
                        </div>

                        <p class="mt-4 font-display text-sm leading-snug text-tinta sm:text-base">{{ $orang['nama'] }}</p>
                        <p class="mt-1 text-xs text-tinta-lembut sm:text-sm">{{ $orang['peran'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
