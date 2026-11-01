<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
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
                ->orWhere('nama_ayah', 'like', "%{$kata}%")
                ->orWhere('nama_ibu', 'like', "%{$kata}%")
                ->orWhere('whatsapp', 'like', "%{$kata}%");
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return blank($status) ? $query : $query->where('status', $status);
    }

    public function scopeJenjang(Builder $query, ?string $jenjang): Builder
    {
        return blank($jenjang) ? $query : $query->where('jenjang', $jenjang);
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
        return config("ppdb.jenjang.{$this->jenjang}.label", $this->jenjang);
    }

    public function getStatusLabelAttribute(): string
    {
        return config("ppdb.status.{$this->status}.label", $this->status);
    }

    public function getStatusWarnaAttribute(): string
    {
        return config("ppdb.status.{$this->status}.warna", 'slate');
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

    /**
     * Daftar berkas yang diunggah, siap ditampilkan di panel admin.
     *
     * @return array<int, array{label: string, path: string|null}>
     */
    public function daftarBerkas(): array
    {
        return [
            ['label' => 'Kartu Keluarga', 'path' => $this->berkas_kk],
            ['label' => 'Akta Kelahiran', 'path' => $this->berkas_akta],
            ['label' => 'Pas Foto Anak', 'path' => $this->berkas_foto],
        ];
    }
}
