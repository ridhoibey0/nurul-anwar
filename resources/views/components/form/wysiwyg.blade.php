@props([
    'nama',
    'label',
    'wajib' => false,
    'bantuan' => null,
    'nilai' => null,
])

{{--
    Editor teks dengan tombol format (Trix).

    Trix bekerja berpasangan: <trix-editor> yang dilihat panitia, dan sebuah
    input tersembunyi yang isinya ikut terkirim saat formulir disubmit. Nama
    isian menempel di input tersembunyi itu, jadi validasi, pesan galat, dan
    old() berjalan seperti isian biasa.

    Isinya dicetak dengan {!! !!} karena memang HTML, dan sudah disaring
    App\Support\HtmlAman saat disimpan.
--}}

@php
    $isi = old($nama, $nilai);
    $idInput = $nama.'_nilai';
@endphp

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

    <input id="{{ $idInput }}" type="hidden" name="{{ $nama }}" value="{{ $isi }}">

    <trix-editor
        id="{{ $nama }}"
        input="{{ $idInput }}"
        @error($nama) aria-invalid="true" aria-describedby="{{ $nama }}-galat" @enderror
        {{ $attributes->merge([
            'class' => 'trix-isi mt-2 block w-full rounded-2xl border-2 bg-white px-4 py-3 text-sm text-tinta '
                .($errors->has($nama) ? 'border-koral-500' : 'border-hijau-100'),
        ]) }}
    ></trix-editor>

    @error($nama)
        <p id="{{ $nama }}-galat" class="mt-1.5 flex items-start gap-1.5 text-xs text-koral-600">
            <x-ikon nama="peringatan" class="mt-px h-3.5 w-3.5 shrink-0" />
            {{ $message }}
        </p>
    @enderror
</div>
