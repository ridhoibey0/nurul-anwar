@if ($artikel->isNotEmpty())
    <section id="blog" class="bg-krem py-16 sm:py-24">
        <div class="mx-auto w-full max-w-7xl px-5 sm:px-6 lg:px-10">
            <div class="muncul flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-end">
                <x-judul rintisan="Dari blog" rata="kiri">
                    Bacaan ringan untuk <span class="text-koral-500">orang tua</span>
                </x-judul>

                <x-tombol
                    :href="route('blog.index')"
                    warna="garis"
                    ukuran="sm"
                    class="shrink-0"
                >
                    Semua artikel
                </x-tombol>
            </div>

            <div class="muncul mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($artikel as $i => $tulisan)
                    <x-kartu-artikel :artikel="$tulisan" :urutan="$i" />
                @endforeach
            </div>
        </div>
    </section>
@endif
