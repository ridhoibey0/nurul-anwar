<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePengaturanPpdbRequest;
use App\Http\Requests\UpdatePengaturanSekolahRequest;
use App\Models\Pendaftaran;
use App\Models\Pengaturan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PengaturanController extends Controller
{
    /**
     * Nama isian di formulir => kunci di tabel pengaturan. Ditulis sekali di
     * sini supaya nama isian dan nama kunci tidak bisa menyimpang diam-diam.
     */
    private const PEMETAAN = [
        'nama' => 'sekolah_nama',
        'nama_pendek' => 'sekolah_nama_pendek',
        'tagline' => 'sekolah_tagline',
        'tahun_berdiri' => 'sekolah_tahun_berdiri',
        'akreditasi' => 'sekolah_akreditasi',
        'alamat' => 'sekolah_alamat',
        'kota' => 'sekolah_kota',
        'provinsi' => 'sekolah_provinsi',
        'jam_operasional' => 'sekolah_jam_operasional',
        'telepon' => 'sekolah_telepon',
        'whatsapp' => 'sekolah_whatsapp',
        'email' => 'sekolah_email',
        'instagram' => 'sekolah_instagram',
        'instagram_url' => 'sekolah_instagram_url',
        'facebook_url' => 'sekolah_facebook_url',
        'youtube_url' => 'sekolah_youtube_url',
        'peta_lat' => 'sekolah_peta_lat',
        'peta_lng' => 'sekolah_peta_lng',
        'peta_url' => 'sekolah_peta_url',
    ];

    public function editSekolah(): View
    {
        return view('admin.sekolah', [
            'sekolah' => config('sekolah'),
            // Selama kunci ini belum pernah disimpan, yang tampil di isian
            // adalah nilai bawaan dari .env.
            'pernahDisimpan' => Pengaturan::ambil('sekolah_nama') !== null,
        ]);
    }

    public function updateSekolah(UpdatePengaturanSekolahRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $simpan = [];

        foreach (self::PEMETAAN as $isian => $kunci) {
            // Isian yang dikosongkan disimpan sebagai string kosong, bukan
            // null. Null berarti "belum pernah diisi" dan akan jatuh balik ke
            // nilai .env -- padahal panitia justru sengaja mengosongkannya.
            $simpan[$kunci] = (string) ($data[$isian] ?? '');
        }

        Pengaturan::simpan($simpan);

        return redirect()
            ->route('admin.pengaturan.sekolah')
            ->with('sukses', 'Identitas sekolah tersimpan. Perubahan langsung tampil di website.');
    }

    public function edit(): View
    {
        return view('admin.pengaturan', [
            'buka' => (bool) config('ppdb.buka'),
            'tahunAjaran' => config('ppdb.tahun_ajaran'),
            'dibukaPada' => config('ppdb.dibuka_pada'),
            'ditutupPada' => config('ppdb.ditutup_pada'),
            'perTahun' => Pendaftaran::query()
                ->selectRaw('tahun_ajaran, COUNT(*) as jumlah')
                ->groupBy('tahun_ajaran')
                ->orderByDesc('tahun_ajaran')
                ->pluck('jumlah', 'tahun_ajaran'),
        ]);
    }

    public function update(UpdatePengaturanPpdbRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Pengaturan::simpan([
            'ppdb_buka' => (bool) $data['buka'],
            'ppdb_tahun_ajaran' => $data['tahun_ajaran'],
            'ppdb_dibuka_pada' => $data['dibuka_pada'],
            'ppdb_ditutup_pada' => $data['ditutup_pada'],
        ]);

        return redirect()
            ->route('admin.pengaturan.edit')
            ->with('sukses', 'Pengaturan PPDB tersimpan. Perubahan langsung tampil di website.');
    }

    /**
     * Saklar cepat buka/tutup pendaftaran, dipakai dari halaman ringkasan.
     */
    public function toggle(): RedirectResponse
    {
        // Dibaca dari tabel pengaturan (bukan config) supaya saklar tetap benar
        // walau config sudah ditimpa lebih awal dalam request ini.
        $tersimpan = Pengaturan::ambil('ppdb_buka');

        $buka = ! ($tersimpan === null
            ? (bool) config('ppdb.buka')
            : filter_var($tersimpan, FILTER_VALIDATE_BOOLEAN));

        Pengaturan::simpan(['ppdb_buka' => $buka]);

        return back()->with(
            'sukses',
            $buka
                ? 'Pendaftaran dibuka. Formulir online sudah bisa diakses orang tua.'
                : 'Pendaftaran ditutup. Formulir online tidak lagi menerima pengiriman.',
        );
    }
}
