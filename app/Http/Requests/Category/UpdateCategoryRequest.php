<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $cat = $this->route('category');
    
    
    
        return [
            'name' => ['nullable', 'string', 'max:255', 'unique:categories,name,' . $cat->id],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}

