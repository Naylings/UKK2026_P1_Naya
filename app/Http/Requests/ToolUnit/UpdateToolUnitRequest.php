<?php

namespace App\Http\Requests\ToolUnit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateToolUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in(['available', 'lent', 'nonactive']),
            ],
            'notes'  => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status harus diisi.',
            'status.in'       => 'Status tidak valid. Gunakan: available, lent, nonactive.',
            'notes.string'    => 'Notes harus berupa teks.',
            'notes.max'       => 'Notes maksimal 500 karakter.',
        ];
    }
}
