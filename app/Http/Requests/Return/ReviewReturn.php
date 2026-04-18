<?php

namespace App\Http\Requests\Return;

use Illuminate\Foundation\Http\FormRequest;

class ReviewReturn extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'condition' => ['required', 'in:good,broken,maintenance'],
            'condition_notes' => ['nullable', 'string', 'max:1000'],

            'notes' => ['nullable', 'string', 'max:1000'],

            'violation_type' => ['nullable', 'in:late,damaged,lost,other'],
            'total_score' => ['nullable', 'integer', 'min:0'],
            'fine' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'condition.required'    => 'Status kondisi wajib diisi.',
            'condition.in'          => 'Status kondisi tidak valid.',
            'violation_type.in'     => 'Tipe pelanggaran tidak valid.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hasViolation = !empty($this->violation_type);

            if ($hasViolation) {
                if ($this->total_score === null) {
                    $validator->errors()->add('total_score', 'Total poin pelanggaran wajib ditentukan oleh petugas.');
                }
                if ($this->fine === null) {
                    $validator->errors()->add('fine', 'Nominal denda wajib ditentukan oleh petugas.');
                }
                if (!$this->description) {
                    $validator->errors()->add('description', 'Deskripsi masalah wajib diisi.');
                }
            }
        });
    }
}
