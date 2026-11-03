<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePendaftaranRequest;
use App\Models\Alamat;
use App\Models\BerkasPendaftaran;
use App\Models\Jenjang;
use App\Models\OrangTua;
use App\Models\Pendaftaran;
use App\Models\StatusPendaftaran;
use App\Support\Merek;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PendaftaranController extends Controller
{
    /**
     * Nama isian di formulir => kolom di tabel pendaftaran_orang_tua.
     *
     * Ditulis sekali di sini supaya menambah peran baru (mis. penanggung
     * jawab selain orang tua) cukup menambah satu baris, tanpa menyentuh
     * struktur tabel mana pun.
     */
    private const ORANG_TUA = [
        OrangTua::AYAH => [
            'nama' => 'nama_ayah',
            'nik' => 'nik_ayah',
            'pendidikan' => 'pendidikan_ayah',
            'pekerjaan' => 'pekerjaan_ayah',
            'penghasilan' => 'penghasilan_ayah',
            'telepon' => 'telepon_ayah',
        ],
        OrangTua::IBU => [
            'nama' => 'nama_ibu',
            'nik' => 'nik_ibu',
            'pendidikan' => 'pendidikan_ibu',
            'pekerjaan' => 'pekerjaan_ibu',
            'penghasilan' => 'penghasilan_ibu',
            'telepon' => 'telepon_ibu',
        ],
        OrangTua::WALI => [
            'nama' => 'nama_wali',
            'hubungan' => 'hubungan_wali',
            'telepon' => 'telepon_wali',
        ],
    ];

    /**
     * Halaman informasi PPDB: alur, syarat, kuota per jenjang.
     */
    public function info(): View
    {
        return view('pendaftaran.info', [
            'jenjang' => Jenjang::query()->urut()->get(),
            'alur' => config('ppdb.alur'),
            'syarat' => config('ppdb.syarat'),
        ]);
    }

    /**
     * Formulir pendaftaran.
     */
    public function create(): View|RedirectResponse
    {
        if (! config('ppdb.buka')) {
            return redirect()
                ->route('ppdb.info')
                ->with('info', 'Pendaftaran untuk tahun ajaran '.config('ppdb.tahun_ajaran').' sedang ditutup.');
        }

        return view('pendaftaran.create', [
            'jenjang' => Jenjang::query()->urut()->get(),
            'syarat' => config('ppdb.syarat'),
        ]);
    }

    /**
     * Simpan formulir.
     *
     * Nama isian di formulir sengaja tidak ikut berubah saat tabelnya
     * dipecah: formulir adalah tampilan, dan bentuk penyimpanan tidak perlu
     * bocor ke sana. Pemetaannya dikerjakan di controller ini.
     */
    public function store(StorePendaftaranRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $tahunAjaran = config('ppdb.tahun_ajaran');

        $pendaftaran = DB::transaction(function () use ($request, $data, $tahunAjaran): Pendaftaran {
            $alamat = Alamat::create([
                'jalan' => $data['alamat'],
                'kelurahan' => $data['kelurahan'] ?? null,
                'kecamatan' => $data['kecamatan'] ?? null,
                'kota' => $data['kota'] ?? null,
                'kode_pos' => $data['kode_pos'] ?? null,
            ]);

            $pendaftaran = Pendaftaran::create([
                'nomor_pendaftaran' => Pendaftaran::buatNomor($tahunAjaran),
                'tahun_ajaran' => $tahunAjaran,
                'jenjang_id' => Jenjang::dariKode($data['jenjang'])?->id,
                'status_id' => StatusPendaftaran::awal()->id,
                'alamat_id' => $alamat->id,

                'nama_lengkap' => $data['nama_lengkap'],
                'nama_panggilan' => $data['nama_panggilan'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'],
                'tempat_lahir' => $data['tempat_lahir'],
                'tanggal_lahir' => $data['tanggal_lahir'],
                'nik' => $data['nik'] ?? null,
                'anak_ke' => $data['anak_ke'] ?? null,
                'jumlah_saudara' => $data['jumlah_saudara'] ?? null,
                'agama' => $data['agama'],

                'asal_sekolah' => $data['asal_sekolah'] ?? null,
                'riwayat_kesehatan' => $data['riwayat_kesehatan'] ?? null,

                'whatsapp' => $data['whatsapp'],
                'email' => $data['email'] ?? null,
                'catatan_orang_tua' => $data['catatan_orang_tua'] ?? null,

                'ip_pendaftar' => $request->ip(),
            ]);

            $this->simpanOrangTua($pendaftaran, $data);
            $this->simpanBerkas($pendaftaran, $request, $tahunAjaran);

            return $pendaftaran;
        });

        return redirect()
            ->route('ppdb.selesai')
            ->with('pendaftaran_id', $pendaftaran->id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function simpanOrangTua(Pendaftaran $pendaftaran, array $data): void
    {
        foreach (self::ORANG_TUA as $peran => $peta) {
            // Wali boleh dikosongkan; tanpa nama, barisnya tidak dibuat sama
            // sekali daripada menyimpan baris berisi kolom kosong semua.
            if (blank($data[$peta['nama']] ?? null)) {
                continue;
            }

            $isi = ['peran' => $peran];

            foreach ($peta as $kolom => $isian) {
                $isi[$kolom] = $data[$isian] ?? null;
            }

            $pendaftaran->orangTua()->create($isi);
        }
    }

    private function simpanBerkas(Pendaftaran $pendaftaran, Request $request, string $tahunAjaran): void
    {
        $folder = 'ppdb/'.str_replace('/', '-', $tahunAjaran);

        foreach (array_keys(BerkasPendaftaran::JENIS) as $jenis) {
            $isian = 'berkas_'.$jenis;

            if (! $request->hasFile($isian)) {
                continue;
            }

            $berkas = $request->file($isian);

            $nama = Str::slug(Str::limit($pendaftaran->nama_lengkap, 40, ''))
                .'-'.$isian.'-'.Str::random(8).'.'.$berkas->extension();

            // Disimpan di disk privat; hanya bisa diunduh lewat panel admin.
            $pendaftaran->berkas()->create([
                'jenis' => $jenis,
                'path' => $berkas->storeAs($folder, $nama, 'local'),
            ]);
        }
    }

    /**
     * Halaman konfirmasi setelah formulir terkirim.
     *
     * Nomor pendaftaran hanya dibaca dari session agar data pendaftar lain
     * tidak bisa dibuka dengan menebak URL.
     */
    public function selesai(Request $request): View|RedirectResponse
    {
        $id = $request->session()->get('pendaftaran_id');

        $pendaftaran = $id ? Pendaftaran::query()->lengkap()->find($id) : null;

        if (! $pendaftaran) {
            return redirect()->route('ppdb.cek');
        }

        // Dipertahankan agar halaman tetap bisa dimuat ulang / dicetak.
        $request->session()->keep('pendaftaran_id');

        return view('pendaftaran.selesai', compact('pendaftaran'));
    }

    /**
     * Form cek status mandiri.
     */
    public function cek(): View
    {
        return view('pendaftaran.cek', ['pendaftaran' => null]);
    }

    public function cekProses(Request $request): View
    {
        $pendaftaran = $this->cariMandiri($request);

        return view('pendaftaran.cek', [
            'pendaftaran' => $pendaftaran,
            'tidakDitemukan' => $pendaftaran === null,
        ]);
    }

    /**
     * Unduh bukti pendaftaran sebagai PDF.
     *
     * Dibuat di server, bukan dengan menyuruh peramban mencetak halaman:
     * hasilnya berkas yang bisa disimpan dan dikirim lewat WhatsApp, tanpa
     * ikut membawa menu, tombol, dan alamat halaman seperti hasil cetakan
     * peramban.
     *
     * Syaratnya sama dengan melihat status: nomor pendaftaran DAN tanggal
     * lahir anak harus cocok. Jadi berkas berisi data anak orang lain tidak
     * bisa diunduh hanya dengan menebak nomornya.
     */
    public function bukti(Request $request): Response
    {
        $pendaftaran = $this->cariMandiri($request);

        // Hanya tercapai bila isian tersembunyinya diubah, karena tombol unduh
        // cuma muncul setelah datanya ketemu.
        abort_if($pendaftaran === null, 404);

        $pdf = Pdf::loadView('pendaftaran.bukti', [
            'pendaftaran' => $pendaftaran,
            'logo' => $this->logoPdf(),
            'diunduhPada' => now(),
        ])->setPaper('a4');

        return $pdf->download('bukti-pendaftaran-'.$pendaftaran->nomor_pendaftaran.'.pdf');
    }

    /**
     * Pencarian mandiri oleh orang tua: nomor pendaftaran + tanggal lahir.
     *
     * Ditulis sekali di sini supaya halaman cek status dan unduhan bukti tidak
     * bisa menyimpang syaratnya diam-diam.
     */
    private function cariMandiri(Request $request): ?Pendaftaran
    {
        $validated = $request->validate([
            'nomor_pendaftaran' => ['required', 'string', 'max:32'],
            'tanggal_lahir' => ['required', 'date'],
        ], [], [
            'nomor_pendaftaran' => 'nomor pendaftaran',
            'tanggal_lahir' => 'tanggal lahir anak',
        ]);

        return Pendaftaran::query()
            ->lengkap()
            ->where('nomor_pendaftaran', trim(strtoupper($validated['nomor_pendaftaran'])))
            ->whereDate('tanggal_lahir', $validated['tanggal_lahir'])
            ->first();
    }

    /**
     * Logo untuk kop bukti pendaftaran, sebagai data URI.
     *
     * @return string|null null bila belum ada logo yang diunggah
     */
    private function logoPdf(): ?string
    {
        // Logo bawaan website berbentuk SVG, dan dompdf tidak menggambarnya
        // dengan baik. Jadi kop hanya memakai logo bila panitia sudah
        // mengunggah PNG-nya sendiri; kalau belum, kopnya cukup teks.
        if (Merek::pilihan('logo') !== Merek::UNGGAHAN) {
            return null;
        }

        $berkas = public_path((string) Merek::berkas('logo'));

        if (! is_file($berkas)) {
            return null;
        }

        // Ditanam sebagai data URI, bukan diberikan sebagai jalur berkas:
        // dompdf membatasi berkas yang boleh dibukanya lewat pengaturan
        // chroot, dan cara ini tidak bergantung pada pengaturan itu.
        return 'data:image/png;base64,'.base64_encode((string) file_get_contents($berkas));
    }
}
