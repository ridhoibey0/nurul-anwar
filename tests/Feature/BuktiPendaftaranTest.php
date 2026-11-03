<?php

namespace Tests\Feature;

use App\Models\Pendaftaran;
use App\Models\Pengaturan;
use App\Support\Merek;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Unduhan bukti pendaftaran (PDF) oleh orang tua.
 *
 * Butuh paket barryvdh/laravel-dompdf terpasang. Tanpa itu seluruh berkas ini
 * gagal dengan "Class Barryvdh\DomPDF\Facade\Pdf not found".
 */
class BuktiPendaftaranTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $ubah
     */
    private function pendaftar(array $ubah = []): Pendaftaran
    {
        return Pendaftaran::factory()->create(array_merge([
            'nomor_pendaftaran' => 'PPDB-2627-0007',
            'nama_lengkap' => 'Aisyah Nur Hidayah',
            'tanggal_lahir' => '2021-04-17',
        ], $ubah));
    }

    /**
     * @param  array<string, mixed>  $kirim
     */
    private function unduh(array $kirim)
    {
        return $this->post('/ppdb/bukti', $kirim);
    }

    private function unduhMilik(Pendaftaran $pendaftaran)
    {
        return $this->unduh([
            'nomor_pendaftaran' => $pendaftaran->nomor_pendaftaran,
            'tanggal_lahir' => $pendaftaran->tanggal_lahir->toDateString(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Unduhan berhasil
    |--------------------------------------------------------------------------
    */

    public function test_bukti_terunduh_sebagai_berkas_pdf(): void
    {
        $respons = $this->unduhMilik($this->pendaftar())->assertOk();

        $this->assertStringContainsString(
            'application/pdf',
            (string) $respons->headers->get('content-type'),
        );

        // Harus attachment, bukan inline: yang diminta berkas yang tersimpan,
        // bukan halaman yang terbuka di peramban.
        $this->assertStringContainsString(
            'attachment',
            (string) $respons->headers->get('content-disposition'),
        );
    }

    public function test_nama_berkas_memakai_nomor_pendaftaran(): void
    {
        $pendaftaran = $this->pendaftar(['nomor_pendaftaran' => 'PPDB-2627-0042']);

        $this->assertStringContainsString(
            'bukti-pendaftaran-PPDB-2627-0042.pdf',
            (string) $this->unduhMilik($pendaftaran)->headers->get('content-disposition'),
        );
    }

    public function test_isinya_benar_benar_pdf_bukan_html(): void
    {
        $isi = $this->unduhMilik($this->pendaftar())->content();

        // Setiap berkas PDF diawali tanda ini.
        $this->assertStringStartsWith('%PDF-', $isi);
        $this->assertStringNotContainsString('<!DOCTYPE html>', $isi);
    }

    public function test_berkasnya_tidak_kosong(): void
    {
        $isi = $this->unduhMilik($this->pendaftar())->content();

        // PDF satu halaman berisi data pendaftar beserta font yang ditanam
        // tidak mungkin cuma beberapa ratus bita. Kalau segitu, berarti
        // rendernya gagal tapi tetap mengembalikan 200.
        $this->assertGreaterThan(2000, strlen($isi));
    }

    /*
    |--------------------------------------------------------------------------
    | Hak akses
    |--------------------------------------------------------------------------
    */

    public function test_nomor_benar_tapi_tanggal_lahir_salah_ditolak(): void
    {
        $pendaftaran = $this->pendaftar();

        // Inti pengujian ini: nomor pendaftaran saja tidak cukup. Nomornya
        // berurutan dan gampang ditebak, jadi tanpa tanggal lahir siapa pun
        // bisa mengunduh data anak orang lain.
        $this->unduh([
            'nomor_pendaftaran' => $pendaftaran->nomor_pendaftaran,
            'tanggal_lahir' => '2019-01-01',
        ])->assertNotFound();
    }

    public function test_nomor_pendaftaran_yang_tidak_ada_ditolak(): void
    {
        $this->pendaftar();

        $this->unduh([
            'nomor_pendaftaran' => 'PPDB-2627-9999',
            'tanggal_lahir' => '2021-04-17',
        ])->assertNotFound();
    }

    public function test_nomor_pendaftaran_wajib_diisi(): void
    {
        $this->unduh(['tanggal_lahir' => '2021-04-17'])
            ->assertSessionHasErrors('nomor_pendaftaran');
    }

    public function test_tanggal_lahir_wajib_diisi(): void
    {
        $this->unduh(['nomor_pendaftaran' => 'PPDB-2627-0007'])
            ->assertSessionHasErrors('tanggal_lahir');
    }

    public function test_nomor_pendaftaran_tidak_peka_huruf_besar_kecil(): void
    {
        $pendaftaran = $this->pendaftar();

        $this->unduh([
            'nomor_pendaftaran' => strtolower($pendaftaran->nomor_pendaftaran),
            'tanggal_lahir' => $pendaftaran->tanggal_lahir->toDateString(),
        ])->assertOk();
    }

    public function test_spasi_di_nomor_pendaftaran_diabaikan(): void
    {
        $pendaftaran = $this->pendaftar();

        $this->unduh([
            'nomor_pendaftaran' => '  '.$pendaftaran->nomor_pendaftaran.'  ',
            'tanggal_lahir' => $pendaftaran->tanggal_lahir->toDateString(),
        ])->assertOk();
    }

    public function test_rutenya_tidak_bisa_dibuka_lewat_get(): void
    {
        // Sengaja POST saja: lewat GET, nomor pendaftaran dan tanggal lahir
        // anak akan ikut tercatat di riwayat peramban dan log server.
        $this->get('/ppdb/bukti')->assertMethodNotAllowed();
    }

    /*
    |--------------------------------------------------------------------------
    | Tombol di halaman
    |--------------------------------------------------------------------------
    */

    public function test_tombol_unduh_muncul_setelah_data_ditemukan(): void
    {
        $pendaftaran = $this->pendaftar();

        // Sebelum mencari, belum ada apa pun untuk diunduh.
        $this->get('/ppdb/cek-status')
            ->assertOk()
            ->assertDontSee('Unduh Bukti Pendaftaran');

        $this->post('/ppdb/cek-status', [
            'nomor_pendaftaran' => $pendaftaran->nomor_pendaftaran,
            'tanggal_lahir' => $pendaftaran->tanggal_lahir->toDateString(),
        ])
            ->assertOk()
            ->assertSee('Unduh Bukti Pendaftaran')
            ->assertSee(route('ppdb.bukti'), false);
    }

    public function test_tombol_unduh_tidak_muncul_saat_data_tidak_ditemukan(): void
    {
        $this->post('/ppdb/cek-status', [
            'nomor_pendaftaran' => 'PPDB-2627-9999',
            'tanggal_lahir' => '2021-04-17',
        ])
            ->assertOk()
            ->assertSee('Data tidak ditemukan')
            ->assertDontSee('Unduh Bukti Pendaftaran');
    }

    public function test_tombol_unduh_muncul_di_halaman_konfirmasi(): void
    {
        $pendaftaran = $this->pendaftar();

        $this->withSession(['pendaftaran_id' => $pendaftaran->id])
            ->get('/ppdb/selesai')
            ->assertOk()
            ->assertSee('Unduh Bukti Pendaftaran')
            ->assertSee(route('ppdb.bukti'), false);
    }

    /*
    |--------------------------------------------------------------------------
    | Logo di kop
    |--------------------------------------------------------------------------
    */

    public function test_bukti_tetap_terbentuk_tanpa_logo_yang_diunggah(): void
    {
        // Logo bawaan website berbentuk SVG dan tidak dipasang di kop, jadi
        // keadaan bawaan ini harus tetap menghasilkan PDF yang utuh.
        $this->assertSame(Merek::BAWAAN, Merek::pilihan('logo'));

        $this->unduhMilik($this->pendaftar())->assertOk();
    }

    public function test_bukti_tetap_terbentuk_setelah_logo_dikosongkan(): void
    {
        Pengaturan::simpan(['sekolah_logo' => '']);
        Pengaturan::terapkanKeConfig();

        $this->assertNull(logo_url());

        $this->unduhMilik($this->pendaftar())->assertOk();
    }

    public function test_bukti_tetap_terbentuk_saat_berkas_logonya_hilang(): void
    {
        // Jalurnya tersimpan tapi berkasnya tidak ada di server, misalnya
        // karena folder public/images/merek ikut hilang saat deploy. PDF-nya
        // harus tetap keluar, cuma tanpa logo di kop.
        Pengaturan::simpan(['sekolah_logo' => 'images/merek/logo-yang-sudah-hilang.png']);
        Pengaturan::terapkanKeConfig();

        $this->unduhMilik($this->pendaftar())->assertOk();
    }

    public function test_bukti_terbentuk_dengan_logo_yang_diunggah(): void
    {
        // Berkasnya ditaruh langsung di public/ -- bukan lewat Storage::fake --
        // karena logoPdf() membacanya dengan public_path(). Dengan disk tiruan,
        // jalur itu tidak akan pernah ada dan logonya malah terlewat diuji.
        $jalur = 'images/'.Merek::DISK.'/logo-uji-'.Str::random(8).'.png';
        $berkas = public_path($jalur);

        File::ensureDirectoryExists(dirname($berkas));

        // Digambar dengan GD, bukan ditempel sebagai base64, supaya yang
        // dibaca dompdf pasti PNG yang sah.
        $gambar = imagecreatetruecolor(48, 48);
        imagepng($gambar, $berkas);
        imagedestroy($gambar);

        try {
            Pengaturan::simpan(['sekolah_logo' => $jalur]);
            Pengaturan::terapkanKeConfig();

            $this->assertSame(Merek::UNGGAHAN, Merek::pilihan('logo'));

            $isi = $this->unduhMilik($this->pendaftar())->assertOk()->content();

            $this->assertStringStartsWith('%PDF-', $isi);
        } finally {
            File::delete($berkas);
        }
    }
}
