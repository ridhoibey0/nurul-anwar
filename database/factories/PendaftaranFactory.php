<?php

namespace Database\Factories;

use App\Models\Alamat;
use App\Models\BerkasPendaftaran;
use App\Models\Jenjang;
use App\Models\OrangTua;
use App\Models\Pendaftaran;
use App\Models\StatusPendaftaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pendaftaran>
 */
class PendaftaranFactory extends Factory
{
    protected $model = Pendaftaran::class;

    public function definition(): array
    {
        $tahunAjaran = config('ppdb.tahun_ajaran');
        $kelamin = fake()->randomElement(['L', 'P']);

        return [
            'nomor_pendaftaran' => config('ppdb.prefix_nomor').'-'
                .substr($tahunAjaran, 2, 2).substr($tahunAjaran, -2).'-'
                .str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'tahun_ajaran' => $tahunAjaran,

            // inRandomOrder()->value('id') dipakai supaya factory tidak ikut
            // membuat baris jenjang baru: isinya tabel referensi yang sudah
            // terisi sejak migrasi.
            'jenjang_id' => Jenjang::query()->inRandomOrder()->value('id'),
            'status_id' => StatusPendaftaran::query()->inRandomOrder()->value('id'),

            'nama_lengkap' => $kelamin === 'L' ? fake()->name('male') : fake()->name('female'),
            'nama_panggilan' => fake()->firstName(),
            'jenis_kelamin' => $kelamin,
            'tempat_lahir' => fake()->city(),
            'tanggal_lahir' => fake()->dateTimeBetween('-7 years', '-3 years'),
            'nik' => fake()->numerify('################'),
            'anak_ke' => fake()->numberBetween(1, 3),
            'jumlah_saudara' => fake()->numberBetween(0, 3),
            'agama' => 'Islam',

            'alamat_id' => Alamat::factory(),

            'asal_sekolah' => fake()->optional()->company(),
            'riwayat_kesehatan' => fake()->optional()->sentence(),

            'whatsapp' => fake()->numerify('08##########'),
            'email' => fake()->optional()->safeEmail(),

            'catatan_orang_tua' => fake()->optional()->sentence(),

            'created_at' => fake()->dateTimeBetween('-2 months'),
        ];
    }

    /** Jenjang tertentu, mis. ->jenjang('ra-a'). */
    public function jenjang(string $kode): static
    {
        return $this->state(fn (): array => [
            'jenjang_id' => Jenjang::query()->where('kode', $kode)->value('id'),
        ]);
    }

    /** Status tertentu, mis. ->status('diterima'). */
    public function status(string $kode): static
    {
        return $this->state(fn (): array => [
            'status_id' => StatusPendaftaran::query()->where('kode', $kode)->value('id'),
        ]);
    }

    /**
     * Lengkapi dengan data ayah, ibu, dan berkas wajib.
     *
     * Dipakai pengujian yang memeriksa tampilan detail pendaftar atau isi
     * bukti pendaftaran, bukan yang cuma butuh satu barisnya ada.
     */
    public function lengkap(): static
    {
        return $this->afterCreating(function (Pendaftaran $pendaftaran): void {
            $pendaftaran->orangTua()->createMany([
                [
                    'peran' => OrangTua::AYAH,
                    'nama' => fake()->name('male'),
                    'nik' => fake()->numerify('################'),
                    'pendidikan' => fake()->randomElement(['SMA/SMK', 'D3', 'S1']),
                    'pekerjaan' => fake()->jobTitle(),
                    'penghasilan' => fake()->randomElement(['< 2 juta', '2 - 5 juta', '5 - 10 juta']),
                    'telepon' => fake()->numerify('08##########'),
                ],
                [
                    'peran' => OrangTua::IBU,
                    'nama' => fake()->name('female'),
                    'nik' => fake()->numerify('################'),
                    'pendidikan' => fake()->randomElement(['SMA/SMK', 'D3', 'S1']),
                    'pekerjaan' => fake()->randomElement(['Ibu Rumah Tangga', 'Guru', 'Wiraswasta']),
                    'penghasilan' => fake()->randomElement(['Tidak berpenghasilan', '< 2 juta']),
                    'telepon' => fake()->numerify('08##########'),
                ],
            ]);

            $pendaftaran->berkas()->createMany([
                ['jenis' => BerkasPendaftaran::KK, 'path' => 'ppdb/contoh/kk-'.$pendaftaran->id.'.pdf'],
                ['jenis' => BerkasPendaftaran::AKTA, 'path' => 'ppdb/contoh/akta-'.$pendaftaran->id.'.jpg'],
            ]);
        });
    }
}
