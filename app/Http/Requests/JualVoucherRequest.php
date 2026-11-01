<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JualVoucherRequest extends FormRequest
{
    // Hak akses dijaga middleware auth + role di routes.
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pasien_id' => ['required', 'integer', 'exists:pasiens,id'],
            'timer_id'  => ['required', 'integer', 'exists:timers,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'pasien_id.required' => 'Pilih pasien terlebih dahulu.',
            'pasien_id.exists'   => 'Pasien tidak ditemukan.',
            'timer_id.required'  => 'Pilih timer terlebih dahulu.',
            'timer_id.exists'    => 'Timer tidak ditemukan.',
        ];
    }
}