@props([
    'nama',
    'label',
    'wajib' => false,
    'bantuan' => null,
    'terima' => '.jpg,.jpeg,.png,.pdf',
    'keterangan' => null,
])

{{--
    Input berkas dengan nama file yang tampil setelah dipilih, agar orang tua
    yakin berkasnya sudah terpasang sebelum mengirim formulir.
--}}

<div x-data="{ namaBerkas: '' }">
    <label for="{{ $nama }}" class="block text-sm font-semibold text-tinta">
        {{ $label }}
        @if ($wajib)
            <span class="text-koral-500" aria-hidden="true">*</span>
        @endif
    </label>

    @if ($bantuan)
        <p class="mt-0.5 text-xs text-tinta-lembut">{{ $bantuan }}</p>
    @endif

    <label
        for="{{ $nama }}"
        class="relative mt-2 flex cursor-pointer items-center gap-3 rounded-2xl border-2 border-dashed bg-white px-4 py-4 transition-colors hover:border-hijau-400 focus-within:border-hijau-400 {{ $errors->has($nama) ? 'border-koral-500' : 'border-hijau-200' }}"
    >
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-hijau-100 text-hijau-700">
            <x-ikon nama="berkas" class="h-5 w-5" />
        </span>

        <span class="min-w-0 flex-1">
            <span class="block truncate text-sm font-semibold text-tinta" x-text="namaBerkas || 'Pilih berkas'"></span>
            <span class="block text-xs text-tinta-lembut">
                {{ $keterangan ?? 'JPG, PNG, atau PDF · maksimal '.round(config('ppdb.berkas.maks_kb') / 1024, 1).' MB' }}
            </span>
        </span>

        <input
            id="{{ $nama }}"
            name="{{ $nama }}"
            type="file"
            accept="{{ $terima }}"
            {{-- Transparan, bukan sr-only, agar peramban tetap bisa menyorotnya saat validasi gagal. --}}
            class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
            @if ($wajib) required @endif
            @change="namaBerkas = $event.target.files[0]?.name ?? ''"
        >
    </label>

    @error($nama)
        <p class="mt-1.5 flex items-start gap-1.5 text-xs text-koral-600">
            <x-ikon nama="peringatan" class="mt-px h-3.5 w-3.5 shrink-0" />
            {{ $message }}
        </p>
    @enderror
</div>
