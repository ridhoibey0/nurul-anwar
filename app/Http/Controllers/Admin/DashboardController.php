<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $tahunAjaran = config('ppdb.tahun_ajaran');

        $perStatus = Pendaftaran::query()
            ->where('tahun_ajaran', $tahunAjaran)
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $perJenjang = Pendaftaran::query()
            ->where('tahun_ajaran', $tahunAjaran)
            ->selectRaw('jenjang, COUNT(*) as jumlah')
            ->groupBy('jenjang')
            ->pluck('jumlah', 'jenjang');

        return view('admin.dashboard', [
            'tahunAjaran' => $tahunAjaran,
            'total' => $perStatus->sum(),
            'perStatus' => $perStatus,
            'perJenjang' => $perJenjang,
            'terbaru' => Pendaftaran::query()
                ->where('tahun_ajaran', $tahunAjaran)
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }
}
