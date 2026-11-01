<?php

namespace Tests\Feature;

use App\Models\Galeri;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GaleriTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Halaman depan
    |--------------------------------------------------------------------------
    */

    public function test_bagian_keseharian_tersembunyi_selama_belum_ada_foto(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('Sehari di sekolah');
    }

    public function test_beranda_hanya_menampilkan_foto_yang_ditandai_tampil(): void
    {
        Galeri::factory()->create(['judul' => 'Latihan menulis di kelas']);
        Galeri::factory()->disembunyikan()->create(['judul' => 'Foto yang disembunyikan']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Latihan menulis di kelas')
            ->assertDontSee('Foto yang disembunyikan');
    }

    public function test_foto_tanpa_judul_tetap_punya_alt_text(): void
    {
        Galeri::factory()->tanpaJudul()->create();

        $this->get('/')
            ->assertOk()
            ->assertSee('alt="Kegiatan di '.config('sekolah.nama').'"', false);
    }

    public function test_urutan_foto_di_beranda_mengikuti_kolom_urutan(): void
    {
        Galeri::factory()->create(['judul' => 'Foto Ketiga', 'urutan' => 3]);
        Galeri::factory()->create(['judul' => 'Foto Pertama', 'urutan' => 1]);
        Galeri::factory()->create(['judul' => 'Foto Kedua', 'urutan' => 2]);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['Foto Pertama', 'Foto Kedua', 'Foto Ketiga']);
    }

    /*
    |--------------------------------------------------------------------------
    | Panel admin
    |--------------------------------------------------------------------------
    */

    public function test_tamu_tidak_bisa_mengelola_galeri(): void
    {
        $foto = Galeri::factory()->create();

        $this->get('/admin/galeri')->assertRedirect('/admin/login');
        $this->post('/admin/galeri')->assertRedirect('/admin/login');
        $this->put('/admin/galeri/'.$foto->id, ['judul' => 'Diubah'])->assertRedirect('/admin/login');
        $this->delete('/admin/galeri/'.$foto->id)->assertRedirect('/admin/login');

        $this->assertDatabaseCount('galeri', 1);
    }

    public function test_panitia_dapat_mengunggah_beberapa_foto_sekaligus(): void
    {
        Storage::fake('galeri');

        $this->actingAs(User::factory()->create())
            ->post('/admin/galeri', [
                'foto' => [
                    UploadedFile::fake()->image('kegiatan-1.jpg', 2400, 1800),
                    UploadedFile::fake()->image('kegiatan-2.jpg', 1800, 2400),
                ],
            ])
            ->assertRedirect('/admin/galeri')
            ->assertSessionHas('sukses');

        $this->assertDatabaseCount('galeri', 2);

        foreach (Galeri::all() as $foto) {
            $this->assertStringStartsWith('images/galeri/', $foto->gambar);
            Storage::disk('galeri')->assertExists(basename($foto->gambar));
        }
    }

    public function test_foto_besar_diperkecil_saat_diunggah(): void
    {
        Storage::fake('galeri');

        $this->actingAs(User::factory()->create())
            ->post('/admin/galeri', [
                'foto' => [UploadedFile::fake()->image('besar.jpg', 3200, 2400)],
            ])
            ->assertRedirect('/admin/galeri');

        $berkas = Storage::disk('galeri')->path(basename(Galeri::query()->firstOrFail()->gambar));
        [$lebar, $tinggi] = getimagesize($berkas);

        $this->assertLessThanOrEqual(1400, max($lebar, $tinggi));
        // Rasio aslinya harus terjaga: 3200x2400 adalah 4:3.
        $this->assertEqualsWithDelta(4 / 3, $lebar / $tinggi, 0.01);
    }

    public function test_unggahan_selain_gambar_ditolak(): void
    {
        Storage::fake('galeri');

        $this->actingAs(User::factory()->create())
            ->post('/admin/galeri', [
                'foto' => [UploadedFile::fake()->create('daftar.pdf', 100, 'application/pdf')],
            ])
            ->assertSessionHasErrors('foto.0');

        $this->assertDatabaseCount('galeri', 0);
    }

    public function test_panitia_dapat_mengubah_judul_dan_menyembunyikan_foto(): void
    {
        $foto = Galeri::factory()->create(['judul' => 'Judul lama']);

        $this->actingAs(User::factory()->create())
            ->put('/admin/galeri/'.$foto->id, ['judul' => 'Manasik haji bersama'])
            ->assertRedirect('/admin/galeri');

        $foto->refresh();

        $this->assertSame('Manasik haji bersama', $foto->judul);
        // Checkbox yang tidak dicentang tidak ikut terkirim browser.
        $this->assertFalse($foto->tampil);
    }

    public function test_panitia_dapat_menggeser_urutan_foto(): void
    {
        $pertama = Galeri::factory()->create(['urutan' => 1]);
        $kedua = Galeri::factory()->create(['urutan' => 2]);
        $ketiga = Galeri::factory()->create(['urutan' => 3]);

        $this->actingAs(User::factory()->create())
            ->post('/admin/galeri/'.$ketiga->id.'/geser', ['arah' => 'naik'])
            ->assertRedirect('/admin/galeri');

        $this->assertSame(
            [$pertama->id, $ketiga->id, $kedua->id],
            Galeri::query()->urut()->pluck('id')->all(),
        );
    }

    public function test_menggeser_foto_teratas_ke_atas_tidak_mengubah_apa_pun(): void
    {
        $pertama = Galeri::factory()->create(['urutan' => 1]);
        $kedua = Galeri::factory()->create(['urutan' => 2]);

        $this->actingAs(User::factory()->create())
            ->post('/admin/galeri/'.$pertama->id.'/geser', ['arah' => 'naik'])
            ->assertRedirect('/admin/galeri');

        $this->assertSame(
            [$pertama->id, $kedua->id],
            Galeri::query()->urut()->pluck('id')->all(),
        );
    }

    public function test_menghapus_foto_ikut_menghapus_berkasnya(): void
    {
        Storage::fake('galeri');
        Storage::disk('galeri')->put('foto-lama.jpg', 'isi');

        $foto = Galeri::factory()->create(['gambar' => 'images/galeri/foto-lama.jpg']);

        $this->actingAs(User::factory()->create())
            ->delete('/admin/galeri/'.$foto->id)
            ->assertRedirect('/admin/galeri');

        $this->assertDatabaseCount('galeri', 0);
        Storage::disk('galeri')->assertMissing('foto-lama.jpg');
    }
}
