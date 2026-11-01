<span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-hijau-700 text-white">
    <x-ikon :nama="$kontak['ikon']" class="h-5 w-5" />
</span>

<div>
    <p class="teks-rintisan text-[10px] text-tinta-lembut">{{ $kontak['label'] }}</p>
    <p class="mt-1 text-sm font-semibold leading-relaxed text-tinta sm:text-base">{{ $kontak['nilai'] }}</p>
</div>
