<?php

namespace App\Http\Requests\Return;

use Illuminate\Foundation\Http\FormRequest;

class ReviewReturn extends FormRequest
{
    public function authorize(): bool
    {
        return true; // nanti bisa policy: employee only
    }

    public function rules(): array
    {
        return [
            // kondisi final unit
            'condition_id' => ['required', 'exists:unit_conditions,id'],

            // optional violation
            'violation_type' => ['nullable', 'in:late,damaged,lost,other'],

            'total_score' => ['nullable', 'integer', 'min:0'],

            'fine' => ['nullable', 'numeric', 'min:0'],

            'description' => ['nullable', 'string', 'max:2000'],

            'condition' => ['required', 'in:good,broken,maintenance'],

            'condition_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'condition_id.required' => 'Kondisi wajib diisi.',
            'condition.required'    => 'Status kondisi wajib diisi.',
            'condition.in'          => 'Status kondisi tidak valid.',

            'violation_type.in'     => 'Tipe pelanggaran tidak valid.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $hasViolation = $this->violation_type !== null;

            // kalau violation ada → wajib isi detail minimal
            if ($hasViolation) {

                if ($this->total_score === null) {
                    $validator->errors()->add(
                        'total_score',
                        'Total score wajib diisi jika ada pelanggaran.'
                    );
                }

                if ($this->description === null) {
                    $validator->errors()->add(
                        'description',
                        'Deskripsi pelanggaran wajib diisi.'
                    );
                }
            }

            // guard: jangan kirim violation tapi data kosong
            if ($this->violation_type && !$this->description) {
                $validator->errors()->add(
                    'description',
                    'Deskripsi wajib diisi untuk pelanggaran.'
                );
            }
        });
    }
}