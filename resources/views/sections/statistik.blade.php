<section class="bg-krem pb-16 pt-6 sm:pb-24">
    <div class="mx-auto w-full max-w-7xl px-5 sm:px-6 lg:px-10">
        @php
            // Jumlah kolom mengikuti jumlah kotak. Akreditasi bisa dikosongkan
            // dari panel admin, dan kotak yang hilang tidak boleh meninggalkan
            // sel menganga di kanan.
            $kolom = match (count($statistik)) {
                1 => 'lg:grid-cols-1',
                2 => 'lg:grid-cols-2',
                3 => 'lg:grid-cols-3',
                default => 'lg:grid-cols-4',
            };
        @endphp

        <dl class="muncul grid grid-cols-2 gap-6 {{ $kolom }}">
            @foreach ($statistik as $item)
                <div class="rounded-[2rem] bg-white px-6 py-8 text-center bayang-lembut">
                    <dt class="sr-only">{{ $item['label'] }}</dt>
                    <dd>
                        <span class="block font-display text-4xl font-semibold text-hijau-700 sm:text-5xl">
                            {{ $item['angka'] }}
                        </span>
                        <span class="mt-2 block text-sm text-tinta-lembut">{{ $item['label'] }}</span>
                    </dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>
