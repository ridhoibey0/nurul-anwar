<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Status seleksi pendaftaran: baru, diverifikasi, diterima, ditolak.
 *
 * Tabel referensi, isinya dibuat saat migrasi. Kolom "warna" dipakai lencana
 * status di panel admin dan halaman cek status.
 */
class StatusPendaftaran extends Model
{
    protected $table = 'status_pendaftaran';

    protected $guarded = ['id'];

    /** Status yang dipasang ke pendaftaran yang baru masuk. */
    public const AWAL = 'baru';

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'kode';
    }

    public function pendaftaran(): HasMany
    {
        return $this->hasMany(Pendaftaran::class, 'status_id');
    }

    public function scopeUrut(Builder $query): Builder
    {
        return $query->orderBy('urutan')->orderBy('id');
    }

    public static function dariKode(?string $kode): ?self
    {
        return blank($kode)
            ? null
            : static::query()->where('kode', $kode)->first();
    }

    /**
     * Status awal untuk pendaftaran baru.
     *
     * Dibuat sebagai metode tersendiri supaya kegagalannya jelas: tanpa baris
     * ini tabel referensinya belum ter-migrasi, dan pendaftaran yang masuk
     * tidak punya status untuk dipasang.
     */
    public static function awal(): self
    {
        $status = static::dariKode(self::AWAL);

        if (! $status) {
            throw new \RuntimeException(
                'Status "'.self::AWAL.'" tidak ada di tabel status_pendaftaran. '
                .'Jalankan php artisan migrate.'
            );
        }

        return $status;
    }
}
