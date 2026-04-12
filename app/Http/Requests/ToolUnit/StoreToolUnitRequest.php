<?php

namespace App\Http\Requests\ToolUnit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreToolUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'quantity' => $this->input('quantity', 1),
            'condition' => $this->input('condition', 'good'),
        ]);
    }

    public function rules(): array
    {
        return [
            'tool_id'     => 'required|integer|exists:tools,id',
            'quantity'    => 'required|integer|min:1|max:100',
            'notes'       => 'nullable|string|max:500',
            'condition'   => [
                'required',
                Rule::in(['good', 'broken', 'maintenance']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'tool_id.required'   => 'Tool ID harus diisi.',
            'tool_id.integer'    => 'Tool ID harus berupa angka.',
            'tool_id.exists'     => 'Tool tidak ditemukan.',
            'quantity.required'  => 'Quantity harus diisi.',
            'quantity.integer'   => 'Quantity harus berupa angka.',
            'quantity.min'       => 'Minimum quantity adalah 1.',
            'quantity.max'       => 'Maximum quantity adalah 100.',
            'notes.string'       => 'Notes harus berupa teks.',
            'notes.max'          => 'Notes maksimal 500 karakter.',
            'condition.required' => 'Kondisi harus diisi.',
            'condition.in'       => 'Kondisi tidak valid. Gunakan: good, broken, maintenance.',
        ];
    }
}
