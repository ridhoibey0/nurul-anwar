@props(['status'])

@php
    $warna = config("ppdb.status.{$status}.warna", 'slate');
    $label = config("ppdb.status.{$status}.label", $status);

    $gaya = match ($warna) {
        'amber' => 'bg-kuning-100 text-kuning-500',
        'sky' => 'bg-langit-200 text-hijau-800',
        'green' => 'bg-hijau-100 text-hijau-700',
        'rose' => 'bg-koral-100 text-koral-600',
        default => 'bg-krem-tua text-tinta-lembut',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold '.$gaya]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
    {{ $label }}
</span>
