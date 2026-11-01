<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TimerRequest extends FormRequest
{
    // Hak akses dijaga middleware auth + role:admin di routes.
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama' => trim((string) $this->input('nama')),
        ]);
    }

    public function rules(): array
    {
        return [
            'nama' => [
                'required', 'string', 'max:100',
                // Saat edit, nama milik timer itu sendiri diabaikan dari cek unik.
                Rule::unique('timers', 'nama')->ignore($this->route('timer')),
            ],
            'durasi_menit' => ['required', 'integer', 'min:1', 'max:600'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama timer wajib diisi.',
            'nama.max' => 'Nama timer maksimal 100 karakter.',
            'nama.unique' => 'Nama timer sudah dipakai.',
            'durasi_menit.required' => 'Durasi wajib diisi.',
            'durasi_menit.integer' => 'Durasi harus berupa angka bulat (menit).',
            'durasi_menit.min' => 'Durasi minimal 1 menit.',
            'durasi_menit.max' => 'Durasi maksimal 600 menit (10 jam).',
        ];
    }
}
