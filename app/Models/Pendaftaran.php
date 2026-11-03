<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Pendaftaran extends Model
{
    /** @use HasFactory<\Database\Factories\PendaftaranFactory> */
    use HasFactory;

    protected $table = 'pendaftaran_siswa';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'diverifikasi_pada' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Nomor pendaftaran
    |--------------------------------------------------------------------------
    */

    /**
     * Membuat nomor pendaftaran berurutan, mis. PPDB-2627-0001.
     *
     * Dipanggil di dalam transaksi agar nomor tidak bentrok saat dua orang
     * mengirim formulir pada saat yang hampir bersamaan.
     */
    public static function buatNomor(string $tahunAjaran): string
    {
        $kode = substr($tahunAjaran, 2, 2).substr($tahunAjaran, -2);
        $prefix = config('ppdb.prefix_nomor', 'PPDB').'-'.$kode.'-';

        $terakhir = static::query()
            ->where('nomor_pendaftaran', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('nomor_pendaftaran')
            ->value('nomor_pendaftaran');

        $urutan = $terakhir ? ((int) substr($terakhir, -4)) + 1 : 1;

        return $prefix.str_pad((string) $urutan, 4, '0', STR_PAD_LEFT);
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi
    |--------------------------------------------------------------------------
    */

    public function jenjang(): BelongsTo
    {
        return $this->belongsTo(Jenjang::class, 'jenjang_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(StatusPendaftaran::class, 'status_id');
    }

    public function alamat(): BelongsTo
    {
        return $this->belongsTo(Alamat::class, 'alamat_id');
    }

    public function orangTua(): HasMany
    {
        return $this->hasMany(OrangTua::class, 'pendaftaran_id');
    }

    public function berkas(): HasMany
    {
        return $this->hasMany(BerkasPendaftaran::class, 'pendaftaran_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    /** Relasi yang hampir selalu ikut dibaca bersama pendaftarannya. */
    public function scopeLengkap(Builder $query): Builder
    {
        return $query->with(['jenjang', 'status', 'alamat', 'orangTua', 'berkas']);
    }

    /*
    |--------------------------------------------------------------------------
    | Scope
    |--------------------------------------------------------------------------
    */

    public function scopeCari(Builder $query, ?string $kata): Builder
    {
        if (blank($kata)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($kata): void {
            $q->where('nomor_pendaftaran', 'like', "%{$kata}%")
                ->orWhere('nama_lengkap', 'like', "%{$kata}%")
                ->orWhere('whatsapp', 'like', "%{$kata}%")
                // Nama orang tua kini di tabel tersendiri, jadi dicari lewat
                // relasinya supaya kotak pencarian panitia tetap menemukannya.
                ->orWhereHas('orangTua', function (Builder $o) use ($kata): void {
                    $o->where('nama', 'like', "%{$kata}%");
                });
        });
    }

    /** Filter berdasarkan kode status, mis. "diterima". */
    public function scopeStatus(Builder $query, ?string $kode): Builder
    {
        return blank($kode) ? $query : $query->whereRelation('status', 'kode', $kode);
    }

    /** Filter berdasarkan kode jenjang, mis. "ra-a". */
    public function scopeJenjang(Builder $query, ?string $kode): Builder
    {
        return blank($kode) ? $query : $query->whereRelation('jenjang', 'kode', $kode);
    }

    public function scopeTahunAjaran(Builder $query, ?string $tahunAjaran): Builder
    {
        return blank($tahunAjaran) ? $query : $query->where('tahun_ajaran', $tahunAjaran);
    }

    /*
    |--------------------------------------------------------------------------
    | Atribut turunan
    |--------------------------------------------------------------------------
    */

    public function getJenjangLabelAttribute(): string
    {
        return $this->jenjang?->nama ?? '-';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status?->label ?? '-';
    }

    public function getStatusKodeAttribute(): ?string
    {
        return $this->status?->kode;
    }

    public function getJenisKelaminLabelAttribute(): string
    {
        return $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    }

    /**
     * Usia anak dalam format "4 tahun 3 bulan" pada tanggal tertentu.
     */
    public function usiaPada(?Carbon $tanggal = null): string
    {
        if (! $this->tanggal_lahir) {
            return '-';
        }

        $selisih = $this->tanggal_lahir->diff($tanggal ?? Carbon::now());

        return $selisih->y.' tahun '.$selisih->m.' bulan';
    }

    public function getUsiaAttribute(): string
    {
        return $this->usiaPada();
    }

    /*
    |--------------------------------------------------------------------------
    | Orang tua & berkas
    |--------------------------------------------------------------------------
    */

    /** Data satu peran, mis. orangTuaPeran('ayah'). */
    public function orangTuaPeran(string $peran): ?OrangTua
    {
        return $this->orangTua->firstWhere('peran', $peran);
    }

    /** Pintasan yang sering dipakai di daftar dan ekspor. */
    public function getNamaAyahAttribute(): ?string
    {
        return $this->orangTuaPeran(OrangTua::AYAH)?->nama;
    }

    public function getNamaIbuAttribute(): ?string
    {
        return $this->orangTuaPeran(OrangTua::IBU)?->nama;
    }

    public function berkasJenis(string $jenis): ?BerkasPendaftaran
    {
        return $this->berkas->firstWhere('jenis', $jenis);
    }

    /**
     * Seluruh jenis berkas yang diminta beserta status unggahannya, termasuk
     * yang belum diunggah. Dipakai panel admin dan bukti pendaftaran.
     *
     * @return array<int, array{jenis: string, label: string, wajib: bool, berkas: BerkasPendaftaran|null}>
     */
    public function daftarBerkas(): array
    {
        $hasil = [];

        foreach (BerkasPendaftaran::JENIS as $jenis => $keterangan) {
            $hasil[] = [
                'jenis' => $jenis,
                'label' => $keterangan['label'],
                'wajib' => $keterangan['wajib'],
                'berkas' => $this->berkasJenis($jenis),
            ];
        }

        return $hasil;
    }
}
