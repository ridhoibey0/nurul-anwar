<?php

namespace Database\Factories;

use App\Models\Alamat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Alamat>
 */
class AlamatFactory extends Factory
{
    protected $model = Alamat::class;

    public function definition(): array
    {
        return [
            'jalan' => fake()->streetAddress(),
            'kelurahan' => 'Kahuripan',
            'kecamatan' => 'Tawang',
            'kota' => 'Kota Tasikmalaya',
            'kode_pos' => fake()->numerify('461##'),
        ];
    }
}
