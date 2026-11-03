<?php

namespace Tests\Feature;

use App\Models\Alamat;
use App\Models\BerkasPendaftaran;
use App\Models\Jenjang;
use App\Models\OrangTua;
use App\Models\Pendaftaran;
use App\Models\StatusPendaftaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Struktur pendaftaran setelah dinormalisasi.
 *
 * Yang diuji di sini bukan tampilannya, melainkan bahwa satu formulir yang
 * dikirim benar-benar terurai ke tabel-tabelnya: alamat, orang tua, dan
 * berkas masing-masing jadi barisnya sendiri.
 */
class StrukturPendaftaranTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $ubah
     * @return array<string, mixed>
     */
    private function dataFormulir(array $ubah = []): array
    {
        return array_merge([
            'jenjang' => 'ra-a',
            'nama_lengkap' => 'Aisyah Nur Hidayah',
            'nama_panggilan' => 'Aisyah',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Tasikmalaya',
            'tanggal_lahir' => now()->subYears(5)->toDateString(),
            'agama' => 'Islam',

            'alamat' => 'Jl. Winaya Utama No. 20',
            'kelurahan' => 'Kahuripan',
            'kecamatan' => 'Tawang',
            'kota' => 'Kota Tasikmalaya',
            'kode_pos' => '46115',

            'nama_ayah' => 'Hidayat Rahman',
            'pekerjaan_ayah' => 'Wiraswasta',
            'telepon_ayah' => '081111111111',
            'nama_ibu' => 'Siti Aminah',
            'pekerjaan_ibu' => 'Ibu Rumah Tangga',

            'whatsapp' => '081234567890',

            'berkas_kk' => UploadedFile::fake()->create('kk.pdf', 100, 'application/pdf'),
            'berkas_akta' => UploadedFile::fake()->image('akta.jpg'),
            'persetujuan' => '1',
        ], $ubah);
    }

    private function kirim(array $ubah = []): Pendaftaran
    {
        Storage::fake('local');

        $this->post(route('ppdb.store'), $this->dataFormulir($ubah))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('ppdb.selesai'));

        return Pendaftaran::query()->lengkap()->latest('id')->firstOrFail();
    }

    /*
    |--------------------------------------------------------------------------
    | Tabel referensi
    |--------------------------------------------------------------------------
    */

    public function test_tabel_referensi_terisi_sejak_migrasi(): void
    {
        // Diisi di migrasi, bukan seeder: tanpa jenjang formulir tidak punya
        // pilihan, dan tanpa status "baru" pendaftaran tidak punya status awal.
        $this->assertSame(5, Jenjang::query()->count());
        $this->assertSame(4, StatusPendaftaran::query()->count());

        $this->assertNotNull(Jenjang::dariKode('ra-a'));
        $this->assertNotNull(StatusPendaftaran::dariKode('baru'));
    }

    public function test_jenjang_dan_status_urut_sesuai_kolom_urutan(): void
    {
        $this->assertSame(
            ['kb', 'ra-a', 'ra-b', 'tpa', 'dta'],
            Jenjang::query()->urut()->pluck('kode')->all(),
        );

        $this->assertSame(
            ['baru', 'diverifikasi', 'diterima', 'ditolak'],
            StatusPendaftaran::query()->urut()->pluck('kode')->all(),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Satu formulir terurai ke tabelnya masing-masing
    |--------------------------------------------------------------------------
    */

    public function test_jenjang_tersimpan_sebagai_relasi_bukan_teks(): void
    {
        $pendaftaran = $this->kirim(['jenjang' => 'ra-b']);

        $this->assertSame('ra-b', $pendaftaran->jenjang->kode);
        $this->assertSame('RA Kelompok B', $pendaftaran->jenjang_label);
        $this->assertDatabaseMissing('pendaftaran_siswa', ['id' => $pendaftaran->id, 'jenjang_id' => null]);
    }

    public function test_pendaftaran_baru_berstatus_menunggu_verifikasi(): void
    {
        $pendaftaran = $this->kirim();

        $this->assertSame('baru', $pendaftaran->status->kode);
        $this->assertSame('Menunggu Verifikasi', $pendaftaran->status_label);
    }

    public function test_alamat_tersimpan_di_tabel_tersendiri(): void
    {
        $pendaftaran = $this->kirim();

        $this->assertNotNull($pendaftaran->alamat);
        $this->assertSame('Jl. Winaya Utama No. 20', $pendaftaran->alamat->jalan);
        $this->assertSame('Kahuripan', $pendaftaran->alamat->kelurahan);
        $this->assertSame('46115', $pendaftaran->alamat->kode_pos);

        $this->assertStringContainsString('Kota Tasikmalaya 46115', $pendaftaran->alamat->lengkap);
    }

    public function test_ayah_dan_ibu_jadi_baris_bukan_kolom(): void
    {
        $pendaftaran = $this->kirim();

        $this->assertCount(2, $pendaftaran->orangTua);

        $ayah = $pendaftaran->orangTuaPeran(OrangTua::AYAH);
        $this->assertSame('Hidayat Rahman', $ayah->nama);
        $this->assertSame('Wiraswasta', $ayah->pekerjaan);
        $this->assertSame('081111111111', $ayah->telepon);

        $this->assertSame('Siti Aminah', $pendaftaran->orangTuaPeran(OrangTua::IBU)->nama);
    }

    public function test_wali_tidak_dibuat_bila_tidak_diisi(): void
    {
        $pendaftaran = $this->kirim();

        // Daripada menyimpan baris berisi kolom kosong semua.
        $this->assertNull($pendaftaran->orangTuaPeran(OrangTua::WALI));
        $this->assertDatabaseCount('pendaftaran_orang_tua', 2);
    }

    public function test_wali_dibuat_bila_diisi(): void
    {
        $pendaftaran = $this->kirim([
            'nama_wali' => 'Dadang Supriatna',
            'hubungan_wali' => 'Paman',
            'telepon_wali' => '082222222222',
        ]);

        $wali = $pendaftaran->orangTuaPeran(OrangTua::WALI);

        $this->assertNotNull($wali);
        $this->assertSame('Dadang Supriatna', $wali->nama);
        $this->assertSame('Paman', $wali->hubungan);
        $this->assertDatabaseCount('pendaftaran_orang_tua', 3);
    }

    public function test_berkas_jadi_baris_dan_tetap_di_disk_privat(): void
    {
        $pendaftaran = $this->kirim();

        $this->assertCount(2, $pendaftaran->berkas);

        $kk = $pendaftaran->berkasJenis(BerkasPendaftaran::KK);

        $this->assertNotNull($kk);
        $this->assertSame('Kartu Keluarga', $kk->label);
        Storage::disk('local')->assertExists($kk->path);

        // Pas foto opsional dan tidak dikirim, jadi tidak ada barisnya.
        $this->assertNull($pendaftaran->berkasJenis(BerkasPendaftaran::FOTO));
    }

    public function test_daftar_berkas_tetap_menyebut_yang_belum_diunggah(): void
    {
        $pendaftaran = $this->kirim();
        $daftar = $pendaftaran->daftarBerkas();

        // Panel admin perlu tahu apa yang KURANG, bukan cuma apa yang ada.
        $this->assertCount(3, $daftar);
        $this->assertSame('foto', $daftar[2]['jenis']);
        $this->assertNull($daftar[2]['berkas']);
        $this->assertFalse($daftar[2]['wajib']);
    }

    public function test_jenjang_yang_tidak_terdaftar_ditolak(): void
    {
        $this->post(route('ppdb.store'), $this->dataFormulir(['jenjang' => 'sma']))
            ->assertSessionHasErrors('jenjang');

        $this->assertDatabaseCount('pendaftaran_siswa', 0);
    }

    public function test_formulir_gagal_tidak_meninggalkan_baris_setengah_jadi(): void
    {
        $this->post(route('ppdb.store'), $this->dataFormulir(['nama_lengkap' => '']))
            ->assertSessionHasErrors('nama_lengkap');

        // Validasi gagal sebelum controller, jadi alamat pun tidak terbuat.
        $this->assertDatabaseCount('pendaftaran_siswa', 0);
        $this->assertDatabaseCount('alamat', 0);
        $this->assertDatabaseCount('pendaftaran_orang_tua', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Penghapusan
    |--------------------------------------------------------------------------
    */

    public function test_menghapus_pendaftaran_ikut_menghapus_anak_anaknya(): void
    {
        $pendaftaran = $this->kirim();
        $alamatId = $pendaftaran->alamat_id;

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.pendaftaran.destroy', $pendaftaran))
            ->assertRedirect(route('admin.pendaftaran.index'));

        $this->assertDatabaseCount('pendaftaran_siswa', 0);

        // Orang tua dan berkas ikut lewat cascade di database.
        $this->assertDatabaseCount('pendaftaran_orang_tua', 0);
        $this->assertDatabaseCount('pendaftaran_berkas', 0);

        // Alamat tidak punya cascade karena pendaftaran yang menunjuk
        // kepadanya; dibuang controller supaya tidak jadi baris yatim.
        $this->assertDatabaseMissing('alamat', ['id' => $alamatId]);
    }

    public function test_menghapus_pendaftaran_tidak_menghapus_jenjang_dan_status(): void
    {
        $pendaftaran = $this->kirim();

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.pendaftaran.destroy', $pendaftaran));

        // Tabel referensi dipakai bersama; kalau ikut terhapus, pendaftaran
        // lain kehilangan jenjang dan statusnya.
        $this->assertSame(5, Jenjang::query()->count());
        $this->assertSame(4, StatusPendaftaran::query()->count());
    }

    /*
    |--------------------------------------------------------------------------
    | Panel admin
    |--------------------------------------------------------------------------
    */

    public function test_filter_status_dan_jenjang_memakai_kode(): void
    {
        Pendaftaran::factory()->jenjang('kb')->status('baru')->create();
        Pendaftaran::factory()->jenjang('ra-b')->status('diterima')->create();

        $this->assertSame(1, Pendaftaran::query()->status('diterima')->count());
        $this->assertSame(1, Pendaftaran::query()->jenjang('kb')->count());
        $this->assertSame(0, Pendaftaran::query()->status('ditolak')->count());

        // Kode yang kosong berarti "semua", bukan "tidak ada".
        $this->assertSame(2, Pendaftaran::query()->status('')->count());
    }

    public function test_pencarian_menemukan_lewat_nama_orang_tua(): void
    {
        $this->kirim();

        // Nama orang tua kini di tabel lain, jadi pencariannya lewat relasi.
        $this->assertSame(1, Pendaftaran::query()->cari('Hidayat')->count());
        $this->assertSame(1, Pendaftaran::query()->cari('Siti Aminah')->count());
        $this->assertSame(0, Pendaftaran::query()->cari('Tidak Ada Orangnya')->count());
    }

    public function test_panitia_melihat_data_orang_tua_di_halaman_detail(): void
    {
        $pendaftaran = $this->kirim();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.pendaftaran.show', $pendaftaran))
            ->assertOk()
            ->assertSee('Hidayat Rahman')
            ->assertSee('Siti Aminah')
            ->assertSee('Jl. Winaya Utama No. 20')
            ->assertSee('RA Kelompok A');
    }

    public function test_ekspor_csv_mengambil_data_dari_tabel_tabelnya(): void
    {
        $this->kirim();

        $isi = $this->actingAs(User::factory()->create())
            ->get(route('admin.pendaftaran.export'))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Hidayat Rahman', $isi);
        $this->assertStringContainsString('Siti Aminah', $isi);
        $this->assertStringContainsString('Jl. Winaya Utama No. 20', $isi);
        $this->assertStringContainsString('RA Kelompok A', $isi);
        $this->assertStringContainsString('Menunggu Verifikasi', $isi);
    }

    public function test_ringkasan_menghitung_per_kode_jenjang_dan_status(): void
    {
        Pendaftaran::factory()->count(2)->jenjang('kb')->status('baru')->create();
        Pendaftaran::factory()->jenjang('ra-a')->status('diterima')->create();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Kelompok Bermain (KB)')
            ->assertSee('Menunggu Verifikasi');
    }

    /*
    |--------------------------------------------------------------------------
    | Alamat
    |--------------------------------------------------------------------------
    */

    public function test_alamat_lengkap_melewati_bagian_yang_kosong(): void
    {
        $alamat = Alamat::factory()->create([
            'jalan' => 'Jl. Contoh No. 1',
            'kelurahan' => null,
            'kecamatan' => null,
            'kota' => 'Kota Tasikmalaya',
            'kode_pos' => null,
        ]);

        $this->assertSame('Jl. Contoh No. 1, Kota Tasikmalaya', $alamat->lengkap);
    }
}
