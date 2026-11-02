<?php

namespace App\Support;

/**
 * Logo dan gambar bagikan sekolah.
 *
 * Keduanya diunggah panitia dari Panel Admin > Identitas Sekolah dan tersimpan
 * di tabel "pengaturan" sebagai jalur relatif terhadap public/, misalnya
 * "images/merek/logo-20261102-a1b2c3d4.png".
 *
 * Selama belum ada yang diunggah, yang dipakai adalah berkas bawaan yang ikut
 * dalam repositori.
 */
final class Merek
{
    /** Folder unggahan di dalam public/, sekaligus nama disk-nya. */
    public const DISK = 'merek';

    /**
     * Jenis gambar => berkas bawaannya. Ini satu-satunya tempat nilai bawaan
     * ditulis; config/sekolah.php sengaja dibiarkan null supaya tidak ada dua
     * tempat yang harus disamakan.
     *
     * @var array<string, string>
     */
    public const BAWAAN = [
        'logo' => 'images/kiddies/logo-mark.svg',
        'gambar_bagikan' => 'images/og.jpg',
    ];

    /** Jalur relatif public/, selalu terisi. */
    public static function berkas(string $jenis): string
    {
        $jalur = trim((string) config('sekolah.'.$jenis));

        return $jalur !== '' ? $jalur : self::BAWAAN[$jenis];
    }

    public static function url(string $jenis): string
    {
        return asset(self::berkas($jenis));
    }

    /** True bila masih memakai berkas bawaan, belum ada yang diunggah. */
    public static function bawaan(string $jenis): bool
    {
        return self::berkas($jenis) === self::BAWAAN[$jenis];
    }

    /**
     * True bila jalur ini berkas unggahan. Diperiksa sebelum menghapus, supaya
     * berkas bawaan di public/images/kiddies tidak ikut terhapus saat panitia
     * menekan "kembalikan ke bawaan".
     */
    public static function unggahan(?string $jalur): bool
    {
        return $jalur !== null && str_starts_with($jalur, 'images/'.self::DISK.'/');
    }
}
