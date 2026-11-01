<?php

namespace Tests\Feature;

use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengaturanSekolahTest extends TestCase
{
    use RefreshDatabase;

    /**
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
            'instagram_url' => 'https://instagram.com/ra.nurulanwar',
            'facebook_url' => '',
            'youtube_url' => '',
            'peta_lat' => '-7.3562979',
            'peta_lng' => '108.2236263',
            'peta_url' => 'https://maps.app.goo.gl/VT6PuUPbryvVJp3c7',
        ], $ubah);
    }

    private function simpan(array $ubah = [])
    {
        return $this->actingAs(User::factory()->create())
            ->patch('/admin/pengaturan/sekolah', $this->isian($ubah));
    }

    public function test_tamu_tidak_bisa_membuka_atau_mengubah_identitas_sekolah(): void
    {
        $this->get('/admin/pengaturan/sekolah')->assertRedirect('/admin/login');
        $this->patch('/admin/pengaturan/sekolah', $this->isian())->assertRedirect('/admin/login');

        $this->assertDatabaseCount('pengaturan', 0);
    }

    public function test_nilai_tersimpan_menimpa_config(): void
    {
        $this->simpan(['nama' => 'RA Nurul Anwar Cikunten'])
            ->assertRedirect('/admin/pengaturan/sekolah')
            ->assertSessionHas('sukses');

        // Satu instance aplikasi dipakai ulang antar request di dalam tes,
        // jadi penimpaan config dari AppServiceProvider tidak berjalan lagi.
        Pengaturan::terapkanKeConfig();

        $this->assertSame('RA Nurul Anwar Cikunten', config('sekolah.nama'));
        $this->assertSame('6281234567890', config('sekolah.whatsapp'));
    }

    public function test_nomor_whatsapp_dirapikan_apa_pun_bentuk_tulisannya(): void
    {
        foreach ([
            '081234567890' => '6281234567890',
            '+62 812-3456-7890' => '6281234567890',
            '81234567890' => '6281234567890',
            '6281234567890' => '6281234567890',
        ] as $ditulis => $harapan) {
            Pengaturan::query()->delete();

            $this->simpan(['whatsapp' => $ditulis])->assertSessionHasNoErrors();

            $this->assertSame(
                $harapan,
                Pengaturan::ambil('sekolah_whatsapp'),
                'Nomor "'.$ditulis.'" tidak dirapikan dengan benar.',
            );
        }
    }

    public function test_nomor_whatsapp_yang_tidak_masuk_akal_ditolak(): void
    {
        $this->simpan(['whatsapp' => '12345'])->assertSessionHasErrors('whatsapp');

        $this->assertDatabaseCount('pengaturan', 0);
    }

    public function test_nama_instagram_selalu_diberi_tanda_at(): void
    {
        $this->simpan(['instagram' => 'ra.nurulanwar'])->assertSessionHasNoErrors();
        $this->assertSame('@ra.nurulanwar', Pengaturan::ambil('sekolah_instagram'));

        Pengaturan::query()->delete();

        // Yang sudah ditulis dengan @ tidak jadi dobel.
        $this->simpan(['instagram' => '@ra.nurulanwar'])->assertSessionHasNoErrors();
        $this->assertSame('@ra.nurulanwar', Pengaturan::ambil('sekolah_instagram'));
    }

    public function test_isian_yang_dikosongkan_tidak_jatuh_balik_ke_env(): void
    {
        $this->simpan(['akreditasi' => ''])->assertSessionHasNoErrors();

        // Tersimpan sebagai string kosong, bukan null. Null berarti "belum
        // pernah diisi" dan akan memunculkan lagi nilai bawaan dari .env.
        $this->assertSame('', Pengaturan::ambil('sekolah_akreditasi'));

        Pengaturan::terapkanKeConfig();

        $this->assertSame('', config('sekolah.akreditasi'));
    }

    public function test_lencana_akreditasi_hilang_dari_beranda_saat_dikosongkan(): void
    {
        $this->get('/')->assertOk()->assertSee('Akreditasi');

        $this->simpan(['akreditasi' => '']);
        Pengaturan::terapkanKeConfig();

        $this->get('/')->assertOk()->assertDontSee('Akreditasi');
    }

    public function test_ikon_media_sosial_tanpa_tautan_tidak_ditampilkan(): void
    {
        $this->simpan([
            'instagram_url' => 'https://instagram.com/ra.nurulanwar',
            'facebook_url' => '',
            'youtube_url' => '',
        ]);
        Pengaturan::terapkanKeConfig();

        $this->get('/')
            ->assertOk()
            ->assertSee('https://instagram.com/ra.nurulanwar', false)
            ->assertSee('aria-label="Instagram"', false)
            ->assertDontSee('aria-label="Facebook"', false)
            ->assertDontSee('aria-label="YouTube"', false);
    }

    public function test_tautan_media_sosial_harus_lengkap(): void
    {
        $this->simpan(['facebook_url' => 'facebook.com/sekolah'])
            ->assertSessionHasErrors('facebook_url');
    }

    public function test_alamat_dari_panel_admin_ikut_ke_data_terstruktur(): void
    {
        $this->simpan([
            'alamat' => 'Jl. Contoh No. 1',
            'kota' => 'Kota Bandung',
        ]);
        Pengaturan::terapkanKeConfig();

        $isi = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('"streetAddress":"Jl. Contoh No. 1"', $isi);
        $this->assertStringContainsString('"addressLocality":"Kota Bandung"', $isi);
    }

    public function test_tahun_berdiri_tidak_boleh_di_masa_depan(): void
    {
        $this->simpan(['tahun_berdiri' => (string) (date('Y') + 1)])
            ->assertSessionHasErrors('tahun_berdiri');
    }

    public function test_pengaturan_ppdb_tidak_ikut_terhapus_saat_identitas_disimpan(): void
    {
        Pengaturan::simpan(['ppdb_tahun_ajaran' => '2030/2031']);

        $this->simpan();

        $this->assertSame('2030/2031', Pengaturan::ambil('ppdb_tahun_ajaran'));
    }
}
