<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppReviewRequest extends FormRequest
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
        return [
            'reviewer_name' => ['required', 'string', 'max:80'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'role' => ['nullable', 'string', 'max:50'],
            'comment' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reviewer_name.max' => 'Nama maksimal 80 karakter.',
            'rating.between' => 'Rating harus antara 1 sampai 5.',
            'role.max' => 'Role maksimal 50 karakter.',
            'comment.max' => 'Komentar maksimal 1000 karakter.',
        ];
    }
}
