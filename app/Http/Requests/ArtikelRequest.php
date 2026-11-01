<?php

namespace App\Http\Requests;

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
            'isi' => ['required', 'string', 'max:50000'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hapus_gambar' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['draf', 'terbit'])],
            'terbit_pada' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'hapus_gambar' => $this->boolean('hapus_gambar'),
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
        ];
    }
}
