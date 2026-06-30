<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price' => ['required_without:has_variants', 'nullable', 'integer', 'min:100', 'max:100000000'],
            'stock' => ['required_without:has_variants', 'nullable', 'integer', 'min:0', 'max:1000000'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'deleted_image_ids' => ['nullable', 'array'],
            'deleted_image_ids.*' => ['integer'],
            'has_variants' => ['required', 'boolean'],
            'variants' => ['required_if:has_variants,true', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.name' => ['required_if:has_variants,true', 'string', 'max:150'],
            'variants.*.price' => ['required_if:has_variants,true', 'integer', 'min:100', 'max:100000000'],
            'variants.*.stock' => ['required_if:has_variants,true', 'integer', 'min:0', 'max:1000000'],
            'variants.*.image_index' => ['nullable', 'integer', 'min:0', 'max:4'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Pilih kategori produk.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'price.min' => 'Harga minimal Rp100.',
            'images.*.uploaded' => 'Gambar gagal diunggah. Pastikan ukuran file tidak melebihi batas server (Maks 2MB).',
            'images.*.max' => 'Ukuran gambar maksimal 2MB.',
            'variants.*.price.min' => 'Harga varian minimal Rp100.',
        ];
    }

    public function validationData()
    {
        $data = $this->all();

        if (isset($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $key => $file) {
                if ($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                    if ($file->getError() === \UPLOAD_ERR_NO_FILE) {
                        unset($data['images'][$key]);
                    }
                }
            }
            if (empty($data['images'])) {
                unset($data['images']);
            }
        }

        return $data;
    }
}
