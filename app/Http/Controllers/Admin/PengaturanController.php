<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePengaturanPpdbRequest;
use App\Http\Requests\UpdatePengaturanSekolahRequest;
use App\Models\Pendaftaran;
use App\Models\Pengaturan;
use App\Support\Gambar;
use App\Support\Merek;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

    /**
     * Isian berkas di formulir yang sama => kunci di tabel pengaturan. Dipisah
     * dari PEMETAAN karena isinya berkas unggahan, bukan teks: penyimpanannya
     * lewat simpanMerek(), bukan disalin langsung.
     */
    private const MEREK = [
        'logo' => 'sekolah_logo',
        'gambar_bagikan' => 'sekolah_gambar_bagikan',
    ];

    public function editSekolah(): View
    {
        return view('admin.sekolah', [
            'sekolah' => config('sekolah'),
            // Selama kunci ini belum pernah disimpan, yang tampil di isian
            // adalah nilai bawaan dari .env.
            'pernahDisimpan' => Pengaturan::ambil('sekolah_nama') !== null,
            'logoPilihan' => Merek::pilihan('logo'),
            'bagikanPilihan' => Merek::pilihan('gambar_bagikan'),
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

        $sebelumnya = $this->jalurMerek();
        $merek = $this->simpanMerek($request);

        Pengaturan::simpan($simpan + $merek);

        // Berkas lama dihapus setelah jalur barunya tersimpan, bukan
        // sebelumnya: kalau penyimpanan gagal di tengah jalan, pengaturan
        // tidak jadi menunjuk ke berkas yang sudah lenyap.
        foreach (array_keys($merek) as $kunci) {
            $this->hapusMerek($sebelumnya[$kunci]);
        }

        return redirect()
            ->route('admin.pengaturan.sekolah')
            ->with('sukses', 'Identitas sekolah tersimpan. Perubahan langsung tampil di website.');
    }

    /**
     * Jalur logo dan gambar bagikan yang tersimpan sekarang.
     *
     * @return array<string, string|null>
     */
    private function jalurMerek(): array
    {
        $hasil = [];

        foreach (self::MEREK as $kunci) {
            $hasil[$kunci] = Pengaturan::ambil($kunci);
        }

        return $hasil;
    }

    /**
     * Proses unggahan logo dan gambar bagikan, lalu kembalikan nilai barunya.
     *
     * Hanya jenis yang benar-benar diubah panitia yang muncul di hasilnya,
     * supaya menyimpan formulir tanpa menyentuh gambar tidak mengubah gambar
     * yang sudah dipasang.
     *
     * Nilai yang dikembalikan mengikuti tiga keadaan di App\Support\Merek:
     * null untuk berkas bawaan, string kosong untuk tanpa gambar, dan jalur
     * berkas untuk hasil unggahan.
     *
     * @return array<string, string|null>
     */
    private function simpanMerek(Request $request): array
    {
        $hasil = [];

        foreach (self::MEREK as $isian => $kunci) {
            $berkas = $request->file($isian);

            // Memilih berkas selalu menang atas pilihan keadaan: panitia yang
            // mengunggah gambar jelas-jelas ingin gambar itu yang dipakai.
            if ($berkas instanceof UploadedFile) {
                $baru = $this->unggahMerek($isian, $berkas);

                if ($baru === null) {
                    throw ValidationException::withMessages([
                        $isian => 'Gambar gagal diproses. Pastikan ekstensi GD aktif di server.',
                    ]);
                }

                $hasil[$kunci] = $baru;

                continue;
            }

            // UNGGAHAN tanpa berkas baru berarti "pertahankan yang sekarang",
            // jadi kuncinya sengaja tidak dimasukkan ke hasil.
            $pilihan = $request->input($isian.'_pilihan');

            if ($pilihan === Merek::BAWAAN) {
                $hasil[$kunci] = null;
            } elseif ($pilihan === Merek::KOSONG) {
                $hasil[$kunci] = '';
            }
        }

        return $hasil;
    }

    /** @return string|null jalur relatif public/, atau null bila gagal diproses */
    private function unggahMerek(string $jenis, UploadedFile $berkas): ?string
    {
        $persegi = $jenis === 'logo';
        $nama = $jenis.'-'.now()->format('Ymd').'-'.Str::random(8).($persegi ? '.png' : '.jpg');
        $tujuan = Storage::disk(Merek::DISK)->path($nama);

        // GD menulis langsung ke filesystem, jadi foldernya harus sudah ada.
        File::ensureDirectoryExists(dirname($tujuan));

        $berhasil = $persegi
            ? Gambar::persegi($berkas, $tujuan)
            : Gambar::kecilkan($berkas, $tujuan);

        return $berhasil ? 'images/'.Merek::DISK.'/'.$nama : null;
    }

    private function hapusMerek(?string $jalur): void
    {
        // Hanya berkas unggahan yang boleh dihapus. Tanpa penjagaan ini,
        // "kembalikan ke bawaan" bisa menghapus logo bawaan dari
        // public/images/kiddies dan website kehilangan logo sama sekali.
        if (Merek::unggahan($jalur)) {
            Storage::disk(Merek::DISK)->delete(basename((string) $jalur));
        }
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
