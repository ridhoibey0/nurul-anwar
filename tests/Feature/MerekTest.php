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
 * Logo dan gambar bagikan: unggahan, kembali ke bawaan, dan dikosongkan.
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

    private function unggahLogo(): string
    {
        $this->simpan(['logo' => UploadedFile::fake()->image('logo.png', 500, 500)])
            ->assertSessionHasNoErrors();

        Pengaturan::terapkanKeConfig();

        return $this->namaBerkas('sekolah_logo');
    }

    /*
    |--------------------------------------------------------------------------
    | Keadaan bawaan
    |--------------------------------------------------------------------------
    */

    public function test_berkas_bawaan_dipakai_selama_belum_pernah_diatur(): void
    {
        $this->assertSame(Merek::BAWAAN, Merek::pilihan('logo'));
        $this->assertSame(Merek::BAWAAN, Merek::pilihan('gambar_bagikan'));

        $this->get('/')
            ->assertOk()
            ->assertSee(Merek::BERKAS_BAWAAN['logo'], false)
            ->assertSee(Merek::BERKAS_BAWAAN['gambar_bagikan'], false);
    }

    public function test_berkas_bawaan_benar_benar_ada_di_dalam_repositori(): void
    {
        $this->assertTrue(File::exists(public_path(Merek::BERKAS_BAWAAN['logo'])));
        $this->assertTrue(File::exists(public_path(Merek::BERKAS_BAWAAN['gambar_bagikan'])));
    }

    /*
    |--------------------------------------------------------------------------
    | Unggahan
    |--------------------------------------------------------------------------
    */

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

        $this->assertSame(Merek::UNGGAHAN, Merek::pilihan('logo'));

        $this->get('/')
            ->assertOk()
            ->assertSee($jalur, false)
            ->assertDontSee(Merek::BERKAS_BAWAAN['logo'], false);
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
        $lama = $this->unggahLogo();

        $this->simpan(['logo' => UploadedFile::fake()->image('kedua.png', 500, 500)]);
        $baru = $this->namaBerkas('sekolah_logo');

        $this->assertNotSame($lama, $baru);
        Storage::disk(Merek::DISK)->assertMissing($lama);
        Storage::disk(Merek::DISK)->assertExists($baru);
    }

    public function test_memilih_berkas_menang_atas_pilihan_keadaan(): void
    {
        // Panitia yang memilih berkas jelas ingin berkas itu yang dipakai,
        // walau pilihan radionya masih tertinggal di "tanpa logo".
        $this->simpan([
            'logo' => UploadedFile::fake()->image('logo.png', 500, 500),
            'logo_pilihan' => Merek::KOSONG,
        ])->assertSessionHasNoErrors();

        Pengaturan::terapkanKeConfig();

        $this->assertSame(Merek::UNGGAHAN, Merek::pilihan('logo'));
        $this->assertNotNull(logo_url());
    }

    /*
    |--------------------------------------------------------------------------
    | Dikosongkan
    |--------------------------------------------------------------------------
    */

    public function test_logo_bisa_dikosongkan_sama_sekali(): void
    {
        $this->simpan(['logo_pilihan' => Merek::KOSONG])->assertSessionHasNoErrors();

        // String kosong, bukan null. Null berarti "belum pernah diatur" dan
        // akan memunculkan lagi logo bawaan.
        $this->assertSame('', Pengaturan::ambil('sekolah_logo'));

        Pengaturan::terapkanKeConfig();

        $this->assertSame(Merek::KOSONG, Merek::pilihan('logo'));
        $this->assertNull(Merek::berkas('logo'));
        $this->assertNull(logo_url());
    }

    public function test_tanpa_logo_website_menampilkan_nama_sekolah_saja(): void
    {
        $this->simpan(['logo_pilihan' => Merek::KOSONG]);
        Pengaturan::terapkanKeConfig();

        $this->get('/')
            ->assertOk()
            ->assertDontSee(Merek::BERKAS_BAWAAN['logo'], false)
            // Navbar dan footer tetap menyebut nama sekolah, jadi tidak ada
            // bagian yang jadi kosong melompong.
            ->assertSee(config('sekolah.nama_pendek'));
    }

    public function test_tanpa_logo_halaman_tidak_memuat_gambar_rusak(): void
    {
        $this->simpan(['logo_pilihan' => Merek::KOSONG]);
        Pengaturan::terapkanKeConfig();

        // src kosong membuat peramban memuat ulang halaman itu sendiri sebagai
        // gambar, jadi tag img-nya harus benar-benar tidak ditulis.
        $this->get('/')->assertOk()->assertDontSee('<img src=""', false);
        $this->get('/admin/login')->assertOk()->assertDontSee('<img src=""', false);
    }

    public function test_tanpa_logo_ikon_tab_jatuh_ke_favicon_ico(): void
    {
        $this->simpan(['logo_pilihan' => Merek::KOSONG]);
        Pengaturan::terapkanKeConfig();

        $this->get('/')
            ->assertOk()
            ->assertSee('favicon.ico', false)
            ->assertDontSee('apple-touch-icon', false);
    }

    public function test_mengosongkan_logo_menghapus_berkas_unggahannya(): void
    {
        $nama = $this->unggahLogo();

        $this->simpan(['logo_pilihan' => Merek::KOSONG])->assertSessionHasNoErrors();

        Storage::disk(Merek::DISK)->assertMissing($nama);
    }

    public function test_gambar_bagikan_bisa_dikosongkan_dan_tag_og_image_hilang(): void
    {
        $this->get('/')->assertOk()->assertSee('property="og:image"', false);

        $this->simpan(['gambar_bagikan_pilihan' => Merek::KOSONG])->assertSessionHasNoErrors();
        Pengaturan::terapkanKeConfig();

        $this->assertNull(gambar_bagikan_url());

        $this->get('/')
            ->assertOk()
            // og:image yang isinya kosong membuat sebagian aplikasi
            // menampilkan kotak gambar rusak, jadi tag-nya dibuang sekalian.
            ->assertDontSee('property="og:image"', false)
            ->assertDontSee('content=""', false)
            // Kartu ringkas lebih pas daripada kartu bergambar besar yang
            // gambarnya tidak ada.
            ->assertSee('name="twitter:card" content="summary"', false);
    }

    public function test_tanpa_gambar_bagikan_data_terstruktur_tidak_menyebut_image(): void
    {
        $this->simpan(['gambar_bagikan_pilihan' => Merek::KOSONG]);
        Pengaturan::terapkanKeConfig();

        // array_filter di partials/data-terstruktur membuang kunci bernilai
        // null, jadi JSON-LD-nya tetap sah, bukan "image": null.
        $this->get('/')->assertOk()->assertDontSee('"image"', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Kembali ke bawaan
    |--------------------------------------------------------------------------
    */

    public function test_logo_bisa_dikembalikan_ke_bawaan(): void
    {
        $nama = $this->unggahLogo();

        $this->simpan(['logo_pilihan' => Merek::BAWAAN])->assertSessionHasNoErrors();

        // Disimpan null: null berarti "pakai berkas bawaan", dan
        // terapkanKeConfig() memang melewati baris bernilai null.
        $this->assertNull(Pengaturan::ambil('sekolah_logo'));
        Storage::disk(Merek::DISK)->assertMissing($nama);

        Pengaturan::terapkanKeConfig();

        $this->assertSame(Merek::BAWAAN, Merek::pilihan('logo'));
        $this->get('/')->assertOk()->assertSee(Merek::BERKAS_BAWAAN['logo'], false);
    }

    public function test_logo_yang_dikosongkan_bisa_dikembalikan_ke_bawaan(): void
    {
        $this->simpan(['logo_pilihan' => Merek::KOSONG]);
        Pengaturan::terapkanKeConfig();
        $this->assertNull(logo_url());

        $this->simpan(['logo_pilihan' => Merek::BAWAAN])->assertSessionHasNoErrors();
        Pengaturan::terapkanKeConfig();

        $this->assertSame(Merek::BAWAAN, Merek::pilihan('logo'));
        $this->assertNotNull(logo_url());
    }

    public function test_berkas_bawaan_tidak_ikut_terhapus(): void
    {
        // Penjaga di PengaturanController::hapusMerek(). Tanpa ini, jalur yang
        // menunjuk ke berkas bawaan bisa ikut terhapus dari public/ dan
        // website kehilangan logonya untuk selamanya.
        $this->assertFalse(Merek::unggahan(Merek::BERKAS_BAWAAN['logo']));
        $this->assertFalse(Merek::unggahan(Merek::BERKAS_BAWAAN['gambar_bagikan']));
        $this->assertFalse(Merek::unggahan(null));
        $this->assertFalse(Merek::unggahan(''));
        $this->assertTrue(Merek::unggahan('images/merek/logo-20261102-abcd1234.png'));
    }

    /*
    |--------------------------------------------------------------------------
    | Tidak disentuh
    |--------------------------------------------------------------------------
    */

    public function test_menyimpan_tanpa_menyentuh_gambar_tidak_mengubah_logo(): void
    {
        $nama = $this->unggahLogo();

        // Panitia cuma mengganti nomor telepon; logonya tidak boleh hilang.
        $this->simpan(['telepon' => '(0265) 111111'])->assertSessionHasNoErrors();

        $this->assertSame($nama, $this->namaBerkas('sekolah_logo'));
        Storage::disk(Merek::DISK)->assertExists($nama);
    }

    public function test_memilih_unggahan_tanpa_berkas_baru_mempertahankan_yang_sekarang(): void
    {
        $nama = $this->unggahLogo();

        $this->simpan(['logo_pilihan' => Merek::UNGGAHAN])->assertSessionHasNoErrors();

        $this->assertSame($nama, $this->namaBerkas('sekolah_logo'));
        Storage::disk(Merek::DISK)->assertExists($nama);
    }

    public function test_mengubah_logo_tidak_menyentuh_gambar_bagikan(): void
    {
        $this->simpan(['gambar_bagikan' => UploadedFile::fake()->image('bagikan.jpg', 1600, 900)]);
        $bagikan = $this->namaBerkas('sekolah_gambar_bagikan');

        $this->simpan(['logo_pilihan' => Merek::KOSONG])->assertSessionHasNoErrors();

        $this->assertSame($bagikan, $this->namaBerkas('sekolah_gambar_bagikan'));
        Storage::disk(Merek::DISK)->assertExists($bagikan);
    }

    /*
    |--------------------------------------------------------------------------
    | Penolakan
    |--------------------------------------------------------------------------
    */

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

    public function test_pilihan_keadaan_yang_tidak_dikenal_ditolak(): void
    {
        $this->simpan(['logo_pilihan' => 'hapus-semuanya'])
            ->assertSessionHasErrors('logo_pilihan');

        $this->assertDatabaseCount('pengaturan', 0);
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

    /*
    |--------------------------------------------------------------------------
    | Gambar bagikan & halaman panel
    |--------------------------------------------------------------------------
    */

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
            ->assertDontSee(Merek::BERKAS_BAWAAN['gambar_bagikan'], false);
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

        $this->unggahLogo();

        $this->get('/')
            ->assertOk()
            ->assertSee('<link rel="icon" href="'.logo_url().'" type="image/png">', false);
    }

    public function test_halaman_identitas_sekolah_menampilkan_pilihan_sesuai_keadaan(): void
    {
        $halaman = $this->actingAs(User::factory()->create())
            ->get('/admin/pengaturan/sekolah')
            ->assertOk()
            ->assertSee('Logo &amp; gambar', false)
            ->assertSee('Unggah logo')
            ->assertSee('Tanpa logo, cukup nama sekolah');

        // Selama belum ada unggahan, pilihan "logo yang sudah diunggah" tidak
        // ada isinya, jadi tidak ditawarkan.
        $halaman->assertDontSee('Logo yang sudah diunggah');

        $this->unggahLogo();

        $this->actingAs(User::factory()->create())
            ->get('/admin/pengaturan/sekolah')
            ->assertOk()
            ->assertSee('Ganti logo')
            ->assertSee('Logo yang sudah diunggah');
    }

    public function test_halaman_identitas_sekolah_menandai_keadaan_yang_sedang_berlaku(): void
    {
        $this->simpan(['logo_pilihan' => Merek::KOSONG]);
        Pengaturan::terapkanKeConfig();

        $this->actingAs(User::factory()->create())
            ->get('/admin/pengaturan/sekolah')
            ->assertOk()
            ->assertSee('value="'.Merek::KOSONG.'" checked', false)
            ->assertSee('Tanpa logo');
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
