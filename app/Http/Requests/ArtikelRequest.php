<?php

namespace App\Http\Requests;

use App\Support\HtmlAman;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArtikelRequest extends FormRequest
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
            'judul' => ['required', 'string', 'max:200'],
            'kategori' => ['nullable', 'string', 'max:80'],
            'ringkasan' => ['required', 'string', 'max:400'],
            // Batasnya longgar karena yang dikirim HTML: tag pembungkus
            // ikut terhitung. Panjang tulisan yang sebenarnya dijaga
            // aturan isi_teks di bawah.
            'isi' => ['required', 'string', 'max:200000'],
            'isi_teks' => ['required', 'string', 'max:50000'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hapus_gambar' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['draf', 'terbit'])],
            'terbit_pada' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Editor mengirim "<div><br></div>" saat kotaknya dikosongkan, dan itu
        // lolos aturan required padahal tidak ada tulisannya. Teks polosnya
        // dititipkan sebagai isian tersendiri supaya yang diperiksa panjang
        // tulisannya, bukan panjang tag-nya.
        $this->merge([
            'hapus_gambar' => $this->boolean('hapus_gambar'),
            'isi_teks' => HtmlAman::teks($this->input('isi')) ?: null,
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'judul' => 'judul artikel',
            'kategori' => 'kategori',
            'ringkasan' => 'ringkasan',
            'isi' => 'isi artikel',
            'isi_teks' => 'isi artikel',
            'gambar' => 'gambar sampul',
            'status' => 'status',
            'terbit_pada' => 'tanggal terbit',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'gambar.image' => 'Gambar sampul harus berupa berkas gambar (JPG, PNG, atau WEBP).',
            'gambar.max' => 'Ukuran gambar sampul maksimal 2 MB.',
            'ringkasan.max' => 'Ringkasan maksimal 400 karakter agar rapi di kartu blog.',
            'isi_teks.required' => 'Isi artikel masih kosong.',
            'isi_teks.max' => 'Isi artikel terlalu panjang, maksimal 50.000 karakter.',
        ];
    }
}
