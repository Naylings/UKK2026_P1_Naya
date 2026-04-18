<?php

namespace App\Http\Requests\Appeal;

use App\Models\Appeal;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreAppealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan appeal wajib diisi.',
            'reason.max' => 'Alasan tidak boleh lebih dari 1000 karakter.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            if ($user->is_restricted) {
                $validator->errors()->add('user', 'Akun Anda sedang dibatasi.');
            }

            if (Appeal::where('user_id', $user->id)->where('status', 'pending')->exists()) {
                $validator->errors()->add('appeal', 'Anda sudah memiliki appeal pending.');
            }
        });
    }
}
?>

