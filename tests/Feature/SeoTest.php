<?php

namespace Tests\Feature;

use App\Models\Artikel;
use App\Models\Pendaftaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Alamat kanonik
    |--------------------------------------------------------------------------
    */

    public function test_setiap_halaman_menyebut_alamat_kanoniknya(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.url('/').'">', false);
    }

    public function test_pencarian_blog_dikanonikkan_ke_halaman_blog_biasa(): void
    {
        Artikel::factory()->create();

        // Tanpa ini, /blog?q=apa pun terindeks sebagai halaman kembar.
        $this->get('/blog?q=bekal')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.url('/blog').'">', false);
    }

    public function test_halaman_kedua_blog_tetap_berdiri_sendiri(): void
    {
        Artikel::factory()->count(12)->create();

        // Halaman 2 memuat artikel yang tidak ada di halaman 1, jadi tidak
        // boleh dikanonikkan ke halaman 1.
        $this->get('/blog?page=2')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.url('/blog').'?page=2">', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Pratinjau tautan
    |--------------------------------------------------------------------------
    */

    public function test_beranda_punya_gambar_pratinjau(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('property="og:image" content="'.asset('images/og.jpg').'"', false)
            ->assertSee('property="og:locale" content="id_ID"', false);
    }

    public function test_artikel_memakai_gambar_sampulnya_sendiri_sebagai_pratinjau(): void
    {
        $artikel = Artikel::factory()->create(['gambar' => 'images/artikel/sampul.jpg']);

        $this->get('/blog/'.$artikel->slug)
            ->assertOk()
            ->assertSee('property="og:type" content="article"', false)
            ->assertSee('property="og:image" content="'.asset('images/artikel/sampul.jpg').'"', false);
    }

    public function test_artikel_tanpa_sampul_jatuh_ke_gambar_bawaan(): void
    {
        $artikel = Artikel::factory()->create(['gambar' => null]);

        $this->get('/blog/'.$artikel->slug)
            ->assertOk()
            ->assertSee('property="og:image" content="'.asset('images/og.jpg').'"', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Data terstruktur
    |--------------------------------------------------------------------------
    */

    public function test_beranda_memuat_data_terstruktur_sekolah(): void
    {
        $isi = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('"@type":"EducationalOrganization"', $isi);
        $this->assertStringContainsString('"telephone":"'.config('sekolah.telepon').'"', $isi);

        // Tautan media sosial masih domain polos, jadi tidak boleh diklaim.
        $this->assertStringNotContainsString('"sameAs"', $isi);
    }

    public function test_artikel_memuat_data_terstruktur_blogposting(): void
    {
        $artikel = Artikel::factory()->create(['judul' => 'Catatan Pekan Perkenalan']);

        $isi = $this->get('/blog/'.$artikel->slug)->assertOk()->getContent();

        $this->assertStringContainsString('"@type":"BlogPosting"', $isi);
        $this->assertStringContainsString('"headline":"Catatan Pekan Perkenalan"', $isi);
        $this->assertStringContainsString('"datePublished"', $isi);
    }

    /*
    |--------------------------------------------------------------------------
    | Sitemap & robots
    |--------------------------------------------------------------------------
    */

    public function test_sitemap_memuat_halaman_publik_dan_artikel_terbit(): void
    {
        $terbit = Artikel::factory()->create(['judul' => 'Artikel Terbit']);
        $draf = Artikel::factory()->draf()->create(['judul' => 'Artikel Draf']);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('home'), false)
            ->assertSee(route('ppdb.info'), false)
            ->assertSee(route('blog.show', $terbit->slug), false)
            ->assertDontSee(route('blog.show', $draf->slug), false);
    }

    public function test_sitemap_tidak_memuat_halaman_konfirmasi(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee(route('ppdb.selesai'), false);
    }

    public function test_robots_menunjuk_ke_sitemap_dan_menutup_panel_admin(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Disallow: /ppdb/selesai')
            ->assertSee('Sitemap: '.route('sitemap'));
    }

    /*
    |--------------------------------------------------------------------------
    | Halaman yang tidak boleh terindeks
    |--------------------------------------------------------------------------
    */

    public function test_halaman_konfirmasi_ditandai_noindex(): void
    {
        $pendaftaran = Pendaftaran::factory()->create();

        $this->withSession(['pendaftaran_id' => $pendaftaran->id])
            ->get('/ppdb/selesai')
            ->assertOk()
            ->assertSee('name="robots" content="noindex, nofollow"', false);
    }

    public function test_panel_admin_ditandai_noindex(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('name="robots" content="noindex, nofollow"', false);
    }

    public function test_halaman_publik_tidak_ikut_ditandai_noindex(): void
    {
        $this->get('/')->assertOk()->assertDontSee('noindex', false);
        $this->get('/blog')->assertOk()->assertDontSee('noindex', false);
        $this->get('/ppdb')->assertOk()->assertDontSee('noindex', false);
    }
}
