<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePengaturanPpdbRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Rute sudah berada di belakang middleware auth.
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'tahun_ajaran' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'buka' => ['required', 'boolean'],
            'dibuka_pada' => ['required', 'date'],
            'ditutup_pada' => ['required', 'date', 'after_or_equal:dibuka_pada'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $tahun = $this->input('tahun_ajaran');

        if (is_string($tahun)) {
            // Terima "2027 / 2028" atau "2027-2028" lalu rapikan jadi "2027/2028".
            $this->merge([
                'tahun_ajaran' => preg_replace('/\s*[\/\-]\s*/', '/', trim($tahun)),
            ]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $tahun = (string) $this->input('tahun_ajaran');

            if (! preg_match('/^(\d{4})\/(\d{4})$/', $tahun, $cocok)) {
                return;
            }

            if ((int) $cocok[2] !== (int) $cocok[1] + 1) {
                $validator->errors()->add(
                    'tahun_ajaran',
                    'Tahun kedua harus satu tahun setelah tahun pertama, misalnya '.$cocok[1].'/'.($cocok[1] + 1).'.',
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'tahun_ajaran' => 'tahun ajaran',
            'buka' => 'status pendaftaran',
            'dibuka_pada' => 'tanggal dibuka',
            'ditutup_pada' => 'tanggal ditutup',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tahun_ajaran.regex' => 'Format tahun ajaran harus seperti 2027/2028.',
            'ditutup_pada.after_or_equal' => 'Tanggal ditutup tidak boleh lebih awal dari tanggal dibuka.',
        ];
    }
}
