<?php

namespace Tests\Feature;

use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PendaftaranTest extends TestCase
{
    use RefreshDatabase;

    /**
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
            'nama_ayah' => 'Hidayat Rahman',
            'nama_ibu' => 'Siti Aminah',
            'whatsapp' => '081234567890',
            'berkas_kk' => UploadedFile::fake()->create('kk.pdf', 100, 'application/pdf'),
            'berkas_akta' => UploadedFile::fake()->image('akta.jpg'),
            'persetujuan' => '1',
        ], $ubah);
    }

    public function test_halaman_depan_dapat_diakses(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(config('sekolah.nama'));
    }

    public function test_formulir_pendaftaran_tersimpan_dengan_nomor_berurutan(): void
    {
        Storage::fake('local');

        $this->post(route('ppdb.store'), $this->dataFormulir())
            ->assertRedirect(route('ppdb.selesai'));

        $this->post(route('ppdb.store'), $this->dataFormulir([
            'nama_lengkap' => 'Muhammad Fatih',
            'jenis_kelamin' => 'L',
        ]))->assertRedirect(route('ppdb.selesai'));

        $nomor = Pendaftaran::orderBy('id')->pluck('nomor_pendaftaran')->all();

        $this->assertCount(2, $nomor);
        $this->assertStringEndsWith('-0001', $nomor[0]);
        $this->assertStringEndsWith('-0002', $nomor[1]);
    }

    public function test_berkas_disimpan_di_disk_privat(): void
    {
        Storage::fake('local');

        $this->post(route('ppdb.store'), $this->dataFormulir());

        $pendaftaran = Pendaftaran::firstOrFail();

        Storage::disk('local')->assertExists($pendaftaran->berkas_kk);
        Storage::disk('local')->assertExists($pendaftaran->berkas_akta);
    }

    public function test_formulir_menolak_data_tidak_lengkap(): void
    {
        $this->post(route('ppdb.store'), $this->dataFormulir([
            'nama_lengkap' => '',
            'whatsapp' => 'bukan-nomor',
            'persetujuan' => null,
        ]))->assertSessionHasErrors(['nama_lengkap', 'whatsapp', 'persetujuan']);

        $this->assertSame(0, Pendaftaran::count());
    }

    public function test_cek_status_hanya_cocok_dengan_tanggal_lahir_yang_benar(): void
    {
        $pendaftaran = Pendaftaran::factory()->create([
            'tanggal_lahir' => '2021-04-17',
            'status' => 'diterima',
        ]);

        $this->post(route('ppdb.cek.proses'), [
            'nomor_pendaftaran' => $pendaftaran->nomor_pendaftaran,
            'tanggal_lahir' => '2021-04-17',
        ])->assertOk()->assertSee($pendaftaran->nama_lengkap);

        $this->post(route('ppdb.cek.proses'), [
            'nomor_pendaftaran' => $pendaftaran->nomor_pendaftaran,
            'tanggal_lahir' => '2020-01-01',
        ])->assertOk()->assertSee('Data tidak ditemukan');
    }

    public function test_panel_admin_tertutup_untuk_tamu(): void
    {
        $this->get(route('admin.pendaftaran.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_panitia_dapat_memperbarui_status(): void
    {
        $panitia = User::factory()->create();
        $pendaftaran = Pendaftaran::factory()->create(['status' => 'baru']);

        $this->actingAs($panitia)
            ->patch(route('admin.pendaftaran.status', $pendaftaran), [
                'status' => 'diterima',
                'catatan_admin' => 'Berkas lengkap.',
            ])->assertRedirect();

        $pendaftaran->refresh();

        $this->assertSame('diterima', $pendaftaran->status);
        $this->assertSame($panitia->id, $pendaftaran->diverifikasi_oleh);
    }

    public function test_berkas_pendaftar_tidak_bisa_diunduh_tanpa_login(): void
    {
        $pendaftaran = Pendaftaran::factory()->create(['berkas_kk' => 'ppdb/2026-2027/contoh.pdf']);

        $this->get(route('admin.pendaftaran.berkas', [$pendaftaran, 'kk']))
            ->assertRedirect(route('admin.login'));
    }
}
