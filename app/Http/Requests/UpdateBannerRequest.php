<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'placement' => ['required', 'in:main,side'],
            'title' => ['required', 'string', 'max:120'],
            'subtitle' => ['nullable', 'string', 'max:200'],
            'badge_label' => ['nullable', 'string', 'max:40'],
            'cta_label' => ['nullable', 'string', 'max:40'],
            'cta_url' => [
                'nullable',
                'string',
                'max:200',
                function ($attribute, $value, $fail) {
                    if ($value !== null && $value !== '') {
                        if (str_starts_with($value, '/')) {
                            if (!preg_match('/^\/(catalog|stores)($|[\/?#])/', $value)) {
                                $fail('Tautan internal harus diawali dengan /catalog atau /stores.');
                            }
                        } else {
                            if (!preg_match('/^https?:\/\//', $value)) {
                                $fail('Tautan eksternal wajib diawali dengan http:// atau https://');
                            }
                        }
                    }
                }
            ],
            'sort_order' => [
                'required',
                'integer',
                'min:0',
                'max:1000',
                Rule::unique('banners')->where('placement', $this->placement)->ignore($this->route('banner'))
            ],
            'is_active' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'image.uploaded' => 'Gambar gagal diunggah. Pastikan ukuran file tidak melebihi batas server (Maks 2MB).',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
            'sort_order.unique' => 'Urutan tersebut sudah digunakan untuk posisi ini.',
        ];
    }

    public function validationData()
    {
        $data = $this->all();

        $file = $this->file('image');
        // If the user didn't upload a file at all (error 4), remove it from validation
        // so that the 'nullable' rule passes properly.
        if (!$file || $file->getError() === \UPLOAD_ERR_NO_FILE) {
            unset($data['image']);
        }

        return $data;
    }
}
