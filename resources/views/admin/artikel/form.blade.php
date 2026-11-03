@extends('layouts.admin')

@section('title', $artikel->exists ? 'Ubah Artikel' : 'Tulis Artikel')

@push('kepala')
    @vite('resources/js/editor.js')
@endpush

@section('content')
    @php
        $aksi = $artikel->exists
            ? route('admin.artikel.update', $artikel)
            : route('admin.artikel.store');
    @endphp

    <form method="POST" action="{{ $aksi }}" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-[1.7fr_1fr] lg:items-start">
        @csrf
        @if ($artikel->exists)
            @method('PUT')
        @endif

        {{-- Isi tulisan --}}
        <div class="grid gap-6">
            <section class="rounded-[1.75rem] bg-white p-7 bayang-lembut">
                <div class="grid gap-5">
                    <x-form.input
                        nama="judul"
                        label="Judul artikel"
                        :nilai="$artikel->judul"
                        wajib
                        maxlength="200"
                        autocomplete="off"
                    />

                    <x-form.input
                        nama="kategori"
                        label="Kategori"
                        :nilai="$artikel->kategori"
                        bantuan="Opsional, mis. Tips Parenting atau Kegiatan Sekolah."
                        maxlength="80"
                        list="daftar-kategori"
                        autocomplete="off"
                    />

                    <datalist id="daftar-kategori">
                        <option value="Kegiatan Sekolah"></option>
                        <option value="Tips Parenting"></option>
                        <option value="Tumbuh Kembang"></option>
                        <option value="Persiapan Sekolah"></option>
                        <option value="Pengumuman"></option>
                    </datalist>

                    <x-form.area
                        nama="ringkasan"
                        label="Ringkasan"
                        :baris="3"
                        bantuan="Tampil di kartu blog dan hasil pencarian Google. Maksimal 400 karakter."
                        wajib
                        maxlength="400"
                        :nilai="$artikel->ringkasan"
                    />
                </div>
            </section>

            <section class="rounded-[1.75rem] bg-white p-7 bayang-lembut">
                <x-form.wysiwyg
                    nama="isi"
                    label="Isi artikel"
                    bantuan="Pakai tombol di atas kotak untuk menebalkan, membuat subjudul, atau menambah daftar."
                    wajib
                    :nilai="$artikel->isi_html"
                />

                <div class="mt-4 rounded-2xl bg-krem p-5 text-xs leading-relaxed text-tinta-lembut">
                    <p class="font-semibold text-tinta">Yang perlu diketahui</p>
                    <ul class="mt-2 grid list-disc gap-1 pl-4">
                        <li>Menempel tulisan dari Word atau WhatsApp boleh &mdash; format yang tidak dikenal dibuang otomatis.</li>
                        <li>Gambar tidak bisa disisipkan di tengah tulisan. Pakai isian <strong>Gambar sampul</strong> di samping.</li>
                        <li>Tautan keluar otomatis dibuka di tab baru.</li>
                    </ul>
                </div>
            </section>
        </div>

        {{-- Panel samping --}}
        <div class="grid gap-6">
            <section class="rounded-[1.75rem] bg-white p-7 bayang-lembut">
                <h2 class="font-display text-base text-tinta">Penerbitan</h2>

                <div class="mt-5 grid gap-5">
                    <x-form.pilihan
                        nama="status"
                        label="Status"
                        :opsi="['draf' => 'Draf — belum tampil di website', 'terbit' => 'Terbit — tampil di website']"
                        :nilai="$artikel->status ?: 'draf'"
                        :kosong="false"
                        wajib
                    />

                    <x-form.input
                        nama="terbit_pada"
                        label="Tanggal terbit"
                        tipe="date"
                        :nilai="$artikel->terbit_pada?->toDateString()"
                        bantuan="Kosongkan untuk terbit sekarang. Tanggal di masa depan membuat artikel terjadwal."
                    />
                </div>

                <x-tombol type="submit" warna="koral" ikon="centang" class="mt-6 w-full">
                    {{ $artikel->exists ? 'Simpan perubahan' : 'Simpan artikel' }}
                </x-tombol>

                <a href="{{ route('admin.artikel.index') }}" class="mt-3 block text-center text-xs font-semibold text-tinta-lembut hover:text-hijau-700">
                    Batal
                </a>
            </section>

            <section class="rounded-[1.75rem] bg-white p-7 bayang-lembut">
                <h2 class="font-display text-base text-tinta">Gambar sampul</h2>

                @if ($artikel->gambar_url)
                    <img
                        src="{{ $artikel->gambar_url }}"
                        alt="Sampul {{ $artikel->judul }}"
                        class="mt-4 aspect-[16/10] w-full rounded-2xl object-cover"
                    >

                    <label class="mt-3 flex items-center gap-2 text-xs text-tinta-lembut">
                        <input type="checkbox" name="hapus_gambar" value="1" class="h-4 w-4 accent-koral-500">
                        Hapus gambar ini saat menyimpan
                    </label>
                @endif

                <div class="mt-4">
                    <x-form.berkas
                        nama="gambar"
                        :label="$artikel->gambar_url ? 'Ganti gambar' : 'Unggah gambar'"
                        terima=".jpg,.jpeg,.png,.webp"
                        keterangan="JPG, PNG, atau WEBP · maksimal 2 MB"
                    />
                </div>

                <p class="mt-3 text-xs text-tinta-lembut">
                    Ukuran paling pas sekitar 1200 &times; 750 piksel. Tanpa gambar, kartu artikel
                    memakai blok warna berlogo sekolah.
                </p>
            </section>

        </div>
    </form>

    {{-- Di luar form utama: form tidak boleh bersarang. --}}
    @if ($artikel->exists)
        <section class="mt-6 flex flex-wrap items-center gap-4 rounded-[1.75rem] border-2 border-koral-100 bg-white p-7">
            <div class="min-w-0 flex-1">
                <h2 class="font-display text-base text-tinta">Hapus artikel</h2>
                <p class="mt-1 text-xs text-tinta-lembut">
                    Artikel dan gambar sampulnya dihapus permanen. Tautan yang sudah dibagikan
                    ke orang tua akan mati. Untuk menyembunyikan sementara, ubah statusnya ke draf saja.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('admin.artikel.destroy', $artikel) }}"
                onsubmit="return confirm('Hapus artikel ini beserta gambarnya? Tindakan ini tidak bisa dibatalkan.')"
            >
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-full border-2 border-koral-500 px-5 py-2.5 text-sm font-semibold text-koral-600 transition-colors hover:bg-koral-500 hover:text-white"
                >
                    <x-ikon nama="sampah" class="h-4 w-4" />
                    Hapus artikel
                </button>
            </form>
        </section>
    @endif
@endsection
