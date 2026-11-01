<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/**
 * Pengecil gambar seadanya di atas ekstensi GD, tanpa paket tambahan.
 *
 * Foto dari ponsel biasanya 3000-4000 piksel dan 4-5 MB. Dikirim apa adanya,
 * satu halaman galeri bisa puluhan MB. Di sini tiap foto diperkecil dan
 * disimpan ulang sebagai JPEG sebelum masuk ke folder publik.
 */
class Gambar
{
    /** Sisi terpanjang hasil akhir, dalam piksel. */
    public const SISI_MAKS = 1400;

    public const MUTU = 78;

    /**
     * Kembalikan true bila gambar berhasil diperkecil dan ditulis ke $tujuan.
     * Bila GD tidak ada atau formatnya tak dikenal, pemanggil harus menyalin
     * berkas aslinya sendiri.
     */
    public static function kecilkan(UploadedFile $berkas, string $tujuan): bool
    {
        if (! function_exists('imagecreatetruecolor')) {
            return false;
        }

        $sumber = static::baca($berkas->getRealPath(), $berkas->getMimeType());

        if ($sumber === null) {
            return false;
        }

        $lebar = imagesx($sumber);
        $tinggi = imagesy($sumber);
        $skala = min(1, static::SISI_MAKS / max($lebar, $tinggi));

        $lebarBaru = max(1, (int) round($lebar * $skala));
        $tinggiBaru = max(1, (int) round($tinggi * $skala));

        $hasil = imagecreatetruecolor($lebarBaru, $tinggiBaru);

        // PNG/WEBP bisa transparan; JPEG tidak. Latar putih supaya area
        // transparan tidak jadi hitam setelah dikonversi.
        $putih = imagecolorallocate($hasil, 255, 255, 255);
        imagefilledrectangle($hasil, 0, 0, $lebarBaru, $tinggiBaru, $putih);

        imagecopyresampled($hasil, $sumber, 0, 0, 0, 0, $lebarBaru, $tinggiBaru, $lebar, $tinggi);

        $berhasil = imagejpeg($hasil, $tujuan, static::MUTU);

        imagedestroy($sumber);
        imagedestroy($hasil);

        return $berhasil;
    }

    /**
     * @return \GdImage|null
     */
    private static function baca(string $path, ?string $mime)
    {
        $gambar = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if ($gambar === false) {
            return null;
        }

        // Foto ponsel sering menyimpan arah lewat EXIF, bukan piksel. Tanpa ini
        // foto potret bisa tampil miring 90 derajat.
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($path);
            $arah = $exif['Orientation'] ?? 1;

            $gambar = match ($arah) {
                3 => imagerotate($gambar, 180, 0),
                6 => imagerotate($gambar, -90, 0),
                8 => imagerotate($gambar, 90, 0),
                default => $gambar,
            };
        }

        return $gambar;
    }
}
