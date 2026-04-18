<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class ReportPreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $type = $this->route('type');
        $rules = [
            'start_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'tool_id' => ['nullable', 'integer', 'exists:tools,id'],
            'tool_name' => ['nullable', 'string', 'min:2'],
            'unit_code' => ['nullable', 'string', 'exists:tool_units,code'],
            'status' => ['nullable', 'string'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'search' => ['nullable', 'string', 'min:2'],
        ];

        if (in_array($type, ['loans', 'returns'])) {
            $rules['tool_id'][] = 'sometimes';
        }
        if ($type === 'inventory') {
            $rules['category_id'][] = 'sometimes';
        }
        if ($type === 'users') {
            $rules['status'][] = 'sometimes|in:Admin,Employee,User';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'start_date.required' => 'Tanggal mulai harus diisi.',
            'start_date.date' => 'Format tanggal mulai tidak valid.',
            'end_date.required' => 'Tanggal akhir harus diisi.',
            'end_date.date' => 'Format tanggal akhir tidak valid.',
            'end_date.after_or_equal' => 'Tanggal akhir harus lebih besar atau sama dengan tanggal mulai.',
        ];
    }
}
