@props([
    'href' => null,
    'warna' => 'koral',
    'ukuran' => 'md',
    'ikon' => null,
    'luar' => false,
])

@php
    $gayaWarna = match ($warna) {
        'koral' => 'bg-koral-500 text-white hover:bg-koral-600 bayang-timbul',
        'hijau' => 'bg-hijau-700 text-white hover:bg-hijau-800 bayang-kartu',
        'putih' => 'bg-white text-hijau-700 hover:bg-hijau-50 bayang-kartu',
        'kuning' => 'bg-kuning-400 text-tinta hover:bg-kuning-500 bayang-kartu',
        'garis' => 'border-2 border-hijau-700 bg-transparent text-hijau-700 hover:bg-hijau-700 hover:text-white',
        'garis-putih' => 'border-2 border-white/70 bg-transparent text-white hover:bg-white hover:text-hijau-800',
        default => 'bg-koral-500 text-white hover:bg-koral-600',
    };

    $gayaUkuran = match ($ukuran) {
        'sm' => 'px-5 py-2.5 text-sm',
        'lg' => 'px-8 py-4 text-base',
        default => 'px-6 py-3 text-sm',
    };

    $kelas = "inline-flex items-center justify-center gap-2 rounded-full font-display font-medium transition-all duration-200 hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-koral-500 {$gayaWarna} {$gayaUkuran}";
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        @if ($luar) target="_blank" rel="noopener noreferrer" @endif
        {{ $attributes->merge(['class' => $kelas]) }}
    >
        {{ $slot }}
        @if ($ikon)
            <x-ikon :nama="$ikon" class="h-4 w-4 shrink-0" />
        @endif
    </a>
@else
    <button {{ $attributes->merge(['class' => $kelas, 'type' => 'submit']) }}>
        {{ $slot }}
        @if ($ikon)
            <x-ikon :nama="$ikon" class="h-4 w-4 shrink-0" />
        @endif
    </button>
@endif
