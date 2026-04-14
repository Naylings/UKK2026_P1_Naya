<?php

namespace App\Http\Requests\ToolUnit;

use Illuminate\Foundation\Http\FormRequest;

class AvailableUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
        'tool_id'   => 'required|integer|exists:tools,id',
        'loan_date' => 'required|date|after_or_equal:today',
        'due_date'  => 'required|date|after_or_equal:loan_date',
        ];
    }

    public function messages(): array
    {
        return [
            'tool_id.required' => 'Tool ID wajib diisi.',
            'tool_id.exists' => 'Tool tidak ditemukan.',
            'loan_date.required' => 'Tanggal loan date wajib diisi.',
            'loan_date.date' => 'Format tanggal tidak valid.',
            'loan_date.after_or_equal' => 'Loan date tidak boleh sebelum hari ini.',
            'due_date.required' => 'Tanggal due date wajib diisi.',
            'due_date.date' => 'Format tanggal tidak valid.',
            'due_date.after_or_equal' => 'Due date tidak boleh sebelum loan date.',
        ];
    }
}