@if (! empty($testimoni))
<section class="bg-krem py-16 sm:py-24">
    <div class="mx-auto grid w-full max-w-7xl items-center gap-14 px-5 sm:px-6 lg:grid-cols-[0.9fr_1.1fr] lg:gap-20 lg:px-10">
        <div class="muncul relative mx-auto w-full max-w-sm">
            <div class="aspect-[4/5] overflow-hidden blob-a bg-hijau-200 bayang-kartu">
                <img src="{{ asset('images/kiddies/mother-daughter.png') }}" alt="Ibu dan anak" class="h-full w-full object-cover" loading="lazy">
            </div>
        </div>

        <div class="muncul">
            <x-judul rintisan="Kata orang tua" rata="kiri">
                Disayangi anak, <span class="text-koral-500">dipercaya</span> orang tua
            </x-judul>

            <p class="mt-5 max-w-xl text-base leading-relaxed text-tinta-lembut">
                Kepercayaan orang tua adalah hal yang paling kami jaga di setiap proses
                belajar anak.
            </p>

            <div class="mt-10 grid gap-5 sm:grid-cols-2">
                @foreach ($testimoni as $item)
                    <figure class="rounded-[2rem] bg-white p-7 bayang-lembut">
                        <blockquote class="text-sm leading-relaxed text-tinta">
                            &ldquo;{{ $item['kutipan'] }}&rdquo;
                        </blockquote>

                        <figcaption class="mt-6 flex items-center gap-3">
                            @if (! empty($item['avatar']))
                                <img src="{{ asset($item['avatar']) }}" alt="" class="h-10 w-10 rounded-full object-cover" loading="lazy">
                            @endif
                            <div>
                                <p class="text-sm font-semibold text-tinta">{{ $item['nama'] }}</p>
                                <p class="text-xs text-tinta-lembut">{{ $item['peran'] }}</p>
                            </div>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
