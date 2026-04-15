<?php

namespace App\Http\Requests\Loan;

use App\Models\ToolUnit;
use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // nanti bisa pakai policy
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $user = $this->user();
            if (!$user) return;

            // 1. user restriction
            if ($user->is_restricted) {
                $validator->errors()->add(
                    'user',
                    'Anda sedang dibatasi dan tidak dapat mengajukan peminjaman.'
                );
            }

            // 2. guard biar tidak query kalau kosong
            $unitCode = $this->unit_code;
            if (!$unitCode) return;

            $unit = ToolUnit::where('code', $unitCode)->first();

            if ($unit && $unit->status !== 'available') {
                $validator->errors()->add(
                    'unit_code',
                    'Unit tidak tersedia.'
                );
            }
        });
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
