<?php

namespace App\Support;

/**
 * Logo dan gambar bagikan sekolah.
 *
 * Keduanya diatur panitia dari Panel Admin > Identitas Sekolah dan tersimpan di
 * tabel "pengaturan". Ada tiga keadaan, dan ketiganya dibedakan lewat nilai
 * yang tersimpan -- mengikuti aturan yang sudah dipakai isian teks di tabel
 * yang sama (lihat App\Models\Pengaturan):
 *
 *   null  => belum pernah diatur, pakai berkas bawaan yang ikut dalam
 *            repositori. Baris bernilai null memang dilewati terapkanKeConfig().
 *   ''    => sengaja dikosongkan, tidak ada gambar sama sekali.
 *   jalur => berkas unggahan, mis. "images/merek/logo-20261102-a1b2c3d4.png".
 *
 * Jalurnya relatif terhadap public/, jadi bisa langsung diberikan ke asset().
 */
final class Merek
{
    /** Folder unggahan di dalam public/, sekaligus nama disk-nya. */
    public const DISK = 'merek';

    /** Memakai berkas bawaan yang ikut dalam repositori. */
    public const BAWAAN = 'bawaan';

    /** Memakai berkas yang diunggah panitia. */
    public const UNGGAHAN = 'unggahan';

    /** Sengaja tanpa gambar. */
    public const KOSONG = 'kosong';

    /**
     * Jenis gambar => berkas bawaannya. Ini satu-satunya tempat nilai bawaan
     * ditulis; config/sekolah.php sengaja dibiarkan null supaya tidak ada dua
     * tempat yang harus disamakan.
     *
     * @var array<string, string>
     */
    public const BERKAS_BAWAAN = [
        'logo' => 'images/kiddies/logo-mark.svg',
        'gambar_bagikan' => 'images/og.jpg',
    ];

    /** Keadaan yang boleh dipilih panitia dari formulir. */
    public const PILIHAN = [self::BAWAAN, self::UNGGAHAN, self::KOSONG];

    /** @return self::BAWAAN|self::UNGGAHAN|self::KOSONG */
    public static function pilihan(string $jenis): string
    {
        $nilai = config('sekolah.'.$jenis);

        if ($nilai === null) {
            return self::BAWAAN;
        }

        return trim((string) $nilai) === '' ? self::KOSONG : self::UNGGAHAN;
    }

    /** Jalur relatif public/, atau null bila sengaja dikosongkan. */
    public static function berkas(string $jenis): ?string
    {
        return match (self::pilihan($jenis)) {
            self::BAWAAN => self::BERKAS_BAWAAN[$jenis],
            self::KOSONG => null,
            default => trim((string) config('sekolah.'.$jenis)),
        };
    }

    public static function url(string $jenis): ?string
    {
        $berkas = self::berkas($jenis);

        return $berkas === null ? null : asset($berkas);
    }

    /**
     * True bila jalur ini berkas unggahan. Diperiksa sebelum menghapus, supaya
     * berkas bawaan di public/images/kiddies tidak ikut terhapus saat panitia
     * beralih ke bawaan atau mengosongkan gambarnya.
     */
    public static function unggahan(?string $jalur): bool
    {
        return $jalur !== null && str_starts_with($jalur, 'images/'.self::DISK.'/');
    }
}
