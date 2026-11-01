@props([
    'nama',
    'label',
    'tipe' => 'text',
    'wajib' => false,
    'bantuan' => null,
    'nilai' => null,
])

<div>
    <label for="{{ $nama }}" class="block text-sm font-semibold text-tinta">
        {{ $label }}
        @if ($wajib)
            <span class="text-koral-500" aria-hidden="true">*</span>
        @endif
    </label>

    @if ($bantuan)
        <p class="mt-0.5 text-xs text-tinta-lembut">{{ $bantuan }}</p>
    @endif

    <input
        id="{{ $nama }}"
        name="{{ $nama }}"
        type="{{ $tipe }}"
        value="{{ old($nama, $nilai) }}"
        @if ($wajib) required @endif
        @error($nama) aria-invalid="true" aria-describedby="{{ $nama }}-galat" @enderror
        {{ $attributes->merge([
            'class' => 'mt-2 block w-full rounded-2xl border-2 bg-white px-4 py-3 text-sm text-tinta transition-colors placeholder:text-tinta-lembut/60 focus:outline-none focus:ring-0 '
                .($errors->has($nama) ? 'border-koral-500' : 'border-hijau-100 focus:border-hijau-400'),
        ]) }}
    >

    @error($nama)
        <p id="{{ $nama }}-galat" class="mt-1.5 flex items-start gap-1.5 text-xs text-koral-600">
            <x-ikon nama="peringatan" class="mt-px h-3.5 w-3.5 shrink-0" />
            {{ $message }}
        </p>
    @enderror
</div>
