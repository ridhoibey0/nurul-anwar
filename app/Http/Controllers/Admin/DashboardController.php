<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jenjang;
use App\Models\Pendaftaran;
use App\Models\StatusPendaftaran;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $tahunAjaran = config('ppdb.tahun_ajaran');

        return view('admin.dashboard', [
            'tahunAjaran' => $tahunAjaran,
            'total' => Pendaftaran::query()->where('tahun_ajaran', $tahunAjaran)->count(),

            // Dihitung lewat relasi, jadi kuncinya tetap kode yang terbaca
            // ("baru", "ra-a") walau yang tersimpan di tabel adalah id.
            'perStatus' => $this->hitungPer('status', $tahunAjaran),
            'perJenjang' => $this->hitungPer('jenjang', $tahunAjaran),

            'daftarStatus' => StatusPendaftaran::query()->urut()->get(),
            'daftarJenjang' => Jenjang::query()->urut()->get(),

            'terbaru' => Pendaftaran::query()
                ->with(['jenjang', 'status'])
                ->where('tahun_ajaran', $tahunAjaran)
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }

    /**
     * Jumlah pendaftar per kode, mis. ['baru' => 3, 'diterima' => 1].
     *
     * @return \Illuminate\Support\Collection<string, int>
     */
    private function hitungPer(string $relasi, string $tahunAjaran)
    {
        $tabel = $relasi === 'status' ? 'status_pendaftaran' : 'jenjang';
        $kolom = $relasi.'_id';

        return Pendaftaran::query()
            ->join($tabel, "pendaftaran_siswa.{$kolom}", '=', "{$tabel}.id")
            ->where('pendaftaran_siswa.tahun_ajaran', $tahunAjaran)
            ->selectRaw("{$tabel}.kode as kode, COUNT(*) as jumlah")
            ->groupBy("{$tabel}.kode")
            ->pluck('jumlah', 'kode');
    }
}
