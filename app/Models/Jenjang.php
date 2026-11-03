<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Jenjang program: KB, RA Kelompok A/B, TPA, DTA.
 *
 * Tabel referensi, isinya dibuat saat migrasi. Panitia tidak menambah jenjang
 * tiap tahun, jadi tidak ada layar pengelolaannya di panel admin -- cukup
 * diubah lewat migrasi baru bila sekolah membuka program baru.
 */
class Jenjang extends Model
{
    protected $table = 'jenjang';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'kuota' => 'integer',
            'urutan' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'kode';
    }

    public function pendaftaran(): HasMany
    {
        return $this->hasMany(Pendaftaran::class, 'jenjang_id');
    }

    public function scopeUrut(Builder $query): Builder
    {
        return $query->orderBy('urutan')->orderBy('id');
    }

    /**
     * Cari berdasarkan kode, mis. "ra-a".
     *
     * Dipakai saat menyimpan formulir, karena yang dikirim peramban adalah
     * kodenya, bukan id -- supaya tautan dan filter tetap terbaca manusia.
     */
    public static function dariKode(?string $kode): ?self
    {
        return blank($kode)
            ? null
            : static::query()->where('kode', $kode)->first();
    }
}
