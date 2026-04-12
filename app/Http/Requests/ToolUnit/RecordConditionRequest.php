<?php

namespace App\Http\Requests\ToolUnit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordConditionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'condition' => [
                'required',
                Rule::in(['good', 'broken', 'maintenance']),
            ],
            'notes'     => 'nullable|string|max:1000',
            'return_id' => 'nullable|integer|exists:returns,id',
        ];
    }

    public function messages(): array
    {
        return [
            'condition.required' => 'Kondisi harus diisi.',
            'condition.in'       => 'Kondisi tidak valid. Gunakan: good, broken, maintenance.',
            'notes.string'       => 'Notes harus berupa teks.',
            'notes.max'          => 'Notes maksimal 1000 karakter.',
            'return_id.integer'  => 'Return ID harus berupa angka.',
            'return_id.exists'   => 'Return ID tidak ditemukan.',
        ];
    }
}
