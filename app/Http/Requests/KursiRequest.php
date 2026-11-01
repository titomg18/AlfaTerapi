<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KursiRequest extends FormRequest
{
    // Hak akses dijaga middleware auth + role:admin di routes.
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'no_kursi' => trim((string) $this->input('no_kursi')),
        ]);
    }

    public function rules(): array
    {
        return [
            'no_kursi' => [
                'required', 'string', 'max:20',
                // Saat edit, nomor milik kursi itu sendiri diabaikan dari cek unik.
                Rule::unique('kursis', 'no_kursi')->ignore($this->route('kursi')),
            ],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'no_kursi.required' => 'No. kursi wajib diisi.',
            'no_kursi.max' => 'No. kursi maksimal 20 karakter.',
            'no_kursi.unique' => 'No. kursi sudah terdaftar.',
            'keterangan.max' => 'Keterangan maksimal 255 karakter.',
        ];
    }
}
