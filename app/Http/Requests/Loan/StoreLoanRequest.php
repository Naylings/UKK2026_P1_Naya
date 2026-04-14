<?php

namespace App\Http\Requests\Loan;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // nanti bisa pakai policy
    }

    public function rules(): array
    {
        return [
            'tool_id' => ['required', 'exists:tools,id'],
            'unit_code' => ['required', 'exists:tool_units,code'],
            'loan_date' => ['required', 'date', 'after_or_equal:today'],
            'due_date' => ['required', 'date', 'after_or_equal:loan_date'],
            'purpose' => ['required', 'string', 'max:1000'],
        ];
    }
    public function messages(): array
    {
        return [
            'tool_id.required' => 'Tool wajib dipilih.',
            'unit_code.required' => 'Unit wajib dipilih.',
            'loan_date.after_or_equal' => 'Tanggal pinjam tidak boleh di masa lalu.',
            'due_date.after_or_equal' => 'Tanggal kembali harus setelah tanggal pinjam.',
        ];
    }
}
