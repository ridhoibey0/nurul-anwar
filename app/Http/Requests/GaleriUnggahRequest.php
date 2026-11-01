<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GaleriUnggahRequest extends FormRequest
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
            'foto' => ['required', 'array', 'max:20'],
            'foto.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'foto.required' => 'Pilih dulu foto yang ingin diunggah.',
            'foto.max' => 'Maksimal 20 foto sekali unggah.',
            'foto.*.image' => 'Semua berkas harus berupa gambar (JPG, PNG, atau WEBP).',
            'foto.*.max' => 'Ukuran tiap foto maksimal 8 MB.',
        ];
    }
}
