<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Memindahkan pendaftaran lama ke struktur yang sudah dinormalisasi.
 *
 * Migrasi create_pendaftaran_siswa_table ditulis ulang ke bentuk barunya,
 * jadi pemasangan baru langsung mendapat struktur yang benar. Tapi database
 * yang sudah terlanjur berjalan -- server sekolah -- masih memakai tabel lama
 * dengan 48 kolom, dan migrasi yang sudah pernah jalan tidak akan dijalankan
 * ulang oleh Laravel.
 *
 * Berkas inilah jembatannya: memindahkan isi kolom lama ke tabel-tabel
 * barunya, lalu membuang kolom yang sudah tidak dipakai. Tidak ada satu baris
 * pun yang hilang.
 *
 * Di pemasangan baru migrasi ini tidak mengerjakan apa pun, karena kolom
 * lamanya memang tidak pernah ada.
 */
return new class extends Migration
{
    /** Kolom lama yang isinya sudah pindah dan karenanya dibuang. */
    private const KOLOM_LAMA = [
        'jenjang', 'status',
        'alamat', 'kelurahan', 'kecamatan', 'kota', 'kode_pos',
        'nama_ayah', 'nik_ayah', 'pendidikan_ayah', 'pekerjaan_ayah', 'penghasilan_ayah', 'telepon_ayah',
        'nama_ibu', 'nik_ibu', 'pendidikan_ibu', 'pekerjaan_ibu', 'penghasilan_ibu', 'telepon_ibu',
        'nama_wali', 'hubungan_wali', 'telepon_wali',
        'berkas_kk', 'berkas_akta', 'berkas_foto',
    ];

    public function up(): void
    {
        // Pemasangan baru sudah memakai struktur barunya sejak awal.
        if (! Schema::hasColumn('pendaftaran_siswa', 'nama_ayah')) {
            return;
        }

        // Diperiksa sebelum apa pun disentuh: kalau ada kode yang tidak
        // dikenal, lebih baik berhenti sekarang daripada berhenti di tengah
        // jalan dengan tabel yang sudah separuh berubah.
        $this->pastikanKodeDikenal();

        Schema::table('pendaftaran_siswa', function (Blueprint $table): void {
            // Ditambahkan tanpa foreign key dulu. Di MySQL, mengubah sifat
            // kolom yang sudah terikat foreign key sering ditolak, dan kolom
            // ini masih harus dijadikan NOT NULL setelah terisi.
            $table->unsignedBigInteger('jenjang_id')->nullable()->after('tahun_ajaran');
            $table->unsignedBigInteger('alamat_id')->nullable()->after('agama');
            $table->unsignedBigInteger('status_id')->nullable()->after('catatan_orang_tua');
        });

        $this->pindahkanIsi();

        // Jenjang dan status wajib ada; sekarang kolomnya sudah pasti terisi.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE pendaftaran_siswa MODIFY jenjang_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE pendaftaran_siswa MODIFY status_id BIGINT UNSIGNED NOT NULL');
        }

        Schema::table('pendaftaran_siswa', function (Blueprint $table): void {
            $table->dropIndex(['tahun_ajaran', 'status']);
        });

        Schema::table('pendaftaran_siswa', function (Blueprint $table): void {
            $table->dropColumn(self::KOLOM_LAMA);
        });

        Schema::table('pendaftaran_siswa', function (Blueprint $table): void {
            $table->foreign('jenjang_id')->references('id')->on('jenjang')->restrictOnDelete();
            $table->foreign('status_id')->references('id')->on('status_pendaftaran')->restrictOnDelete();
            $table->foreign('alamat_id')->references('id')->on('alamat')->nullOnDelete();

            $table->index(['tahun_ajaran', 'status_id']);
        });
    }

    /**
     * Berhenti dengan pesan yang jelas bila ada pendaftaran memakai kode
     * jenjang atau status yang tidak ada di tabel referensinya.
     */
    private function pastikanKodeDikenal(): void
    {
        foreach ([
            ['kolom' => 'jenjang', 'tabel' => 'jenjang'],
            ['kolom' => 'status', 'tabel' => 'status_pendaftaran'],
        ] as $periksa) {
            $dikenal = DB::table($periksa['tabel'])->pluck('kode')->all();

            $asing = DB::table('pendaftaran_siswa')
                ->whereNotNull($periksa['kolom'])
                ->whereNotIn($periksa['kolom'], $dikenal)
                ->distinct()
                ->pluck($periksa['kolom'])
                ->all();

            if ($asing !== []) {
                throw new RuntimeException(
                    'Ada pendaftaran dengan '.$periksa['kolom'].' yang tidak dikenal: '
                    .implode(', ', $asing).'. Tambahkan dulu ke tabel '.$periksa['tabel']
                    .', lalu jalankan ulang php artisan migrate.'
                );
            }
        }
    }

    private function pindahkanIsi(): void
    {
        $jenjang = DB::table('jenjang')->pluck('id', 'kode');
        $status = DB::table('status_pendaftaran')->pluck('id', 'kode');
        $sekarang = now();

        DB::table('pendaftaran_siswa')->orderBy('id')->chunkById(100, function ($baris) use ($jenjang, $status, $sekarang): void {
            foreach ($baris as $p) {
                $alamatId = DB::table('alamat')->insertGetId([
                    'jalan' => (string) $p->alamat,
                    'kelurahan' => $p->kelurahan,
                    'kecamatan' => $p->kecamatan,
                    'kota' => $p->kota,
                    'kode_pos' => $p->kode_pos,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ]);

                DB::table('pendaftaran_siswa')->where('id', $p->id)->update([
                    'jenjang_id' => $jenjang[$p->jenjang] ?? null,
                    'status_id' => $status[$p->status] ?? null,
                    'alamat_id' => $alamatId,
                ]);

                $this->pindahkanOrangTua($p, $sekarang);
                $this->pindahkanBerkas($p, $sekarang);
            }
        });
    }

    private function pindahkanOrangTua(object $p, mixed $sekarang): void
    {
        $peta = [
            'ayah' => [
                'nama' => $p->nama_ayah,
                'nik' => $p->nik_ayah,
                'pendidikan' => $p->pendidikan_ayah,
                'pekerjaan' => $p->pekerjaan_ayah,
                'penghasilan' => $p->penghasilan_ayah,
                'telepon' => $p->telepon_ayah,
                'hubungan' => null,
            ],
            'ibu' => [
                'nama' => $p->nama_ibu,
                'nik' => $p->nik_ibu,
                'pendidikan' => $p->pendidikan_ibu,
                'pekerjaan' => $p->pekerjaan_ibu,
                'penghasilan' => $p->penghasilan_ibu,
                'telepon' => $p->telepon_ibu,
                'hubungan' => null,
            ],
            'wali' => [
                'nama' => $p->nama_wali,
                'nik' => null,
                'pendidikan' => null,
                'pekerjaan' => null,
                'penghasilan' => null,
                'telepon' => $p->telepon_wali,
                'hubungan' => $p->hubungan_wali,
            ],
        ];

        $baris = [];

        foreach ($peta as $peran => $isi) {
            // Wali umumnya kosong; barisnya tidak dibuat daripada menyimpan
            // baris yang seluruh kolomnya kosong.
            if (blank($isi['nama'])) {
                continue;
            }

            $baris[] = $isi + [
                'pendaftaran_id' => $p->id,
                'peran' => $peran,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ];
        }

        if ($baris !== []) {
            DB::table('pendaftaran_orang_tua')->insert($baris);
        }
    }

    private function pindahkanBerkas(object $p, mixed $sekarang): void
    {
        $baris = [];

        foreach (['kk', 'akta', 'foto'] as $jenis) {
            $kolom = 'berkas_'.$jenis;

            if (blank($p->{$kolom})) {
                continue;
            }

            $baris[] = [
                'pendaftaran_id' => $p->id,
                'jenis' => $jenis,
                'path' => $p->{$kolom},
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ];
        }

        if ($baris !== []) {
            DB::table('pendaftaran_berkas')->insert($baris);
        }
    }

    /**
     * Sengaja tidak mengembalikan apa pun.
     *
     * Membalikkan pemindahan ini berarti menebak ulang 25 kolom dari tiga
     * tabel, dan tebakan yang salah merusak data pendaftar. Bila perlu
     * kembali ke struktur lama, pulihkan dari cadangan database.
     */
    public function down(): void
    {
        //
    }
};
