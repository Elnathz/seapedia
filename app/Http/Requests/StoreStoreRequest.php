<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoreRequest extends FormRequest
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
            'name' => ['required', 'string', 'min:3', 'max:100', 'unique:stores,name'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=200,min_height=200'],
            'description' => ['nullable', 'string', 'max:500'],
            'full_address' => ['nullable', 'string', 'max:500'],
            'province' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'village' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'origin_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'origin_longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'logo.image' => 'File harus berupa gambar.',
            'logo.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'logo.max' => 'Ukuran foto maksimal 2MB.',
            'logo.dimensions' => 'Resolusi foto minimal 200x200 piksel.',
        ];
    }
}
