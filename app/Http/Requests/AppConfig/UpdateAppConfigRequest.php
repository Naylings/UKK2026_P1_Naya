<?php

namespace App\Http\Requests\AppConfig;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'late_point'   => ['required', 'integer', 'min:0'],
            'broken_point' => ['required', 'integer', 'min:0'],
            'lost_point'   => ['required', 'integer', 'min:0'],
            'late_fine'    => ['required', 'integer', 'min:0', 'max:100'],
            'broken_fine'  => ['required', 'integer', 'min:0', 'max:100'],
            'lost_fine'    => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Nama aplikasi/instansi wajib diisi.',
            'late_point.required'   => 'Poin keterlambatan wajib diisi.',
            'broken_point.required' => 'Poin kerusakan wajib diisi.',
            'lost_point.required'   => 'Poin kehilangan wajib diisi.',
            'late_fine.required'    => 'Denda keterlambatan (%) wajib diisi.',
            'broken_fine.required'  => 'Denda kerusakan (%) wajib diisi.',
            'lost_fine.required'    => 'Denda kehilangan (%) wajib diisi.',
        ];
    }
}
