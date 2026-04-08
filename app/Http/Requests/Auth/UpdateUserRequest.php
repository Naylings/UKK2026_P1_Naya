<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');
    
        $user->loadMissing('detail');
    
        $detailNik = $user->detail?->nik;
    
        return [
            'role' => ['nullable', 'string', Rule::in(['Admin', 'Employee', 'User'])],
    
            'nik' => [
                'sometimes',
                'nullable',
                'string',
                'size:16',
                Rule::unique('user_details', 'nik')->ignore($detailNik, 'nik'),
            ],
    
            'name' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:15'],
            'address' => ['nullable', 'string', 'max:500'],
            'birth_date' => ['nullable', 'date_format:Y-m-d', 'before:today'],
        ];
    }
}

