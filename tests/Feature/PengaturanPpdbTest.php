<?php

namespace Tests\Feature;

use App\Models\Pendaftaran;
use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengaturanPpdbTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_tidak_bisa_membuka_halaman_pengaturan(): void
    {
        $this->get('/admin/pengaturan')->assertRedirect('/admin/login');
        $this->patch('/admin/pengaturan', [])->assertRedirect('/admin/login');
        $this->post('/admin/pengaturan/saklar')->assertRedirect('/admin/login');
    }

    public function test_panitia_dapat_mengganti_tahun_ajaran_dan_jadwal(): void
    {
        $this->actingAs(User::factory()->create())
            ->patch('/admin/pengaturan', [
                'buka' => '1',
                'tahun_ajaran' => '2027/2028',
                'dibuka_pada' => '2027-01-04',
                'ditutup_pada' => '2027-06-30',
            ])
            ->assertRedirect('/admin/pengaturan')
            ->assertSessionHas('sukses');

        $this->assertDatabaseHas('pengaturan', [
            'kunci' => 'ppdb_tahun_ajaran',
            'nilai' => '2027/2028',
        ]);

        // Di produksi penimpaan config terjadi otomatis di AppServiceProvider::boot()
        // setiap request. Dalam pengujian, satu instance aplikasi dipakai untuk
        // beberapa request, jadi penimpaan dipanggil manual di sini.
        Pengaturan::terapkanKeConfig();

        $this->get('/ppdb')
            ->assertOk()
            ->assertSee('2027/2028');
    }

    public function test_tahun_ajaran_harus_dua_tahun_berurutan(): void
    {
        $panitia = User::factory()->create();

        $this->actingAs($panitia)
            ->patch('/admin/pengaturan', [
                'buka' => '1',
                'tahun_ajaran' => '2027/2029',
                'dibuka_pada' => '2027-01-04',
                'ditutup_pada' => '2027-06-30',
            ])
            ->assertSessionHasErrors('tahun_ajaran');

        $this->actingAs($panitia)
            ->patch('/admin/pengaturan', [
                'buka' => '1',
                'tahun_ajaran' => 'dua ribu',
                'dibuka_pada' => '2027-01-04',
                'ditutup_pada' => '2027-06-30',
            ])
            ->assertSessionHasErrors('tahun_ajaran');

        $this->assertDatabaseMissing('pengaturan', ['kunci' => 'ppdb_tahun_ajaran']);
    }

    public function test_tanggal_tutup_tidak_boleh_mendahului_tanggal_buka(): void
    {
        $this->actingAs(User::factory()->create())
            ->patch('/admin/pengaturan', [
                'buka' => '1',
                'tahun_ajaran' => '2027/2028',
                'dibuka_pada' => '2027-06-30',
                'ditutup_pada' => '2027-01-04',
            ])
            ->assertSessionHasErrors('ditutup_pada');
    }

    public function test_pemisah_tahun_ajaran_dirapikan(): void
    {
        $this->actingAs(User::factory()->create())
            ->patch('/admin/pengaturan', [
                'buka' => '1',
                'tahun_ajaran' => '2027 - 2028',
                'dibuka_pada' => '2027-01-04',
                'ditutup_pada' => '2027-06-30',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('2027/2028', Pengaturan::ambil('ppdb_tahun_ajaran'));
    }

    public function test_saklar_menutup_dan_membuka_pendaftaran(): void
    {
        $panitia = User::factory()->create();

        $this->actingAs($panitia)
            ->from('/admin')
            ->post('/admin/pengaturan/saklar')
            ->assertRedirect('/admin');

        $this->assertSame('0', Pengaturan::ambil('ppdb_buka'));

        $this->actingAs($panitia)
            ->from('/admin')
            ->post('/admin/pengaturan/saklar');

        $this->assertSame('1', Pengaturan::ambil('ppdb_buka'));
    }

    public function test_formulir_tertutup_setelah_pendaftaran_dinonaktifkan(): void
    {
        Pengaturan::simpan(['ppdb_buka' => false]);
        Pengaturan::terapkanKeConfig();

        $this->get('/ppdb/daftar')->assertRedirect(route('ppdb.info'));

        $this->post('/ppdb/daftar', ['nama_lengkap' => 'Aisyah'])->assertForbidden();

        $this->assertDatabaseCount('pendaftaran_siswa', 0);
    }

    public function test_nomor_pendaftaran_memakai_tahun_ajaran_dari_pengaturan(): void
    {
        Pengaturan::simpan(['ppdb_tahun_ajaran' => '2030/2031']);
        Pengaturan::terapkanKeConfig();

        $this->assertSame('PPDB-3031-0001', Pendaftaran::buatNomor(config('ppdb.tahun_ajaran')));
    }

    public function test_config_memakai_nilai_bawaan_bila_tabel_pengaturan_kosong(): void
    {
        config(['ppdb.tahun_ajaran' => '2026/2027']);

        Pengaturan::terapkanKeConfig();

        $this->assertSame('2026/2027', config('ppdb.tahun_ajaran'));
    }
}
