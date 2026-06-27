<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category')->id;

        return [
            'name' => ['required', 'string', 'min:2', 'max:80'],
            // Parent must be a root and cannot be the category itself, keeping
            // the tree at two levels and free of cycles.
            'parent_id' => [
                'nullable',
                'integer',
                Rule::notIn([$categoryId]),
                Rule::exists('categories', 'id')->whereNull('parent_id'),
            ],
            'icon' => ['nullable', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'parent_id.not_in' => 'Kategori tidak bisa menjadi induk dirinya sendiri.',
            'parent_id.exists' => 'Kategori induk tidak valid.',
        ];
    }
}
