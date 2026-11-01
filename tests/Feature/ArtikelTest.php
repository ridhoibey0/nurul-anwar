<?php

namespace Tests\Feature;

use App\Models\Artikel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArtikelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function dataArtikel(array $ubah = []): array
    {
        return array_merge([
            'judul' => 'Hari Pertama di Kelompok Bermain',
            'kategori' => 'Kegiatan Sekolah',
            'ringkasan' => 'Catatan singkat tentang pekan perkenalan di kelas KB.',
            'isi' => "Pekan pertama selalu ramai.\n\n## Perkenalan\n\nAnak-anak duduk melingkar.",
            'status' => 'terbit',
            'terbit_pada' => '',
        ], $ubah);
    }

    /*
    |--------------------------------------------------------------------------
    | Halaman publik
    |--------------------------------------------------------------------------
    */

    public function test_halaman_blog_hanya_menampilkan_artikel_terbit(): void
    {
        Artikel::factory()->create(['judul' => 'Artikel Sudah Terbit']);
        Artikel::factory()->draf()->create(['judul' => 'Artikel Masih Draf']);
        Artikel::factory()->terjadwal()->create(['judul' => 'Artikel Terjadwal']);

        $this->get('/blog')
            ->assertOk()
            ->assertSee('Artikel Sudah Terbit')
            ->assertDontSee('Artikel Masih Draf')
            ->assertDontSee('Artikel Terjadwal');
    }

    public function test_draf_tidak_bisa_dibuka_lewat_url(): void
    {
        $draf = Artikel::factory()->draf()->create();
        $terjadwal = Artikel::factory()->terjadwal()->create();

        $this->get('/blog/'.$draf->slug)->assertNotFound();
        $this->get('/blog/'.$terjadwal->slug)->assertNotFound();
    }

    public function test_halaman_detail_artikel_menampilkan_isi(): void
    {
        $artikel = Artikel::factory()->create([
            'judul' => 'Belajar Sambil Bermain',
            'isi' => "## Sentra Balok\n\nAnak menyusun menara.",
        ]);

        $this->get('/blog/'.$artikel->slug)
            ->assertOk()
            ->assertSee('Belajar Sambil Bermain')
            ->assertSee('<h2>Sentra Balok</h2>', false)
            ->assertSee('Anak menyusun menara');
    }

    public function test_html_mentah_pada_isi_dibuang(): void
    {
        $artikel = Artikel::factory()->create([
            'isi' => 'Teks biasa <script>alert(1)</script> lanjut.',
        ]);

        $this->get('/blog/'.$artikel->slug)
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_pencarian_blog_menyaring_judul(): void
    {
        Artikel::factory()->create(['judul' => 'Tips Menyiapkan Bekal Sehat']);
        Artikel::factory()->create(['judul' => 'Jadwal Libur Semester']);

        $this->get('/blog?q=bekal')
            ->assertOk()
            ->assertSee('Tips Menyiapkan Bekal Sehat')
            ->assertDontSee('Jadwal Libur Semester');
    }

    public function test_beranda_menyembunyikan_bagian_blog_bila_kosong(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('Bacaan ringan untuk');

        Artikel::factory()->create(['judul' => 'Artikel Pertama Sekolah']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Artikel Pertama Sekolah');
    }

    /*
    |--------------------------------------------------------------------------
    | Panel admin
    |--------------------------------------------------------------------------
    */

    public function test_tamu_tidak_bisa_mengelola_artikel(): void
    {
        $artikel = Artikel::factory()->create();

        $this->get('/admin/artikel')->assertRedirect('/admin/login');
        $this->get('/admin/artikel/baru')->assertRedirect('/admin/login');
        $this->post('/admin/artikel', $this->dataArtikel())->assertRedirect('/admin/login');
        $this->put('/admin/artikel/'.$artikel->slug, $this->dataArtikel())->assertRedirect('/admin/login');
        $this->delete('/admin/artikel/'.$artikel->slug)->assertRedirect('/admin/login');

        $this->assertDatabaseCount('artikel', 1);
    }

    public function test_panitia_dapat_menulis_artikel(): void
    {
        $panitia = User::factory()->create();

        $this->actingAs($panitia)
            ->post('/admin/artikel', $this->dataArtikel())
            ->assertRedirect('/admin/artikel')
            ->assertSessionHas('sukses');

        $artikel = Artikel::query()->firstOrFail();

        $this->assertSame('hari-pertama-di-kelompok-bermain', $artikel->slug);
        $this->assertSame($panitia->id, $artikel->penulis_id);
        $this->assertNotNull($artikel->terbit_pada);
        $this->assertTrue($artikel->sudah_terbit);
    }

    public function test_draf_tidak_diberi_tanggal_terbit(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/admin/artikel', $this->dataArtikel([
                'status' => 'draf',
                'terbit_pada' => '2026-01-01',
            ]))
            ->assertRedirect('/admin/artikel');

        $artikel = Artikel::query()->firstOrFail();

        $this->assertNull($artikel->terbit_pada);
        $this->assertFalse($artikel->sudah_terbit);
    }

    public function test_slug_dibuat_unik_saat_judul_sama(): void
    {
        $panitia = User::factory()->create();

        $this->actingAs($panitia)->post('/admin/artikel', $this->dataArtikel());
        $this->actingAs($panitia)->post('/admin/artikel', $this->dataArtikel());

        $this->assertSame(
            ['hari-pertama-di-kelompok-bermain', 'hari-pertama-di-kelompok-bermain-2'],
            Artikel::query()->orderBy('id')->pluck('slug')->all(),
        );
    }

    public function test_slug_artikel_terbit_tidak_berubah_saat_judul_disunting(): void
    {
        $artikel = Artikel::factory()->create(['slug' => 'judul-lama']);

        $this->actingAs(User::factory()->create())
            ->put('/admin/artikel/'.$artikel->slug, $this->dataArtikel(['judul' => 'Judul Yang Baru Sekali']))
            ->assertRedirect('/admin/artikel');

        $artikel->refresh();

        $this->assertSame('judul-lama', $artikel->slug);
        $this->assertSame('Judul Yang Baru Sekali', $artikel->judul);
    }

    public function test_gambar_sampul_disimpan_ke_folder_publik(): void
    {
        Storage::fake('artikel');

        $this->actingAs(User::factory()->create())
            ->post('/admin/artikel', $this->dataArtikel([
                'gambar' => UploadedFile::fake()->image('sampul.jpg', 1200, 750),
            ]))
            ->assertRedirect('/admin/artikel');

        $artikel = Artikel::query()->firstOrFail();

        $this->assertStringStartsWith('images/artikel/', $artikel->gambar);
        Storage::disk('artikel')->assertExists(basename($artikel->gambar));
    }

    public function test_validasi_menolak_artikel_tanpa_judul(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/admin/artikel', $this->dataArtikel(['judul' => '', 'ringkasan' => '']))
            ->assertSessionHasErrors(['judul', 'ringkasan']);

        $this->assertDatabaseCount('artikel', 0);
    }

    public function test_panitia_dapat_menghapus_artikel_beserta_gambarnya(): void
    {
        Storage::fake('artikel');
        Storage::disk('artikel')->put('sampul-lama.jpg', 'isi');

        $artikel = Artikel::factory()->create(['gambar' => 'images/artikel/sampul-lama.jpg']);

        $this->actingAs(User::factory()->create())
            ->delete('/admin/artikel/'.$artikel->slug)
            ->assertRedirect('/admin/artikel');

        $this->assertDatabaseCount('artikel', 0);
        Storage::disk('artikel')->assertMissing('sampul-lama.jpg');
    }
}
