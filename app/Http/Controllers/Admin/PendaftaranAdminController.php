<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BerkasPendaftaran;
use App\Models\Jenjang;
use App\Models\OrangTua;
use App\Models\Pendaftaran;
use App\Models\StatusPendaftaran;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PendaftaranAdminController extends Controller
{
    public function index(Request $request): View
    {
        $pendaftaran = Pendaftaran::query()
            // orangTua ikut dimuat karena kolom daftarnya menampilkan nama
            // ayah; tanpa ini tiap baris memicu query tersendiri.
            ->with(['jenjang', 'status', 'orangTua'])
            ->cari($request->string('q')->toString())
            ->status($request->string('status')->toString())
            ->jenjang($request->string('jenjang')->toString())
            ->tahunAjaran($request->string('tahun_ajaran')->toString())
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.pendaftaran.index', [
            'daftar' => $pendaftaran,
            'filter' => $request->only(['q', 'status', 'jenjang', 'tahun_ajaran']),
            'daftarStatus' => StatusPendaftaran::query()->urut()->get(),
            'daftarJenjang' => Jenjang::query()->urut()->get(),
            'tahunTersedia' => Pendaftaran::query()
                ->distinct()
                ->orderByDesc('tahun_ajaran')
                ->pluck('tahun_ajaran'),
        ]);
    }

    public function show(Pendaftaran $pendaftaran): View
    {
        return view('admin.pendaftaran.show', [
            'pendaftaran' => $pendaftaran->load([
                'jenjang', 'status', 'alamat', 'orangTua', 'berkas', 'verifikator',
            ]),
            'daftarStatus' => StatusPendaftaran::query()->urut()->get(),
        ]);
    }

    public function updateStatus(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', Rule::exists('status_pendaftaran', 'kode')],
            'catatan_admin' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'status' => 'status pendaftaran',
            'catatan_admin' => 'catatan panitia',
        ]);

        $pendaftaran->update([
            'status_id' => StatusPendaftaran::dariKode($data['status'])?->id,
            'catatan_admin' => $data['catatan_admin'] ?? null,
            'diverifikasi_pada' => now(),
            'diverifikasi_oleh' => $request->user()->id,
        ]);

        return back()->with('sukses', 'Status pendaftaran '.$pendaftaran->nomor_pendaftaran.' diperbarui.');
    }

    public function destroy(Pendaftaran $pendaftaran): RedirectResponse
    {
        // Baris orang tua dan berkas ikut terhapus lewat cascade di database,
        // tapi berkas fisiknya tidak -- itu harus dihapus sendiri.
        foreach ($pendaftaran->berkas as $berkas) {
            $berkas->hapusBerkas();
        }

        $nomor = $pendaftaran->nomor_pendaftaran;
        $alamat = $pendaftaran->alamat;

        $pendaftaran->delete();

        // Alamat hanya dipakai pendaftaran ini, jadi ikut dibuang supaya tidak
        // tertinggal sebagai baris yatim.
        $alamat?->delete();

        return redirect()
            ->route('admin.pendaftaran.index')
            ->with('sukses', 'Pendaftaran '.$nomor.' beserta berkasnya telah dihapus.');
    }

    /**
     * Mengunduh berkas yang diunggah pendaftar.
     *
     * Berkas disimpan di disk privat, jadi hanya bisa diakses lewat route ini
     * yang sudah berada di belakang middleware auth.
     */
    public function berkas(Pendaftaran $pendaftaran, string $jenis): BinaryFileResponse
    {
        abort_unless(array_key_exists($jenis, BerkasPendaftaran::JENIS), 404);

        $berkas = $pendaftaran->berkasJenis($jenis);

        abort_if($berkas === null || ! $berkas->ada(), 404);

        return response()->file(Storage::disk('local')->path($berkas->path));
    }

    /**
     * Ekspor hasil filter saat ini ke CSV (bisa dibuka di Excel).
     */
    public function export(Request $request): StreamedResponse
    {
        $query = Pendaftaran::query()
            // Tanpa ini tiap baris memicu query tersendiri untuk jenjang,
            // status, alamat, dan orang tuanya.
            ->lengkap()
            ->cari($request->string('q')->toString())
            ->status($request->string('status')->toString())
            ->jenjang($request->string('jenjang')->toString())
            ->tahunAjaran($request->string('tahun_ajaran')->toString())
            ->latest();

        $judul = [
            'Nomor Pendaftaran', 'Tahun Ajaran', 'Jenjang', 'Status',
            'Nama Lengkap', 'Nama Panggilan', 'Jenis Kelamin',
            'Tempat Lahir', 'Tanggal Lahir', 'Usia', 'NIK Anak',
            'Anak Ke', 'Jumlah Saudara',
            'Alamat', 'Kelurahan', 'Kecamatan', 'Kota', 'Kode Pos',
            'Asal Sekolah',
            'Nama Ayah', 'Pekerjaan Ayah', 'Telepon Ayah',
            'Nama Ibu', 'Pekerjaan Ibu', 'Telepon Ibu',
            'Nama Wali', 'Hubungan Wali', 'Telepon Wali',
            'WhatsApp', 'Email', 'Catatan Panitia',
        ];

        $tahunFile = $request->string('tahun_ajaran')->toString() ?: config('ppdb.tahun_ajaran');
        $namaFile = 'ppdb-'.str_replace('/', '-', $tahunFile).'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query, $judul): void {
            $keluaran = fopen('php://output', 'w');

            // BOM agar karakter Indonesia tampil benar saat dibuka di Excel.
            fwrite($keluaran, "\xEF\xBB\xBF");

            fputcsv($keluaran, $judul, ';', '"', '');

            $query->chunk(200, function ($baris) use ($keluaran): void {
                foreach ($baris as $item) {
                    fputcsv($keluaran, $this->barisCsv($item), ';', '"', '');
                }
            });

            fclose($keluaran);
        }, $namaFile, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function barisCsv(Pendaftaran $p): array
    {
        $ayah = $p->orangTuaPeran(OrangTua::AYAH);
        $ibu = $p->orangTuaPeran(OrangTua::IBU);
        $wali = $p->orangTuaPeran(OrangTua::WALI);

        return array_map(static fn ($isi): string => (string) ($isi ?? ''), [
            $p->nomor_pendaftaran,
            $p->tahun_ajaran,
            $p->jenjang_label,
            $p->status_label,
            $p->nama_lengkap,
            $p->nama_panggilan,
            $p->jenis_kelamin_label,
            $p->tempat_lahir,
            $p->tanggal_lahir?->format('Y-m-d'),
            $p->usia,
            $p->nik,
            $p->anak_ke,
            $p->jumlah_saudara,
            $p->alamat?->jalan,
            $p->alamat?->kelurahan,
            $p->alamat?->kecamatan,
            $p->alamat?->kota,
            $p->alamat?->kode_pos,
            $p->asal_sekolah,
            $ayah?->nama,
            $ayah?->pekerjaan,
            $ayah?->telepon,
            $ibu?->nama,
            $ibu?->pekerjaan,
            $ibu?->telepon,
            $wali?->nama,
            $wali?->hubungan,
            $wali?->telepon,
            $p->whatsapp,
            $p->email,
            $p->catatan_admin,
        ]);
    }
}
