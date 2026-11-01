<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePendaftaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) config('ppdb.buka');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maksBerkas = (int) config('ppdb.berkas.maks_kb', 2048);
        $tipeBerkas = implode(',', config('ppdb.berkas.tipe', ['jpg', 'png', 'pdf']));

        return [
            // Langkah 1 - data calon siswa
            'jenjang' => ['required', Rule::in(array_keys(config('ppdb.jenjang')))],
            'nama_lengkap' => ['required', 'string', 'min:3', 'max:120'],
            'nama_panggilan' => ['nullable', 'string', 'max:60'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['required', 'string', 'max:80'],
            'tanggal_lahir' => ['required', 'date', 'before:today', 'after:'.now()->subYears(15)->toDateString()],
            'nik' => ['nullable', 'digits:16'],
            'anak_ke' => ['nullable', 'integer', 'min:1', 'max:20'],
            'jumlah_saudara' => ['nullable', 'integer', 'min:0', 'max:20'],
            'agama' => ['required', 'string', 'max:32'],

            // Langkah 2 - alamat & riwayat
            'alamat' => ['required', 'string', 'max:500'],
            'kelurahan' => ['nullable', 'string', 'max:80'],
            'kecamatan' => ['nullable', 'string', 'max:80'],
            'kota' => ['nullable', 'string', 'max:80'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'asal_sekolah' => ['nullable', 'string', 'max:120'],
            'riwayat_kesehatan' => ['nullable', 'string', 'max:500'],

            // Langkah 3 - orang tua / wali
            'nama_ayah' => ['required', 'string', 'max:120'],
            'nik_ayah' => ['nullable', 'digits:16'],
            'pendidikan_ayah' => ['nullable', 'string', 'max:32'],
            'pekerjaan_ayah' => ['nullable', 'string', 'max:80'],
            'penghasilan_ayah' => ['nullable', 'string', 'max:40'],
            'telepon_ayah' => ['nullable', 'string', 'max:24'],

            'nama_ibu' => ['required', 'string', 'max:120'],
            'nik_ibu' => ['nullable', 'digits:16'],
            'pendidikan_ibu' => ['nullable', 'string', 'max:32'],
            'pekerjaan_ibu' => ['nullable', 'string', 'max:80'],
            'penghasilan_ibu' => ['nullable', 'string', 'max:40'],
            'telepon_ibu' => ['nullable', 'string', 'max:24'],

            'nama_wali' => ['nullable', 'string', 'max:120'],
            'hubungan_wali' => ['nullable', 'string', 'max:40'],
            'telepon_wali' => ['nullable', 'string', 'max:24'],

            'whatsapp' => ['required', 'string', 'regex:/^0[0-9]{8,13}$/'],
            'email' => ['nullable', 'email', 'max:120'],

            // Langkah 4 - berkas & persetujuan
            'berkas_kk' => ['required', 'file', "mimes:{$tipeBerkas}", "max:{$maksBerkas}"],
            'berkas_akta' => ['required', 'file', "mimes:{$tipeBerkas}", "max:{$maksBerkas}"],
            'berkas_foto' => ['nullable', 'file', 'mimes:jpg,jpeg,png', "max:{$maksBerkas}"],
            'catatan_orang_tua' => ['nullable', 'string', 'max:500'],
            'persetujuan' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'jenjang' => 'jenjang yang dipilih',
            'nama_lengkap' => 'nama lengkap anak',
            'nama_panggilan' => 'nama panggilan',
            'jenis_kelamin' => 'jenis kelamin',
            'tempat_lahir' => 'tempat lahir',
            'tanggal_lahir' => 'tanggal lahir',
            'nik' => 'NIK anak',
            'anak_ke' => 'anak ke-',
            'jumlah_saudara' => 'jumlah saudara',
            'alamat' => 'alamat tempat tinggal',
            'kode_pos' => 'kode pos',
            'asal_sekolah' => 'asal sekolah / PAUD',
            'riwayat_kesehatan' => 'riwayat kesehatan',
            'nama_ayah' => 'nama ayah',
            'nik_ayah' => 'NIK ayah',
            'pendidikan_ayah' => 'pendidikan ayah',
            'pekerjaan_ayah' => 'pekerjaan ayah',
            'penghasilan_ayah' => 'penghasilan ayah',
            'telepon_ayah' => 'nomor telepon ayah',
            'nama_ibu' => 'nama ibu',
            'nik_ibu' => 'NIK ibu',
            'pendidikan_ibu' => 'pendidikan ibu',
            'pekerjaan_ibu' => 'pekerjaan ibu',
            'penghasilan_ibu' => 'penghasilan ibu',
            'telepon_ibu' => 'nomor telepon ibu',
            'nama_wali' => 'nama wali',
            'hubungan_wali' => 'hubungan dengan anak',
            'telepon_wali' => 'nomor telepon wali',
            'whatsapp' => 'nomor WhatsApp aktif',
            'email' => 'alamat email',
            'berkas_kk' => 'berkas Kartu Keluarga',
            'berkas_akta' => 'berkas Akta Kelahiran',
            'berkas_foto' => 'pas foto anak',
            'catatan_orang_tua' => 'catatan tambahan',
            'persetujuan' => 'pernyataan kebenaran data',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'whatsapp.regex' => 'Nomor WhatsApp diisi angka saja diawali 0, contoh: 081234567890.',
            'persetujuan.accepted' => 'Centang pernyataan bahwa data yang diisi sudah benar.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'tanggal_lahir.after' => 'Tanggal lahir sepertinya tidak wajar, mohon periksa kembali.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'whatsapp' => preg_replace('/[^0-9]/', '', (string) $this->input('whatsapp')),
            'nik' => preg_replace('/[^0-9]/', '', (string) $this->input('nik')) ?: null,
            'nik_ayah' => preg_replace('/[^0-9]/', '', (string) $this->input('nik_ayah')) ?: null,
            'nik_ibu' => preg_replace('/[^0-9]/', '', (string) $this->input('nik_ibu')) ?: null,
        ]);
    }
}
