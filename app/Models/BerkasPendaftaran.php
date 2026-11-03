<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Satu berkas yang diunggah pendaftar: Kartu Keluarga, akta, atau pas foto.
 *
 * Berkasnya ada di disk privat (storage/app/private/ppdb), jadi hanya bisa
 * dibuka lewat panel admin yang sudah login.
 */
class BerkasPendaftaran extends Model
{
    protected $table = 'pendaftaran_berkas';

    protected $guarded = ['id'];

    public const KK = 'kk';
    public const AKTA = 'akta';
    public const FOTO = 'foto';

    /**
     * Jenis berkas yang diminta, beserta sebutan dan kewajibannya.
     *
     * Dipakai formulir pendaftaran, halaman detail di panel admin, dan bukti
     * pendaftaran -- ketiganya membaca daftar yang sama supaya tidak ada yang
     * ketinggalan saat syaratnya berubah.
     *
     * @var array<string, array{label: string, wajib: bool}>
     */
    public const JENIS = [
        self::KK => ['label' => 'Kartu Keluarga', 'wajib' => true],
        self::AKTA => ['label' => 'Akta Kelahiran', 'wajib' => true],
        self::FOTO => ['label' => 'Pas Foto Anak', 'wajib' => false],
    ];

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    public function getLabelAttribute(): string
    {
        return self::JENIS[$this->jenis]['label'] ?? ucfirst($this->jenis);
    }

    public function ada(): bool
    {
        return filled($this->path) && Storage::disk('local')->exists($this->path);
    }

    public function hapusBerkas(): void
    {
        if (filled($this->path)) {
            Storage::disk('local')->delete($this->path);
        }
    }
}
