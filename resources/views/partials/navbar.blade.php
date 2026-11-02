@php
    // Pada halaman depan navbar melayang di atas hero hijau, jadi teksnya putih.
    $transparan = $navTransparan ?? false;
@endphp

<header
    x-data="{ buka: false, turun: false }"
    x-init="turun = window.scrollY > 24"
    @scroll.window="turun = window.scrollY > 24"
    :class="(turun || buka || {{ $transparan ? 'false' : 'true' }})
        ? 'bg-krem/95 backdrop-blur border-b border-hijau-100'
        : 'bg-transparent'"
    class="fixed inset-x-0 top-0 z-50 transition-colors duration-300"
>
    <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-5 py-4 sm:px-6 lg:px-10">
        <a
            href="{{ route('home') }}"
            class="flex items-center gap-2.5"
            :class="(turun || buka || {{ $transparan ? 'false' : 'true' }}) ? 'text-tinta' : 'text-white'"
        >
            <img src="{{ logo_url() }}" alt="" class="h-8 w-8" width="32" height="32">
            <span class="font-display text-xl font-semibold tracking-tight sm:text-2xl">
                {{ config('sekolah.nama_pendek') }}
            </span>
        </a>

        <nav class="hidden items-center gap-8 lg:flex">
            @foreach (config('sekolah.navigasi') as $tautan)
                <a
                    href="{{ $tautan['url'] }}"
                    class="text-sm font-semibold transition-opacity hover:opacity-70"
                    :class="(turun || {{ $transparan ? 'false' : 'true' }}) ? 'text-tinta' : 'text-white'"
                >{{ $tautan['label'] }}</a>
            @endforeach

            <a
                href="{{ route('ppdb.cek') }}"
                class="text-sm font-semibold transition-opacity hover:opacity-70"
                :class="(turun || {{ $transparan ? 'false' : 'true' }}) ? 'text-tinta' : 'text-white'"
            >Cek Status</a>
        </nav>

        <div class="hidden lg:block">
            <x-tombol :href="route('ppdb.create')" warna="koral" ukuran="sm">
                Daftar Sekarang
            </x-tombol>
        </div>

        <button
            type="button"
            @click="buka = ! buka"
            :aria-expanded="buka.toString()"
            aria-label="Buka menu navigasi"
            class="flex h-11 w-11 items-center justify-center rounded-full transition-colors lg:hidden"
            :class="(turun || buka || {{ $transparan ? 'false' : 'true' }})
                ? 'text-tinta hover:bg-hijau-100'
                : 'text-white hover:bg-white/15'"
        >
            <x-ikon nama="menu" x-show="! buka" class="h-6 w-6" />
            <x-ikon nama="tutup" x-show="buka" x-cloak class="h-6 w-6" />
        </button>
    </div>

    <div
        x-show="buka"
        x-cloak
        x-transition.origin.top
        class="border-t border-hijau-100 bg-krem px-5 pb-6 pt-3 lg:hidden"
    >
        <nav class="flex flex-col gap-1">
            @foreach (config('sekolah.navigasi') as $tautan)
                <a
                    href="{{ $tautan['url'] }}"
                    @click="buka = false"
                    class="rounded-2xl px-4 py-3 text-sm font-semibold text-tinta hover:bg-hijau-100"
                >{{ $tautan['label'] }}</a>
            @endforeach

            <a
                href="{{ route('ppdb.cek') }}"
                @click="buka = false"
                class="rounded-2xl px-4 py-3 text-sm font-semibold text-tinta hover:bg-hijau-100"
            >Cek Status Pendaftaran</a>

            <x-tombol :href="route('ppdb.create')" warna="koral" class="mt-3 w-full">
                Daftar Sekarang
            </x-tombol>
        </nav>
    </div>
</header>

<style>[x-cloak] { display: none !important; }</style>
