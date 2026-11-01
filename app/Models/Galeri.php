<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Galeri extends Model
{
    use HasFactory;

    protected $table = 'galeri';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tampil' => 'boolean',
        ];
    }

    public function scopeTampil(Builder $query): Builder
    {
        return $query->where('tampil', true);
    }

    /**
     * Urutan yang dipilih panitia dulu, sisanya yang terbaru di atas. Tanpa
     * tie-break id, dua foto dengan urutan sama bisa bertukar posisi tiap
     * halaman dimuat.
     */
    public function scopeUrut(Builder $query): Builder
    {
        return $query->orderBy('urutan')->orderByDesc('id');
    }

    public function getGambarUrlAttribute(): string
    {
        return asset($this->gambar);
    }

    /** Dipakai sebagai alt text; foto tanpa judul tetap harus punya deskripsi. */
    public function getAltAttribute(): string
    {
        return $this->judul ?: 'Kegiatan di '.config('sekolah.nama');
    }
}
