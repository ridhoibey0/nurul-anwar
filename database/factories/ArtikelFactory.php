<?php

namespace Database\Factories;

use App\Models\Artikel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Artikel>
 */
class ArtikelFactory extends Factory
{
    protected $model = Artikel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $judul = rtrim($this->faker->sentence(6), '.');

        return [
            'judul' => $judul,
            'slug' => Str::slug($judul).'-'.Str::random(5),
            'kategori' => $this->faker->randomElement(['Kegiatan Sekolah', 'Tips Parenting', 'Tumbuh Kembang']),
            'ringkasan' => $this->faker->paragraph(2),
            'isi' => collect($this->faker->paragraphs(4))->implode("\n\n"),
            'gambar' => null,
            'status' => 'terbit',
            'terbit_pada' => now()->subDays($this->faker->numberBetween(1, 120)),
            'penulis_id' => null,
        ];
    }

    public function draf(): static
    {
        return $this->state(fn (): array => [
            'status' => 'draf',
            'terbit_pada' => null,
        ]);
    }

    public function terjadwal(): static
    {
        return $this->state(fn (): array => [
            'status' => 'terbit',
            'terbit_pada' => now()->addWeek(),
        ]);
    }
}
