<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserCreditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'credit' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'credit.required' => 'Credit score wajib diisi.',
            'credit.integer'  => 'Credit score harus angka.',
            'credit.min'      => 'Credit score minimal 0.',
            'credit.max'      => 'Credit score maksimal 100.',
        ];
    }
}
