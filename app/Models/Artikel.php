<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Artikel extends Model
{
    use HasFactory;

    protected $table = 'artikel';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'terbit_pada' => 'datetime',
        ];
    }

    /** Rute publik memakai slug, bukan id. */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function penulis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penulis_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scope
    |--------------------------------------------------------------------------
    */

    /** Hanya artikel yang sudah terbit dan tanggal terbitnya tidak di masa depan. */
    public function scopeTerbit(Builder $query): Builder
    {
        return $query->where('status', 'terbit')
            ->whereNotNull('terbit_pada')
            ->where('terbit_pada', '<=', now());
    }

    public function scopeCari(Builder $query, ?string $kata): Builder
    {
        if (blank($kata)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($kata): void {
            $q->where('judul', 'like', "%{$kata}%")
                ->orWhere('kategori', 'like', "%{$kata}%")
                ->orWhere('ringkasan', 'like', "%{$kata}%");
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return blank($status) ? $query : $query->where('status', $status);
    }

    public function scopeTerbaru(Builder $query): Builder
    {
        return $query->orderByDesc('terbit_pada')->orderByDesc('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Atribut turunan
    |--------------------------------------------------------------------------
    */

    public function getSudahTerbitAttribute(): bool
    {
        return $this->status === 'terbit'
            && $this->terbit_pada !== null
            && $this->terbit_pada->lessThanOrEqualTo(now());
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status !== 'terbit') {
            return 'Draf';
        }

        return $this->sudah_terbit ? 'Terbit' : 'Terjadwal';
    }

    public function getGambarUrlAttribute(): ?string
    {
        return $this->gambar ? asset($this->gambar) : null;
    }

    /**
     * Isi artikel ditulis dengan Markdown sederhana. HTML mentah dibuang
     * supaya panitia tidak bisa menyuntikkan skrip, sengaja maupun tidak.
     */
    public function getIsiHtmlAttribute(): string
    {
        return Str::markdown($this->isi ?? '', [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public function getWaktuBacaAttribute(): int
    {
        $kata = str_word_count(strip_tags((string) $this->isi));

        return max(1, (int) ceil($kata / 200));
    }

    /*
    |--------------------------------------------------------------------------
    | Slug
    |--------------------------------------------------------------------------
    */

    /** Slug unik dari judul; $kecualiId dipakai saat menyunting artikel sendiri. */
    public static function buatSlug(string $judul, ?int $kecualiId = null): string
    {
        $dasar = Str::slug(Str::limit($judul, 150, '')) ?: 'artikel';
        $slug = $dasar;
        $urutan = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($kecualiId, fn (Builder $q) => $q->whereKeyNot($kecualiId))
            ->exists()
        ) {
            $slug = $dasar.'-'.$urutan;
            $urutan++;
        }

        return $slug;
    }
}
