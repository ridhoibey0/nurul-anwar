<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AkunAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_sandi_baru_tersimpan_dalam_keadaan_ter_hash(): void
    {
        $akun = User::factory()->create(['email' => 'lama@sekolah.id']);

        $this->artisan('admin:akun', [
            '--email' => 'baru@sekolah.id',
            '--nama' => 'Panitia PPDB',
            '--sandi' => 'sandi-yang-baru',
        ])->assertSuccessful();

        $akun->refresh();

        $this->assertSame('baru@sekolah.id', $akun->email);
        $this->assertSame('Panitia PPDB', $akun->name);

        // Inti pengujian ini: sandi tidak boleh tersimpan mentah.
        $this->assertNotSame('sandi-yang-baru', $akun->password);
        $this->assertTrue(Hash::check('sandi-yang-baru', $akun->password));
    }

    public function test_akun_dengan_sandi_baru_bisa_dipakai_login(): void
    {
        User::factory()->create(['email' => 'panitia@sekolah.id']);

        $this->artisan('admin:akun', [
            '--email' => 'panitia@sekolah.id',
            '--nama' => 'Panitia PPDB',
            '--sandi' => 'sandi-yang-baru',
        ])->assertSuccessful();

        $this->post('/admin/login', [
            'email' => 'panitia@sekolah.id',
            'password' => 'sandi-yang-baru',
        ])->assertRedirect('/admin');

        $this->assertAuthenticated();
    }

    public function test_sandi_tidak_berubah_bila_tidak_diisi(): void
    {
        $akun = User::factory()->create(['password' => 'sandi-asli']);
        $sebelum = $akun->password;

        $this->artisan('admin:akun', [
            '--email' => 'ganti@sekolah.id',
            '--nama' => 'Nama Baru',
        ])
            ->expectsQuestion('Sandi baru (kosongkan bila tidak ingin diganti)', '')
            ->assertSuccessful();

        $akun->refresh();

        $this->assertSame('ganti@sekolah.id', $akun->email);
        $this->assertSame($sebelum, $akun->password);
    }

    public function test_sandi_terlalu_pendek_ditolak(): void
    {
        $akun = User::factory()->create(['email' => 'panitia@sekolah.id']);

        $this->artisan('admin:akun', [
            '--email' => 'panitia@sekolah.id',
            '--nama' => 'Panitia',
            '--sandi' => 'pendek',
        ])->assertFailed();

        $this->assertSame('panitia@sekolah.id', $akun->refresh()->email);
    }

    public function test_email_yang_sudah_dipakai_akun_lain_ditolak(): void
    {
        User::factory()->create(['email' => 'satu@sekolah.id']);
        $dua = User::factory()->create(['email' => 'dua@sekolah.id']);

        $this->artisan('admin:akun', [
            '--akun' => 'dua@sekolah.id',
            '--email' => 'satu@sekolah.id',
            '--nama' => 'Panitia',
        ])
            ->expectsQuestion('Sandi baru (kosongkan bila tidak ingin diganti)', '')
            ->assertFailed();

        $this->assertSame('dua@sekolah.id', $dua->refresh()->email);
    }

    public function test_email_boleh_tetap_sama_dengan_milik_akun_itu_sendiri(): void
    {
        User::factory()->create(['email' => 'panitia@sekolah.id']);

        $this->artisan('admin:akun', [
            '--email' => 'panitia@sekolah.id',
            '--nama' => 'Nama Diperbarui',
            '--sandi' => 'sandi-yang-baru',
        ])->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'email' => 'panitia@sekolah.id',
            'name' => 'Nama Diperbarui',
        ]);
    }

    public function test_akun_panitia_kedua_bisa_dibuat(): void
    {
        User::factory()->create(['email' => 'satu@sekolah.id']);

        $this->artisan('admin:akun', [
            '--baru' => true,
            '--email' => 'dua@sekolah.id',
            '--nama' => 'Panitia Kedua',
            '--sandi' => 'sandi-yang-baru',
        ])->assertSuccessful();

        $this->assertDatabaseCount('users', 2);
        $this->assertTrue(
            Hash::check('sandi-yang-baru', User::query()->where('email', 'dua@sekolah.id')->value('password')),
        );
    }

    public function test_akun_baru_tanpa_sandi_ditolak(): void
    {
        $this->artisan('admin:akun', [
            '--baru' => true,
            '--email' => 'dua@sekolah.id',
            '--nama' => 'Panitia Kedua',
        ])
            ->expectsQuestion('Sandi', '')
            ->assertFailed();

        $this->assertDatabaseCount('users', 0);
    }
}
