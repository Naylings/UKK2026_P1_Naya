<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // User fields
            'email'    => ['required', 'string', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role'     => ['required', 'string', Rule::in(['Admin', 'Employee', 'User'])],
            
            // UserDetail fields
            'nik'        => ['required', 'string', 'size:16', 'unique:user_details,nik'],
            'name'       => ['required', 'string', 'max:255'],
            'no_hp'      => ['required', 'string', 'max:15'],
            'address'    => ['required', 'string', 'max:500'],
            'birth_date' => ['required', 'date_format:Y-m-d', 'before:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'       => 'Email wajib diisi.',
            'email.email'          => 'Format email tidak valid.',
            'email.unique'         => 'Email sudah terdaftar.',
            'password.required'    => 'Password wajib diisi.',
            'password.min'         => 'Password minimal 6 karakter.',
            'role.in'              => 'Role harus: Admin, Employee, atau User.',
            
            'nik.required'         => 'NIK wajib diisi.',
            'nik.size'             => 'NIK harus 16 digit.',
            'nik.unique'           => 'NIK sudah terdaftar.',
            'name.required'        => 'Nama wajib diisi.',
            'name.max'             => 'Nama maksimal 255 karakter.',
            'no_hp.required'       => 'Nomor HP wajib diisi.',
            'no_hp.max'            => 'Nomor HP maksimal 15 karakter.',
            'address.required'     => 'Alamat wajib diisi.',
            'address.max'          => 'Alamat maksimal 500 karakter.',
            'birth_date.required'  => 'Tanggal lahir wajib diisi.',
            'birth_date.date_format' => 'Format tanggal harus YYYY-MM-DD.',
            'birth_date.before'    => 'Tanggal lahir harus di masa lalu.',
        ];
    }
}

