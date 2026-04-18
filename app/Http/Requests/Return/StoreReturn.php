<?php

namespace App\Http\Requests\Return;

use App\Exceptions\ReturnException;
use Illuminate\Foundation\Http\FormRequest;

class StoreReturn extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proof'        => ['nullable', 'file', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'proof.file'            => 'Bukti harus berupa file.',
            'proof.image'           => 'Bukti harus berupa gambar.',
            'proof.max'             => 'Ukuran maksimal file 2MB.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $user = $this->user();
            if (!$user) return;

if ($user->is_restricted && !$this->route('loanId')) {
                $validator->errors()->add(
                    'user',
                    'Anda tidak memiliki akses aktif untuk melakukan pengembalian.'
                );
            }
        });
    }
}