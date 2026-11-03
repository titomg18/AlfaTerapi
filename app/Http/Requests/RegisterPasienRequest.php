<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterPasienRequest extends FormRequest
{
    /**
     * Pendaftaran dibuka untuk umum (guest).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'min:2', 'max:255'],
            'no_telepon' => ['required', 'string', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'alamat' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'nama.min' => 'Nama minimal 2 karakter.',
            'no_telepon.required' => 'Nomor WhatsApp / telepon wajib diisi.',
            'no_telepon.regex' => 'Format nomor telepon tidak valid (8-20 digit angka).',
            'alamat.required' => 'Alamat lengkap tempat tinggal wajib diisi.',
        ];
    }
}
