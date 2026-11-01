@props([
    'rintisan' => null,
    'teks' => null,
    'rata' => 'tengah',
    'gelap' => false,
])

@php
    $kelasWadah = $rata === 'tengah' ? 'mx-auto max-w-2xl text-center' : 'max-w-xl';
    $warnaJudul = $gelap ? 'text-white' : 'text-tinta';
    $warnaTeks = $gelap ? 'text-hijau-100' : 'text-tinta-lembut';
    $warnaRintisan = $gelap ? 'text-kuning-300' : 'text-koral-500';
@endphp

<div {{ $attributes->merge(['class' => $kelasWadah]) }}>
    @if ($rintisan)
        <p class="teks-rintisan text-xs {{ $warnaRintisan }}">{{ $rintisan }}</p>
    @endif

    <h2 class="mt-3 font-display text-3xl leading-tight {{ $warnaJudul }} sm:text-4xl">
        {{ $slot }}
    </h2>

    @if ($teks)
        <p class="mt-4 text-base leading-relaxed {{ $warnaTeks }}">{{ $teks }}</p>
    @endif
</div>
