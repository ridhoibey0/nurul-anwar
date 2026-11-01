<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArtikelRequest;
use App\Models\Artikel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArtikelAdminController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.artikel.index', [
            'daftar' => Artikel::query()
                ->with('penulis')
                ->cari($request->string('q')->toString())
                ->status($request->string('status')->toString())
                ->orderByDesc('id')
                ->paginate(15)
                ->withQueryString(),
            'filter' => $request->only(['q', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('admin.artikel.form', [
            'artikel' => new Artikel(['status' => 'draf']),
        ]);
    }

    public function store(ArtikelRequest $request): RedirectResponse
    {
        $data = $this->siapkanData($request);

        $data['slug'] = Artikel::buatSlug($data['judul']);
        $data['penulis_id'] = $request->user()->id;

        if ($berkas = $request->file('gambar')) {
            $data['gambar'] = $this->simpanGambar($berkas, $data['slug']);
        }

        $artikel = Artikel::create($data);

        return redirect()
            ->route('admin.artikel.index')
            ->with('sukses', 'Artikel "'.$artikel->judul.'" tersimpan sebagai '.strtolower($artikel->status_label).'.');
    }

    public function edit(Artikel $artikel): View
    {
        return view('admin.artikel.form', ['artikel' => $artikel]);
    }

    public function update(ArtikelRequest $request, Artikel $artikel): RedirectResponse
    {
        $data = $this->siapkanData($request);

        // Slug hanya ikut berubah selama artikel belum pernah terbit, supaya
        // tautan yang sudah dibagikan ke orang tua tidak mati.
        if (! $artikel->sudah_terbit) {
            $data['slug'] = Artikel::buatSlug($data['judul'], $artikel->id);
        }

        if ($berkas = $request->file('gambar')) {
            $this->hapusGambar($artikel);
            $data['gambar'] = $this->simpanGambar($berkas, $data['slug'] ?? $artikel->slug);
        } elseif ($request->boolean('hapus_gambar')) {
            $this->hapusGambar($artikel);
            $data['gambar'] = null;
        }

        $artikel->update($data);

        return redirect()
            ->route('admin.artikel.index')
            ->with('sukses', 'Artikel "'.$artikel->judul.'" diperbarui.');
    }

    public function destroy(Artikel $artikel): RedirectResponse
    {
        $judul = $artikel->judul;

        $this->hapusGambar($artikel);
        $artikel->delete();

        return redirect()
            ->route('admin.artikel.index')
            ->with('sukses', 'Artikel "'.$judul.'" dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function siapkanData(ArtikelRequest $request): array
    {
        $data = $request->safe()->only(['judul', 'kategori', 'ringkasan', 'isi', 'status', 'terbit_pada']);

        if ($data['status'] === 'terbit') {
            // Dibiarkan kosong berarti terbit sekarang.
            $data['terbit_pada'] = ($data['terbit_pada'] ?? null) ?: now();
        } else {
            $data['terbit_pada'] = null;
        }

        return $data;
    }

    private function simpanGambar(UploadedFile $berkas, string $slug): string
    {
        $nama = Str::limit($slug, 60, '').'-'.Str::random(6).'.'.$berkas->extension();

        $berkas->storeAs('', $nama, 'artikel');

        // Path relatif terhadap public/, siap dipakai asset().
        return 'images/artikel/'.$nama;
    }

    private function hapusGambar(Artikel $artikel): void
    {
        if (! $artikel->gambar) {
            return;
        }

        Storage::disk('artikel')->delete(basename($artikel->gambar));
    }
}
