<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PasienRequest extends FormRequest
{
    // Hak akses sudah dijaga middleware auth + role di routes.
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama'       => ['required', 'string', 'max:255'],
            'alamat'     => ['required', 'string', 'max:1000'],
            'no_telepon' => ['required', 'string', 'regex:/^[0-9+\-\s()]{8,20}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required'       => 'Nama pasien wajib diisi.',
            'alamat.required'     => 'Alamat wajib diisi.',
            'no_telepon.required' => 'No. telepon wajib diisi.',
            'no_telepon.regex'    => 'No. telepon tidak valid (8-20 karakter, hanya angka, +, -, spasi, atau tanda kurung).',
        ];
    }
}