<?php

namespace Database\Factories;

use App\Models\Galeri;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Galeri>
 */
class GaleriFactory extends Factory
{
    protected $model = Galeri::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'judul' => 'Kegiatan '.fake()->words(2, true),
            'gambar' => 'images/galeri/'.Str::random(10).'.jpg',
            'urutan' => fake()->numberBetween(1, 50),
            'tampil' => true,
        ];
    }

    public function disembunyikan(): static
    {
        return $this->state(['tampil' => false]);
    }

    public function tanpaJudul(): static
    {
        return $this->state(['judul' => null]);
    }
}
