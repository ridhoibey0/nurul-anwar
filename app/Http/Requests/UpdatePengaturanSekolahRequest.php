<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePengaturanSekolahRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Rute sudah berada di belakang middleware auth.
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:120'],
            'nama_pendek' => ['required', 'string', 'max:60'],
            'tagline' => ['required', 'string', 'max:120'],
            'tahun_berdiri' => ['required', 'integer', 'min:1900', 'max:'.date('Y')],
            'akreditasi' => ['nullable', 'string', 'max:10'],

            'alamat' => ['required', 'string', 'max:255'],
            'kota' => ['required', 'string', 'max:80'],
            'provinsi' => ['required', 'string', 'max:80'],
            'jam_operasional' => ['required', 'string', 'max:120'],

            // Telepon dibiarkan apa adanya supaya "(0265) 338279" tetap bisa
            // ditulis seperti di papan nama; tautan tel: dirapikan sendiri.
            'telepon' => ['required', 'string', 'max:40'],
            'whatsapp' => ['required', 'string', 'regex:/^62[0-9]{8,15}$/'],
            'email' => ['required', 'email:rfc', 'max:120'],
            'instagram' => ['nullable', 'string', 'max:60'],

            'instagram_url' => ['nullable', 'url:https', 'max:255'],
            'facebook_url' => ['nullable', 'url:https', 'max:255'],
            'youtube_url' => ['nullable', 'url:https', 'max:255'],

            'peta_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'peta_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'peta_url' => ['nullable', 'url:https', 'max:255'],

            /*
            | SVG tidak diterima dengan sengaja, walau logo biasanya tersedia
            | dalam bentuk itu: berkas SVG boleh memuat <script>, dan logo ini
            | disajikan dari domain sekolah sendiri. Satu berkas SVG yang
            | disiapkan orang lain lalu diunggah panitia akan berjalan sebagai
            | kode di domain yang sama dengan panel admin.
            */
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'gambar_bagikan' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],

            'hapus_logo' => ['nullable', 'boolean'],
            'hapus_gambar_bagikan' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $instagram = $this->input('instagram');

        $this->merge([
            // Panitia biasanya menyalin nomor dalam bentuk 0812..., +62 812...,
            // atau 0812-3456-7890. Semua dirapikan jadi 62xxxxxxxxxx sebelum
            // divalidasi, supaya mereka tidak perlu tahu formatnya.
            'whatsapp' => $this->rapikanWhatsapp($this->input('whatsapp')),
            'instagram' => is_string($instagram) && trim($instagram) !== ''
                ? '@'.ltrim(trim($instagram), '@')
                : null,
        ]);
    }

    /**
     * Isian bisa saja datang bukan sebagai string (mis. dikirim sebagai array
     * dari luar formulir). Dikembalikan apa adanya supaya aturan validasi yang
     * menolaknya, bukan TypeError di sini.
     */
    private function rapikanWhatsapp(mixed $nomor): mixed
    {
        if (! is_string($nomor)) {
            return $nomor;
        }

        $angka = preg_replace('/[^0-9]/', '', $nomor) ?? '';

        if (str_starts_with($angka, '0')) {
            return '62'.substr($angka, 1);
        }

        // Ditulis tanpa kode negara maupun angka 0 di depan, mis. "81234...".
        if (str_starts_with($angka, '8')) {
            return '62'.$angka;
        }

        return $angka;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama' => 'nama sekolah',
            'nama_pendek' => 'nama pendek',
            'tagline' => 'tagline',
            'tahun_berdiri' => 'tahun berdiri',
            'akreditasi' => 'akreditasi',
            'alamat' => 'alamat',
            'kota' => 'kota',
            'provinsi' => 'provinsi',
            'jam_operasional' => 'jam operasional',
            'telepon' => 'nomor telepon',
            'whatsapp' => 'nomor WhatsApp',
            'email' => 'alamat email',
            'instagram' => 'nama akun Instagram',
            'instagram_url' => 'tautan Instagram',
            'facebook_url' => 'tautan Facebook',
            'youtube_url' => 'tautan YouTube',
            'peta_lat' => 'garis lintang',
            'peta_lng' => 'garis bujur',
            'peta_url' => 'tautan Google Maps',
            'logo' => 'logo sekolah',
            'gambar_bagikan' => 'gambar bagikan',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'whatsapp.regex' => 'Nomor WhatsApp tidak dikenali. Tulis seperti 0812-3456-7890 atau 6281234567890.',
            'tahun_berdiri.max' => 'Tahun berdiri tidak boleh melebihi tahun ini.',
            'instagram_url.url' => 'Tautan Instagram harus lengkap dan diawali https://, misalnya https://instagram.com/ra.nurulanwar',
            'facebook_url.url' => 'Tautan Facebook harus lengkap dan diawali https://.',
            'youtube_url.url' => 'Tautan YouTube harus lengkap dan diawali https://.',
            'peta_url.url' => 'Tautan Google Maps harus lengkap dan diawali https://.',

            'logo.image' => 'Logo harus berupa gambar. Berkas SVG belum bisa diterima, mohon simpan dulu sebagai PNG.',
            'logo.mimes' => 'Logo harus berupa PNG, JPG, atau WEBP. Berkas SVG belum bisa diterima.',
            'logo.max' => 'Ukuran berkas logo maksimal 2 MB.',
            'gambar_bagikan.image' => 'Gambar bagikan harus berupa gambar PNG, JPG, atau WEBP.',
            'gambar_bagikan.mimes' => 'Gambar bagikan harus berupa PNG, JPG, atau WEBP.',
            'gambar_bagikan.max' => 'Ukuran gambar bagikan maksimal 4 MB.',
        ];
    }
}
