@if ($galeri->isNotEmpty())
    <section id="keseharian" class="bg-krem py-16 sm:py-24">
        <div class="mx-auto w-full max-w-7xl px-5 sm:px-6 lg:px-10">
            <x-judul
                rintisan="Sehari di sekolah"
                teks="Foto-foto kegiatan anak-anak di {{ config('sekolah.nama') }}."
                class="muncul"
            >
                Keseharian anak di <span class="text-koral-500">{{ config('sekolah.nama_pendek') }}</span>
            </x-judul>

            <div class="kolase muncul mt-14">
                @foreach ($galeri as $foto)
                    <figure class="group relative overflow-hidden rounded-[2rem] bg-hijau-200 bayang-lembut">
                        <img
                            src="{{ $foto->gambar_url }}"
                            alt="{{ $foto->alt }}"
                            class="h-full min-h-40 w-full object-cover transition-transform duration-500 group-hover:scale-105"
                            loading="lazy"
                        >

                        @if ($foto->judul)
                            <figcaption class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-hijau-900/85 to-transparent p-4 pt-10">
                                <p class="font-display text-sm text-white sm:text-base">{{ $foto->judul }}</p>
                            </figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        </div>
    </section>
@endif
