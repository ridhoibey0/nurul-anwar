<?php

namespace Tests\Unit;

use Illuminate\Support\Carbon;
use Tests\TestCase;

class HelperTest extends TestCase
{
    public function test_tanggal_id_memakai_nama_bulan_indonesia(): void
    {
        $this->assertSame('5 Januari 2026', tanggal_id(Carbon::parse('2026-01-05')));
        $this->assertSame('17 Agustus 2025', tanggal_id(Carbon::parse('2025-08-17')));
        $this->assertSame('-', tanggal_id(null));
    }

    public function test_wa_link_membuang_karakter_selain_angka(): void
    {
        config(['sekolah.whatsapp' => '+62 812-3456-7890']);

        $this->assertSame('https://wa.me/6281234567890', wa_link());
        $this->assertStringContainsString('?text=Halo%20Bu', wa_link('Halo Bu'));
    }

    public function test_peta_embed_memakai_koordinat_bila_tersedia(): void
    {
        config([
            'sekolah.peta_lat' => '-7.3562979',
            'sekolah.peta_lng' => '108.2236263',
            'sekolah.peta_query' => 'KB RA TPA DTA Nurul Anwar',
        ]);

        $this->assertStringContainsString('q=-7.3562979%2C108.2236263', peta_embed());
    }

    public function test_peta_embed_jatuh_ke_nama_bila_koordinat_kosong(): void
    {
        config([
            'sekolah.peta_lat' => '',
            'sekolah.peta_lng' => '',
            'sekolah.peta_query' => 'KB RA TPA DTA Nurul Anwar',
        ]);

        $this->assertStringContainsString('q=KB%20RA%20TPA%20DTA%20Nurul%20Anwar', peta_embed());
    }
}
