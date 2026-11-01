<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BerandaTest extends TestCase
{
    use RefreshDatabase;

    public function test_bagian_testimoni_tersembunyi_selama_belum_ada_kutipan(): void
    {
        config(['konten.testimoni' => []]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Kata orang tua');
    }

    public function test_bagian_testimoni_muncul_setelah_kutipan_diisi(): void
    {
        config(['konten.testimoni' => [[
            'nama' => 'Ibu Fulanah',
            'peran' => 'Orang tua siswa RA B',
            'kutipan' => 'Anak saya senang berangkat sekolah.',
            'avatar' => null,
        ]]]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Kata orang tua')
            ->assertSee('Anak saya senang berangkat sekolah.');
    }

    public function test_statistik_mengikuti_jumlah_guru_dan_jenjang_yang_terdaftar(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee((string) count(config('konten.guru')))
            ->assertSee('Guru &amp; Kepala RA', false)
            ->assertSee('Jenjang Program');
    }
}
