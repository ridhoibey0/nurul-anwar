<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePendaftaranRequest;
use App\Models\Pendaftaran;
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
     * Halaman informasi PPDB: alur, syarat, kuota per jenjang.
     */
    public function info(): View
    {
        return view('pendaftaran.info', [
            'jenjang' => config('ppdb.jenjang'),
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
            'jenjang' => config('ppdb.jenjang'),
            'syarat' => config('ppdb.syarat'),
        ]);
    }

    public function store(StorePendaftaranRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['persetujuan', 'berkas_kk', 'berkas_akta', 'berkas_foto']);

        $folder = 'ppdb/'.str_replace('/', '-', config('ppdb.tahun_ajaran'));

        foreach (['berkas_kk', 'berkas_akta', 'berkas_foto'] as $field) {
            if ($request->hasFile($field)) {
                $berkas = $request->file($field);

                $nama = Str::slug(Str::limit($request->input('nama_lengkap'), 40, ''))
                    .'-'.$field.'-'.Str::random(8).'.'.$berkas->extension();

                // Disimpan di disk privat; hanya bisa diunduh lewat panel admin.
                $data[$field] = $berkas->storeAs($folder, $nama, 'local');
            }
        }

        $data['tahun_ajaran'] = config('ppdb.tahun_ajaran');
        $data['status'] = 'baru';
        $data['ip_pendaftar'] = $request->ip();

        $pendaftaran = DB::transaction(function () use ($data) {
            $data['nomor_pendaftaran'] = Pendaftaran::buatNomor($data['tahun_ajaran']);

            return Pendaftaran::create($data);
        });

        return redirect()
            ->route('ppdb.selesai')
            ->with('pendaftaran_id', $pendaftaran->id);
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

        $pendaftaran = $id ? Pendaftaran::find($id) : null;

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
