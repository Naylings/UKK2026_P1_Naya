<?php

namespace App\Http\Requests\Tool;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreToolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has('bundle_components') && is_string($this->bundle_components)) {
            $decoded = json_decode($this->bundle_components, true);

            $this->merge([
                'bundle_components' => is_array($decoded) ? $decoded : null,
            ]);
        }
    }

    public function rules(): array
    {
        $isBundle = $this->input('item_type') === 'bundle';

        return [
            // Tool fields
            'category_id'       => ['required', 'exists:categories,id'],
            'name'              => ['required', 'string', 'max:255', 'unique:tools,name'],
            'item_type'         => ['required', 'string', Rule::in(['single', 'bundle', 'bundle_tool'])],
            'price'             => ['required', 'numeric', 'min:0'],
            'min_credit_score'  => ['required', 'integer', 'min:0', 'max:100'],
            'description'       => ['nullable', 'string', 'max:500'],
            'code_slug'         => ['required', 'string', 'max:15', 'unique:tools,code_slug'],
            'photo_path'        => ['nullable', 'string', 'max:255'],
            'photo'             => ['nullable', 'file', 'image', 'max:2048'],

            // Bundle components (only if bundle)
            'bundle_components' => ['nullable', 'array', Rule::requiredIf($isBundle)],
            'bundle_components.*.name' => ['required_with:bundle_components', 'string', 'max:255'],
            'bundle_components.*.price' => ['required_with:bundle_components', 'numeric', 'min:0'],
            'bundle_components.*.qty' => ['required_with:bundle_components', 'integer', 'min:1'],
            'bundle_components.*.description' => ['nullable', 'string', 'max:500'],
            'bundle_components.*.photo_path' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori wajib diisi.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'name.required' => 'Nama wajib diisi.',
            'name.string' => 'Nama harus berupa string.',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            'name.unique' => 'Alat dengan nama tersebut sudah ada.',
            'item_type.required' => 'Jenis item wajib diisi.',
            'item_type.string' => 'Jenis item harus berupa string.',
            'item_type.in' => 'Jenis item harus salah satu dari berikut: single, bundle, bundle_tool.',
            'price.required' => 'Harga wajib diisi.',
            'price.numeric' => 'Harga harus berupa angka.',
            'price.min' => 'Harga tidak boleh kurang dari 0.',
            'min_credit_score.required' => 'Skor kredit minimum wajib diisi.',
            'min_credit_score.integer' => 'Skor kredit minimum harus berupa bilangan bulat.',
            'min_credit_score.min' => 'Skor kredit minimum tidak boleh kurang dari 0.',
            'min_credit_score.max' => 'Skor kredit minimum tidak boleh lebih dari 100.',
            'description.string' => 'Deskripsi harus berupa string.',
            'description.max' => 'Deskripsi tidak boleh lebih dari 500 karakter.',
            'code_slug.required' => 'Slug kode wajib diisi.',
            'code_slug.string' => 'Slug kode harus berupa string.',
            'code_slug.max' => 'Slug kode tidak boleh lebih dari 15 karakter.',
            'code_slug.unique' => 'Alat dengan slug kode tersebut sudah ada.',
            'photo_path.string' => 'Path foto harus berupa string.',
            'photo_path.max' => 'Path foto tidak boleh lebih dari 255 karakter.',
            'photo.file' => 'Foto harus berupa file yang valid.',
            'photo.image' => 'Foto harus berupa gambar.',
            'photo.max' => 'Ukuran foto maksimal 2MB.',
            'bundle_components.required' => 'Komponen bundle wajib diisi untuk item bertipe bundle.',
            'bundle_components.array' => 'Komponen bundle harus berupa daftar.',
            'bundle_components.*.name.required_with' => 'Nama tool komponen bundle wajib diisi.',
            'bundle_components.*.name.string' => 'Nama tool komponen bundle harus berupa string.',
            'bundle_components.*.name.max' => 'Nama tool komponen bundle tidak boleh lebih dari 255 karakter.',
            'bundle_components.*.price.required_with' => 'Harga tool komponen bundle wajib diisi.',
            'bundle_components.*.price.numeric' => 'Harga tool komponen bundle harus berupa angka.',
            'bundle_components.*.price.min' => 'Harga tool komponen bundle tidak boleh kurang dari 0.',
            'bundle_components.*.qty.required_with' => 'Jumlah komponen bundle wajib diisi.',
            'bundle_components.*.qty.integer' => 'Jumlah komponen bundle harus berupa bilangan bulat.',
            'bundle_components.*.qty.min' => 'Jumlah komponen bundle minimal 1.',
            'bundle_components.*.description.string' => 'Deskripsi tool komponen bundle harus berupa string.',
            'bundle_components.*.description.max' => 'Deskripsi tool komponen bundle tidak boleh lebih dari 500 karakter.',
            'bundle_components.*.photo_path.string' => 'Path foto tool komponen bundle harus berupa string.',
            'bundle_components.*.photo_path.max' => 'Path foto tool komponen bundle tidak boleh lebih dari 255 karakter.',
        ];
    }
}
