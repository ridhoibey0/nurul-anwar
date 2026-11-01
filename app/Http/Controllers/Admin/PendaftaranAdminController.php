<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
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
            'tahunTersedia' => Pendaftaran::query()
                ->distinct()
                ->orderByDesc('tahun_ajaran')
                ->pluck('tahun_ajaran'),
        ]);
    }

    public function show(Pendaftaran $pendaftaran): View
    {
        return view('admin.pendaftaran.show', [
            'pendaftaran' => $pendaftaran->load('verifikator'),
        ]);
    }

    public function updateStatus(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(config('ppdb.status')))],
            'catatan_admin' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'status' => 'status pendaftaran',
            'catatan_admin' => 'catatan panitia',
        ]);

        $pendaftaran->update([
            'status' => $data['status'],
            'catatan_admin' => $data['catatan_admin'] ?? null,
            'diverifikasi_pada' => now(),
            'diverifikasi_oleh' => $request->user()->id,
        ]);

        return back()->with('sukses', 'Status pendaftaran '.$pendaftaran->nomor_pendaftaran.' diperbarui.');
    }

    public function destroy(Pendaftaran $pendaftaran): RedirectResponse
    {
        foreach ($pendaftaran->daftarBerkas() as $berkas) {
            if ($berkas['path']) {
                Storage::disk('local')->delete($berkas['path']);
            }
        }

        $nomor = $pendaftaran->nomor_pendaftaran;
        $pendaftaran->delete();

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
        abort_unless(in_array($jenis, ['kk', 'akta', 'foto'], true), 404);

        $path = $pendaftaran->{'berkas_'.$jenis};

        abort_if(blank($path) || ! Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path));
    }

    /**
     * Ekspor hasil filter saat ini ke CSV (bisa dibuka di Excel).
     */
    public function export(Request $request): StreamedResponse
    {
        $query = Pendaftaran::query()
            ->cari($request->string('q')->toString())
            ->status($request->string('status')->toString())
            ->jenjang($request->string('jenjang')->toString())
            ->tahunAjaran($request->string('tahun_ajaran')->toString())
            ->latest();

        $kolom = [
            'nomor_pendaftaran' => 'Nomor Pendaftaran',
            'tahun_ajaran' => 'Tahun Ajaran',
            'jenjang_label' => 'Jenjang',
            'status_label' => 'Status',
            'nama_lengkap' => 'Nama Lengkap',
            'nama_panggilan' => 'Nama Panggilan',
            'jenis_kelamin_label' => 'Jenis Kelamin',
            'tempat_lahir' => 'Tempat Lahir',
            'tanggal_lahir' => 'Tanggal Lahir',
            'usia' => 'Usia',
            'nik' => 'NIK Anak',
            'anak_ke' => 'Anak Ke',
            'jumlah_saudara' => 'Jumlah Saudara',
            'alamat' => 'Alamat',
            'kelurahan' => 'Kelurahan',
            'kecamatan' => 'Kecamatan',
            'kota' => 'Kota',
            'asal_sekolah' => 'Asal Sekolah',
            'nama_ayah' => 'Nama Ayah',
            'pekerjaan_ayah' => 'Pekerjaan Ayah',
            'telepon_ayah' => 'Telepon Ayah',
            'nama_ibu' => 'Nama Ibu',
            'pekerjaan_ibu' => 'Pekerjaan Ibu',
            'telepon_ibu' => 'Telepon Ibu',
            'whatsapp' => 'WhatsApp',
            'email' => 'Email',
            'catatan_admin' => 'Catatan Panitia',
        ];

        $tahunFile = $request->string('tahun_ajaran')->toString() ?: config('ppdb.tahun_ajaran');
        $namaFile = 'ppdb-'.str_replace('/', '-', $tahunFile).'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query, $kolom): void {
            $keluaran = fopen('php://output', 'w');

            // BOM agar karakter Indonesia tampil benar saat dibuka di Excel.
            fwrite($keluaran, "\xEF\xBB\xBF");

            fputcsv($keluaran, array_values($kolom), ';', '"', '');

            $query->chunk(200, function ($baris) use ($keluaran, $kolom): void {
                foreach ($baris as $item) {
                    $nilai = [];

                    foreach (array_keys($kolom) as $atribut) {
                        $isi = $item->{$atribut};

                        $nilai[] = $isi instanceof \DateTimeInterface
                            ? $isi->format('Y-m-d')
                            : (string) $isi;
                    }

                    fputcsv($keluaran, $nilai, ';', '"', '');
                }
            });

            fclose($keluaran);
        }, $namaFile, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
