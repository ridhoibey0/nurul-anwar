<?php

namespace Tests\Feature;

use App\Models\Pengaturan;
use App\Models\User;
use App\Support\Gambar;
use App\Support\Merek;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Unggahan logo dan gambar bagikan dari Panel Admin > Identitas Sekolah.
 */
class MerekTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Berkas uji ditulis ke disk tiruan, bukan ke public/images/merek yang
        // sungguhan, supaya pengujian tidak meninggalkan sampah di repositori.
        Storage::fake(Merek::DISK);
    }

    /**
     * Isian wajib di formulir identitas sekolah. Pengujian di berkas ini hanya
     * mengurusi gambarnya, tapi formulirnya satu, jadi sisanya harus ikut.
     *
     * @param  array<string, mixed>  $ubah
     * @return array<string, mixed>
     */
    private function isian(array $ubah = []): array
    {
        return array_merge([
            'nama' => 'RA Nurul Anwar',
            'nama_pendek' => 'RA Nurul Anwar',
            'tagline' => 'Menumbuhkan Cinta Belajar Sejak Dini',
            'tahun_berdiri' => '2005',
            'akreditasi' => 'B',
            'alamat' => 'Jl. Winaya Utama No. 20',
            'kota' => 'Kota Tasikmalaya',
            'provinsi' => 'Jawa Barat',
            'jam_operasional' => 'Senin - Jumat, 07.00 - 13.00 WIB',
            'telepon' => '(0265) 338279',
            'whatsapp' => '081234567890',
            'email' => 'info@ranurulanwar.sch.id',
            'instagram' => 'ra.nurulanwar',
            'instagram_url' => '',
            'facebook_url' => '',
            'youtube_url' => '',
            'peta_lat' => '-7.3562979',
            'peta_lng' => '108.2236263',
            'peta_url' => '',
        ], $ubah);
    }

    private function simpan(array $ubah = [])
    {
        return $this->actingAs(User::factory()->create())
            ->patch('/admin/pengaturan/sekolah', $this->isian($ubah));
    }

    /** Nama berkas saja, tanpa "images/merek/" di depannya. */
    private function namaBerkas(string $kunci): string
    {
        return basename((string) Pengaturan::ambil($kunci));
    }

    public function test_logo_bawaan_dipakai_selama_belum_ada_yang_diunggah(): void
    {
        $this->assertTrue(Merek::bawaan('logo'));
        $this->assertTrue(Merek::bawaan('gambar_bagikan'));

        $this->get('/')
            ->assertOk()
            ->assertSee(Merek::BAWAAN['logo'], false)
            ->assertSee(Merek::BAWAAN['gambar_bagikan'], false);
    }

    public function test_logo_yang_diunggah_tersimpan_dan_menggantikan_logo_bawaan(): void
    {
        $this->simpan(['logo' => UploadedFile::fake()->image('logo-sekolah.png', 900, 900)])
            ->assertRedirect('/admin/pengaturan/sekolah')
            ->assertSessionHasNoErrors();

        $jalur = Pengaturan::ambil('sekolah_logo');

        $this->assertNotNull($jalur);
        $this->assertStringStartsWith('images/merek/', $jalur);
        Storage::disk(Merek::DISK)->assertExists(basename($jalur));

        Pengaturan::terapkanKeConfig();

        $this->assertFalse(Merek::bawaan('logo'));

        $this->get('/')
            ->assertOk()
            ->assertSee($jalur, false)
            ->assertDontSee(Merek::BAWAAN['logo'], false);
    }

    public function test_logo_disimpan_sebagai_png_persegi_walau_aslinya_memanjang(): void
    {
        $this->simpan(['logo' => UploadedFile::fake()->image('spanduk.png', 1600, 400)])
            ->assertSessionHasNoErrors();

        $berkas = Storage::disk(Merek::DISK)->path($this->namaBerkas('sekolah_logo'));
        [$lebar, $tinggi, $jenis] = getimagesize($berkas);

        // Persegi supaya navbar dan footer bisa memasangnya dengan ukuran
        // tetap: logo tidak gepeng, dan tata letak tidak bergeser saat
        // gambarnya selesai dimuat.
        $this->assertSame(Gambar::SISI_LOGO, $lebar);
        $this->assertSame(Gambar::SISI_LOGO, $tinggi);
        $this->assertSame(IMAGETYPE_PNG, $jenis);
    }

    public function test_logo_jpg_ikut_dialihkan_jadi_png(): void
    {
        // PNG dipilih apa pun masukannya supaya logo berlatar transparan tidak
        // berubah jadi berlatar putih.
        $this->simpan(['logo' => UploadedFile::fake()->image('logo.jpg', 600, 600)])
            ->assertSessionHasNoErrors();

        $nama = $this->namaBerkas('sekolah_logo');

        $this->assertStringEndsWith('.png', $nama);
        $this->assertSame(
            IMAGETYPE_PNG,
            getimagesize(Storage::disk(Merek::DISK)->path($nama))[2],
        );
    }

    public function test_mengunggah_logo_baru_menghapus_berkas_logo_sebelumnya(): void
    {
        $this->simpan(['logo' => UploadedFile::fake()->image('pertama.png', 500, 500)]);
        $lama = $this->namaBerkas('sekolah_logo');

        $this->simpan(['logo' => UploadedFile::fake()->image('kedua.png', 500, 500)]);
        $baru = $this->namaBerkas('sekolah_logo');

        $this->assertNotSame($lama, $baru);
        Storage::disk(Merek::DISK)->assertMissing($lama);
        Storage::disk(Merek::DISK)->assertExists($baru);
    }

    public function test_logo_bisa_dikembalikan_ke_bawaan(): void
    {
        $this->simpan(['logo' => UploadedFile::fake()->image('logo.png', 500, 500)]);
        $nama = $this->namaBerkas('sekolah_logo');

        $this->simpan(['hapus_logo' => '1'])->assertSessionHasNoErrors();

        // Disimpan null, bukan string kosong: null berarti "pakai berkas
        // bawaan", dan terapkanKeConfig() memang melewati baris bernilai null.
        $this->assertNull(Pengaturan::ambil('sekolah_logo'));
        Storage::disk(Merek::DISK)->assertMissing($nama);

        Pengaturan::terapkanKeConfig();

        $this->assertTrue(Merek::bawaan('logo'));
        $this->get('/')->assertOk()->assertSee(Merek::BAWAAN['logo'], false);
    }

    public function test_berkas_bawaan_tidak_ikut_terhapus_saat_dikembalikan(): void
    {
        // Penjaga di PengaturanController::hapusMerek(). Tanpa ini, jalur yang
        // menunjuk ke berkas bawaan bisa ikut terhapus dari public/ dan
        // website kehilangan logonya untuk selamanya.
        $this->assertFalse(Merek::unggahan(Merek::BAWAAN['logo']));
        $this->assertFalse(Merek::unggahan(Merek::BAWAAN['gambar_bagikan']));
        $this->assertFalse(Merek::unggahan(null));
        $this->assertTrue(Merek::unggahan('images/merek/logo-20261102-abcd1234.png'));

        // Berkas bawaannya sendiri harus memang ada di dalam repositori.
        $this->assertTrue(File::exists(public_path(Merek::BAWAAN['logo'])));
        $this->assertTrue(File::exists(public_path(Merek::BAWAAN['gambar_bagikan'])));
    }

    public function test_menyimpan_tanpa_menyentuh_gambar_tidak_mengubah_logo(): void
    {
        $this->simpan(['logo' => UploadedFile::fake()->image('logo.png', 500, 500)]);
        $nama = $this->namaBerkas('sekolah_logo');

        // Panitia cuma mengganti nomor telepon; logonya tidak boleh hilang.
        $this->simpan(['telepon' => '(0265) 111111'])->assertSessionHasNoErrors();

        $this->assertSame($nama, $this->namaBerkas('sekolah_logo'));
        Storage::disk(Merek::DISK)->assertExists($nama);
    }

    public function test_berkas_svg_ditolak(): void
    {
        // Aturan "image" bawaan Laravel meloloskan SVG; yang menolaknya adalah
        // daftar mimes. SVG bisa memuat <script> dan disajikan dari domain
        // sekolah sendiri, jadi penolakan ini disengaja.
        $this->simpan(['logo' => UploadedFile::fake()->create('logo.svg', 8, 'image/svg+xml')])
            ->assertSessionHasErrors('logo');

        $this->assertNull(Pengaturan::ambil('sekolah_logo'));
    }

    public function test_berkas_bukan_gambar_ditolak(): void
    {
        $this->simpan(['logo' => UploadedFile::fake()->create('logo.pdf', 40, 'application/pdf')])
            ->assertSessionHasErrors('logo');

        $this->assertNull(Pengaturan::ambil('sekolah_logo'));
    }

    public function test_logo_yang_terlalu_besar_ditolak(): void
    {
        $this->simpan(['logo' => UploadedFile::fake()->image('logo.png')->size(2100)])
            ->assertSessionHasErrors('logo');

        $this->assertNull(Pengaturan::ambil('sekolah_logo'));
    }

    public function test_isian_teks_tidak_ikut_tersimpan_saat_gambarnya_ditolak(): void
    {
        $this->simpan([
            'nama' => 'Nama Yang Baru',
            'logo' => UploadedFile::fake()->create('logo.svg', 8, 'image/svg+xml'),
        ])->assertSessionHasErrors('logo');

        // Gagal berlaku untuk seluruh formulir, bukan separuh tersimpan.
        $this->assertDatabaseCount('pengaturan', 0);
    }

    public function test_gambar_bagikan_yang_diunggah_dipakai_sebagai_og_image(): void
    {
        $this->simpan(['gambar_bagikan' => UploadedFile::fake()->image('bagikan.jpg', 1600, 900)])
            ->assertSessionHasNoErrors();

        $jalur = Pengaturan::ambil('sekolah_gambar_bagikan');

        $this->assertNotNull($jalur);
        Storage::disk(Merek::DISK)->assertExists(basename($jalur));
        $this->assertStringEndsWith('.jpg', $jalur);

        Pengaturan::terapkanKeConfig();

        $this->get('/')
            ->assertOk()
            ->assertSee('property="og:image" content="'.gambar_bagikan_url().'"', false)
            ->assertDontSee(Merek::BAWAAN['gambar_bagikan'], false);
    }

    public function test_gambar_bagikan_bisa_dikembalikan_ke_bawaan(): void
    {
        $this->simpan(['gambar_bagikan' => UploadedFile::fake()->image('bagikan.jpg', 1600, 900)]);
        $nama = $this->namaBerkas('sekolah_gambar_bagikan');

        $this->simpan(['hapus_gambar_bagikan' => '1'])->assertSessionHasNoErrors();

        $this->assertNull(Pengaturan::ambil('sekolah_gambar_bagikan'));
        Storage::disk(Merek::DISK)->assertMissing($nama);

        Pengaturan::terapkanKeConfig();

        $this->assertTrue(Merek::bawaan('gambar_bagikan'));
    }

    public function test_logo_dan_gambar_bagikan_bisa_diunggah_sekaligus(): void
    {
        $this->simpan([
            'logo' => UploadedFile::fake()->image('logo.png', 500, 500),
            'gambar_bagikan' => UploadedFile::fake()->image('bagikan.jpg', 1600, 900),
        ])->assertSessionHasNoErrors();

        Storage::disk(Merek::DISK)->assertExists($this->namaBerkas('sekolah_logo'));
        Storage::disk(Merek::DISK)->assertExists($this->namaBerkas('sekolah_gambar_bagikan'));
    }

    public function test_logo_yang_diunggah_sekaligus_jadi_ikon_tab(): void
    {
        // Supaya panitia tidak perlu mengunggah favicon tersendiri.
        $this->get('/')->assertOk()->assertDontSee('apple-touch-icon', false);

        $this->simpan(['logo' => UploadedFile::fake()->image('logo.png', 500, 500)]);
        Pengaturan::terapkanKeConfig();

        $this->get('/')
            ->assertOk()
            ->assertSee('<link rel="icon" href="'.logo_url().'" type="image/png">', false);
    }

    public function test_halaman_identitas_sekolah_menampilkan_gambar_yang_dipakai(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/pengaturan/sekolah')
            ->assertOk()
            ->assertSee('Logo &amp; gambar', false)
            ->assertSee('Unggah logo')
            // Selama masih bawaan, pilihan "kembalikan ke bawaan" tidak ada
            // gunanya, jadi tidak ditampilkan.
            ->assertDontSee('name="hapus_logo"', false);

        $this->simpan(['logo' => UploadedFile::fake()->image('logo.png', 500, 500)]);
        Pengaturan::terapkanKeConfig();

        $this->actingAs(User::factory()->create())
            ->get('/admin/pengaturan/sekolah')
            ->assertOk()
            ->assertSee('Ganti logo')
            ->assertSee('name="hapus_logo"', false);
    }

    public function test_tamu_tidak_bisa_mengunggah_logo(): void
    {
        $this->patch('/admin/pengaturan/sekolah', $this->isian([
            'logo' => UploadedFile::fake()->image('logo.png', 500, 500),
        ]))->assertRedirect('/admin/login');

        $this->assertDatabaseCount('pengaturan', 0);
        $this->assertEmpty(Storage::disk(Merek::DISK)->allFiles());
    }
}
