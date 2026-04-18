<?php

namespace App\Http\Requests\Appeal;

use Illuminate\Foundation\Http\FormRequest;

class ReviewAppealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return request()->user()->role === 'Admin';
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:approved,rejected'],
            'credit_changed' => ['required_if:status,approved', 'nullable', 'integer', 'min:-1000', 'max:1000'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status keputusan wajib dipilih.',
            'status.in' => 'Status harus approved atau rejected.',
            'credit_changed.required_if' => 'Perubahan credit wajib diisi untuk approved.',
            'credit_changed.integer' => 'Perubahan credit harus angka bulat.',
            'credit_changed.between' => 'Perubahan credit antara -1000 hingga 1000.',
        ];
    }
}
?>

