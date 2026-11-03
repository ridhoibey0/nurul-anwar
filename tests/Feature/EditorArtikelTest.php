<?php

namespace Tests\Feature;

use App\Models\Artikel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menulis artikel lewat editor WYSIWYG.
 *
 * Yang diuji: HTML dari editor tersimpan bersih, tulisan lama yang masih
 * Markdown tetap tampil benar, dan kotak yang dikosongkan ditolak.
 */
class EditorArtikelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $ubah
     * @return array<string, mixed>
     */
    private function isian(array $ubah = []): array
    {
        return array_merge([
            'judul' => 'Pekan Pertama di Sekolah',
            'kategori' => 'Kegiatan Sekolah',
            'ringkasan' => 'Cerita hari pertama anak-anak masuk sekolah.',
            'isi' => '<div>Anak-anak datang pagi-pagi.</div>',
            'status' => 'draf',
        ], $ubah);
    }

    private function simpan(array $ubah = [])
    {
        return $this->actingAs(User::factory()->create())
            ->post(route('admin.artikel.store'), $this->isian($ubah));
    }

    /*
    |--------------------------------------------------------------------------
    | Penyimpanan
    |--------------------------------------------------------------------------
    */

    public function test_html_dari_editor_tersimpan_sebagai_html(): void
    {
        $this->simpan([
            'isi' => '<div>Halo <strong>semua</strong></div><div>Baris kedua</div>',
        ])->assertSessionHasNoErrors();

        $artikel = Artikel::query()->firstOrFail();

        // div diubah jadi p saat disimpan, bukan saat ditampilkan.
        $this->assertStringContainsString('<p>Halo <strong>semua</strong></p>', $artikel->isi);
        $this->assertStringNotContainsString('<div', $artikel->isi);
    }

    public function test_skrip_dari_editor_tidak_pernah_sampai_ke_database(): void
    {
        $this->simpan([
            'isi' => '<div>Tulisan biasa</div><script>fetch("https://jahat.test")</script>',
        ])->assertSessionHasNoErrors();

        $artikel = Artikel::query()->firstOrFail();

        // Disaring saat menyimpan, jadi yang ada di kolomnya memang sudah
        // bersih -- bukan dibersihkan ulang tiap halaman dibuka.
        $this->assertStringNotContainsString('script', $artikel->isi);
        $this->assertStringNotContainsString('jahat.test', $artikel->isi);
        $this->assertStringContainsString('Tulisan biasa', $artikel->isi);
    }

    public function test_isi_tersimpan_tampil_apa_adanya_di_halaman_blog(): void
    {
        $this->simpan([
            'status' => 'terbit',
            'isi' => '<h1>Sentra Balok</h1><div>Anak menyusun menara.</div>',
        ])->assertSessionHasNoErrors();

        $artikel = Artikel::query()->firstOrFail();

        $this->get('/blog/'.$artikel->slug)
            ->assertOk()
            // h1 diturunkan jadi h2: judul artikel sudah memakai h1.
            ->assertSee('<h2>Sentra Balok</h2>', false)
            ->assertSee('Anak menyusun menara');
    }

    /*
    |--------------------------------------------------------------------------
    | Kotak kosong
    |--------------------------------------------------------------------------
    */

    public function test_editor_kosong_ditolak(): void
    {
        // Yang dikirim Trix saat kotaknya tidak diisi. Tanpa penjagaan ini,
        // aturan required lolos padahal tidak ada tulisannya.
        $this->simpan(['isi' => '<div><br></div>'])
            ->assertSessionHasErrors('isi_teks');

        $this->assertDatabaseCount('artikel', 0);
    }

    public function test_editor_berisi_spasi_saja_ditolak(): void
    {
        $this->simpan(['isi' => '<div>   </div><div><br></div>'])
            ->assertSessionHasErrors('isi_teks');

        $this->assertDatabaseCount('artikel', 0);
    }

    public function test_pesan_galatnya_menyebut_isi_artikel(): void
    {
        $this->simpan(['isi' => '<div><br></div>'])->assertSessionHasErrors('isi_teks');

        $galat = session('errors')->get('isi_teks')[0];

        // Bukan "isi teks", yang tidak ada di layar panitia.
        $this->assertStringContainsStringIgnoringCase('isi artikel', $galat);
    }

    public function test_kolom_bantu_tidak_ikut_tersimpan(): void
    {
        $this->simpan()->assertSessionHasNoErrors();

        // isi_teks cuma alat bantu validasi, bukan kolom di tabel artikel.
        $this->assertArrayNotHasKey('isi_teks', Artikel::query()->firstOrFail()->getAttributes());
    }

    /*
    |--------------------------------------------------------------------------
    | Tulisan lama yang masih Markdown
    |--------------------------------------------------------------------------
    */

    public function test_artikel_lama_berformat_markdown_tetap_tampil_benar(): void
    {
        $artikel = Artikel::factory()->create([
            'status' => 'terbit',
            'terbit_pada' => now()->subDay(),
            'isi' => "## Sentra Balok\n\nAnak menyusun menara.",
        ]);

        $this->get('/blog/'.$artikel->slug)
            ->assertOk()
            ->assertSee('<h2>Sentra Balok</h2>', false)
            ->assertSee('Anak menyusun menara');
    }

    public function test_formulir_sunting_memuat_isi_sebagai_html(): void
    {
        $artikel = Artikel::factory()->create([
            'isi' => "## Sentra Balok\n\nAnak menyusun menara.",
        ]);

        // Editor menerima HTML, jadi tulisan Markdown lama dialihkan dulu
        // supaya tidak tampil sebagai "## Sentra Balok" mentah di kotaknya.
        $this->actingAs(User::factory()->create())
            ->get(route('admin.artikel.edit', $artikel))
            ->assertOk()
            ->assertSee('trix-editor', false)
            ->assertSee('Sentra Balok');
    }

    /*
    |--------------------------------------------------------------------------
    | Halaman
    |--------------------------------------------------------------------------
    */

    public function test_editor_hanya_dimuat_di_halaman_tulis_artikel(): void
    {
        $panitia = User::factory()->create();

        // Trix sekitar 200 KB; tidak boleh ikut di halaman yang tidak
        // memakainya, apalagi di halaman publik.
        $this->actingAs($panitia)
            ->get(route('admin.artikel.create'))
            ->assertOk()
            ->assertSee('<trix-editor', false);

        $this->actingAs($panitia)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('trix-editor', false);

        $this->get('/')->assertOk()->assertDontSee('trix', false);
    }

    public function test_tamu_tidak_bisa_membuka_editor(): void
    {
        $this->get(route('admin.artikel.create'))->assertRedirect(route('admin.login'));
    }
}
