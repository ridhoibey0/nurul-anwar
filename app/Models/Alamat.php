<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Alamat tempat tinggal pendaftar.
 */
class Alamat extends Model
{
    protected $table = 'alamat';

    protected $guarded = ['id'];

    public function pendaftaran(): HasMany
    {
        return $this->hasMany(Pendaftaran::class, 'alamat_id');
    }

    /**
     * Alamat satu baris penuh, mis. untuk kop bukti pendaftaran dan ekspor
     * CSV: "Jl. Winaya Utama No. 20, Kahuripan, Tawang, Kota Tasikmalaya 46115".
     */
    public function getLengkapAttribute(): string
    {
        $bagian = array_filter([
            $this->jalan,
            $this->kelurahan,
            $this->kecamatan,
            trim($this->kota.' '.$this->kode_pos),
        ], static fn (?string $isi): bool => filled($isi));

        return implode(', ', $bagian);
    }
}
