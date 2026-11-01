<?php

namespace Database\Factories;

use App\Models\Pendaftaran;
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
        $jenjang = fake()->randomElement(array_keys(config('ppdb.jenjang')));
        $kelamin = fake()->randomElement(['L', 'P']);

        return [
            'nomor_pendaftaran' => config('ppdb.prefix_nomor').'-'
                .substr($tahunAjaran, 2, 2).substr($tahunAjaran, -2).'-'
                .str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'tahun_ajaran' => $tahunAjaran,
            'jenjang' => $jenjang,

            'nama_lengkap' => $kelamin === 'L' ? fake()->name('male') : fake()->name('female'),
            'nama_panggilan' => fake()->firstName(),
            'jenis_kelamin' => $kelamin,
            'tempat_lahir' => fake()->city(),
            'tanggal_lahir' => fake()->dateTimeBetween('-7 years', '-3 years'),
            'nik' => fake()->numerify('################'),
            'anak_ke' => fake()->numberBetween(1, 3),
            'jumlah_saudara' => fake()->numberBetween(0, 3),
            'agama' => 'Islam',

            'alamat' => fake()->streetAddress(),
            'kelurahan' => 'Kahuripan',
            'kecamatan' => 'Tawang',
            'kota' => 'Kota Tasikmalaya',
            'kode_pos' => fake()->numerify('461##'),

            'asal_sekolah' => fake()->optional()->company(),
            'riwayat_kesehatan' => fake()->optional()->sentence(),

            'nama_ayah' => fake()->name('male'),
            'nik_ayah' => fake()->numerify('################'),
            'pendidikan_ayah' => fake()->randomElement(['SMA/SMK', 'D3', 'S1']),
            'pekerjaan_ayah' => fake()->jobTitle(),
            'penghasilan_ayah' => fake()->randomElement(['< 2 juta', '2 - 5 juta', '5 - 10 juta']),
            'telepon_ayah' => fake()->numerify('08##########'),

            'nama_ibu' => fake()->name('female'),
            'nik_ibu' => fake()->numerify('################'),
            'pendidikan_ibu' => fake()->randomElement(['SMA/SMK', 'D3', 'S1']),
            'pekerjaan_ibu' => fake()->randomElement(['Ibu Rumah Tangga', 'Guru', 'Wiraswasta']),
            'penghasilan_ibu' => fake()->randomElement(['Tidak berpenghasilan', '< 2 juta', '2 - 5 juta']),
            'telepon_ibu' => fake()->numerify('08##########'),

            'whatsapp' => fake()->numerify('08##########'),
            'email' => fake()->optional()->safeEmail(),

            'catatan_orang_tua' => fake()->optional()->sentence(),

            'status' => fake()->randomElement(array_keys(config('ppdb.status'))),
            'created_at' => fake()->dateTimeBetween('-2 months'),
        ];
    }
}
