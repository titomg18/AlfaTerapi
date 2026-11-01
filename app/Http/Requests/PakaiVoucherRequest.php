<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PakaiVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kursi_id' => ['required', 'integer', 'exists:kursis,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'kursi_id.required' => 'Pilih kursi terlebih dahulu.',
            'kursi_id.exists' => 'Kursi tidak ditemukan.',
        ];
    }
}
