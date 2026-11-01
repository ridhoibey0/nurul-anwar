<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GaleriUnggahRequest;
use App\Models\Galeri;
use App\Support\Gambar;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GaleriController extends Controller
{
    /** Folder di dalam public/, jadi tidak perlu "php artisan storage:link". */
    private const FOLDER = 'images/galeri';

    public function index(): View
    {
        return view('admin.galeri.index', [
            'daftar' => Galeri::query()->urut()->get(),
        ]);
    }

    public function store(GaleriUnggahRequest $request): RedirectResponse
    {
        $urutan = (int) Galeri::query()->max('urutan');
        $jumlah = 0;

        foreach ($request->file('foto') as $berkas) {
            $nama = $this->simpan($berkas);

            if ($nama === null) {
                continue;
            }

            Galeri::create([
                'gambar' => self::FOLDER.'/'.$nama,
                'urutan' => ++$urutan,
                'tampil' => true,
            ]);

            $jumlah++;
        }

        if ($jumlah === 0) {
            return back()->withErrors([
                'foto' => 'Foto gagal diproses. Pastikan ekstensi GD aktif di server.',
            ]);
        }

        return redirect()
            ->route('admin.galeri.index')
            ->with('sukses', $jumlah.' foto ditambahkan. Beri judul supaya pengunjung tahu isinya.');
    }

    public function update(Request $request, Galeri $galeri): RedirectResponse
    {
        $data = $request->validate(
            ['judul' => ['nullable', 'string', 'max:160']],
            ['judul.max' => 'Judul foto maksimal 160 karakter.'],
        );

        $galeri->update([
            'judul' => $data['judul'] ?: null,
            'tampil' => $request->boolean('tampil'),
        ]);

        return redirect()
            ->route('admin.galeri.index')
            ->with('sukses', 'Foto diperbarui.');
    }

    public function geser(Request $request, Galeri $galeri): RedirectResponse
    {
        $ids = Galeri::query()->urut()->pluck('id')->all();
        $posisi = array_search($galeri->id, $ids, true);

        if ($posisi === false) {
            return redirect()->route('admin.galeri.index');
        }

        $tujuan = $request->input('arah') === 'naik' ? $posisi - 1 : $posisi + 1;

        if ($tujuan < 0 || $tujuan >= count($ids)) {
            return redirect()->route('admin.galeri.index');
        }

        [$ids[$posisi], $ids[$tujuan]] = [$ids[$tujuan], $ids[$posisi]];

        // Tulis ulang nomor urut seluruh daftar, bukan cuma dua yang bertukar,
        // supaya urutannya tetap rapat setelah ada foto yang dihapus.
        DB::transaction(function () use ($ids): void {
            foreach ($ids as $i => $id) {
                Galeri::query()->whereKey($id)->update(['urutan' => $i + 1]);
            }
        });

        return redirect()->route('admin.galeri.index');
    }

    public function destroy(Galeri $galeri): RedirectResponse
    {
        Storage::disk('galeri')->delete(basename($galeri->gambar));
        $galeri->delete();

        return redirect()
            ->route('admin.galeri.index')
            ->with('sukses', 'Foto dihapus.');
    }

    /** @return string|null nama berkas, atau null bila gagal diproses */
    private function simpan(UploadedFile $berkas): ?string
    {
        $nama = now()->format('Ymd').'-'.Str::random(8).'.jpg';
        $tujuan = Storage::disk('galeri')->path($nama);

        // GD menulis langsung ke filesystem, jadi foldernya harus sudah ada.
        File::ensureDirectoryExists(dirname($tujuan));

        if (Gambar::kecilkan($berkas, $tujuan)) {
            return $nama;
        }

        return null;
    }
}
