<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Data ayah, ibu, atau wali dari satu pendaftaran.
 *
 * Ketiganya satu bentuk, jadi dibedakan lewat kolom "peran", bukan lewat
 * kolom bernomor seperti nama_ayah / nama_ibu.
 */
class OrangTua extends Model
{
    protected $table = 'pendaftaran_orang_tua';

    protected $guarded = ['id'];

    public const AYAH = 'ayah';
    public const IBU = 'ibu';
    public const WALI = 'wali';

    /** Peran yang dikenal, beserta sebutannya di layar. */
    public const PERAN = [
        self::AYAH => 'Ayah',
        self::IBU => 'Ibu',
        self::WALI => 'Wali',
    ];

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    public function scopePeran(Builder $query, string $peran): Builder
    {
        return $query->where('peran', $peran);
    }

    /** Urutan tampil yang wajar: ayah, ibu, lalu wali. */
    public function scopeUrut(Builder $query): Builder
    {
        return $query->orderByRaw(
            "CASE peran WHEN ? THEN 1 WHEN ? THEN 2 ELSE 3 END",
            [self::AYAH, self::IBU],
        );
    }

    public function getPeranLabelAttribute(): string
    {
        return self::PERAN[$this->peran] ?? ucfirst($this->peran);
    }
}
