<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Penyimpanan pengaturan sederhana (kunci => nilai) agar panitia bisa
 * mengubah identitas sekolah, kontak, dan pengaturan PPDB tanpa menyentuh
 * .env atau me-restart server.
 *
 * Nilai di .env / config tetap dipakai sebagai nilai bawaan selama baris
 * yang bersangkutan belum pernah disimpan dari panel admin.
 */
class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $guarded = ['id'];

    /** Kunci di tabel ini dipetakan ke kunci config yang ditimpa. */
    public const PETA = [
        'ppdb_buka' => 'ppdb.buka',
        'ppdb_tahun_ajaran' => 'ppdb.tahun_ajaran',
        'ppdb_dibuka_pada' => 'ppdb.dibuka_pada',
        'ppdb_ditutup_pada' => 'ppdb.ditutup_pada',

        'sekolah_nama' => 'sekolah.nama',
        'sekolah_nama_pendek' => 'sekolah.nama_pendek',
        'sekolah_tagline' => 'sekolah.tagline',
        'sekolah_tahun_berdiri' => 'sekolah.tahun_berdiri',
        'sekolah_akreditasi' => 'sekolah.akreditasi',

        'sekolah_alamat' => 'sekolah.alamat',
        'sekolah_kota' => 'sekolah.kota',
        'sekolah_provinsi' => 'sekolah.provinsi',
        'sekolah_jam_operasional' => 'sekolah.jam_operasional',

        'sekolah_telepon' => 'sekolah.telepon',
        'sekolah_whatsapp' => 'sekolah.whatsapp',
        'sekolah_email' => 'sekolah.email',
        'sekolah_instagram' => 'sekolah.instagram',

        'sekolah_instagram_url' => 'sekolah.sosial.instagram',
        'sekolah_facebook_url' => 'sekolah.sosial.facebook',
        'sekolah_youtube_url' => 'sekolah.sosial.youtube',

        'sekolah_peta_lat' => 'sekolah.peta_lat',
        'sekolah_peta_lng' => 'sekolah.peta_lng',
        'sekolah_peta_url' => 'sekolah.peta_url',
    ];

    /** Kunci yang disimpan sebagai "1"/"0" tapi dipakai sebagai boolean. */
    private const BOOLEAN = ['ppdb_buka'];

    /** Kunci penampung hasil query di container, supaya cukup satu query per request. */
    private const MEMO = 'pengaturan.tersimpan';

    /**
     * @return array<string, string|null>
     */
    public static function semua(): array
    {
        if (app()->bound(self::MEMO)) {
            return app(self::MEMO);
        }

        try {
            $nilai = static::query()->pluck('nilai', 'kunci')->all();
        } catch (Throwable) {
            // Tabel belum dibuat (migrasi pertama) atau database belum siap.
            // Jangan di-memo supaya pemanggilan berikutnya mencoba lagi.
            return [];
        }

        app()->instance(self::MEMO, $nilai);

        return $nilai;
    }

    public static function ambil(string $kunci, ?string $bawaan = null): ?string
    {
        return static::semua()[$kunci] ?? $bawaan;
    }

    /**
     * @param  array<string, string|bool|int|null>  $nilai
     */
    public static function simpan(array $nilai): void
    {
        foreach ($nilai as $kunci => $isi) {
            if (is_bool($isi)) {
                $isi = $isi ? '1' : '0';
            }

            static::query()->updateOrCreate(
                ['kunci' => $kunci],
                ['nilai' => $isi === null ? null : (string) $isi],
            );
        }

        app()->forgetInstance(self::MEMO);
    }

    /**
     * Timpa config dengan nilai dari database. Dipanggil dari AppServiceProvider
     * supaya seluruh kode lain tetap cukup memakai config('ppdb.*').
     */
    public static function terapkanKeConfig(): void
    {
        $tersimpan = static::semua();

        foreach (self::PETA as $kunci => $tujuan) {
            // Baris yang belum pernah disimpan dibiarkan memakai nilai .env.
            // String kosong tetap diterapkan: itu berarti panitia sengaja
            // mengosongkan isiannya, bukan belum pernah mengisinya.
            if (! array_key_exists($kunci, $tersimpan) || $tersimpan[$kunci] === null) {
                continue;
            }

            config([
                $tujuan => in_array($kunci, self::BOOLEAN, true)
                    ? filter_var($tersimpan[$kunci], FILTER_VALIDATE_BOOLEAN)
                    : $tersimpan[$kunci],
            ]);
        }
    }
}
