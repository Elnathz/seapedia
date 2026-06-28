<?php

namespace App\Http\Requests;

use App\Enums\DiscountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreVoucherRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:50', 'unique:vouchers,code'],
            'type' => ['required', new Enum(DiscountType::class)],
            'value' => [
                'required', 'integer', 'min:1',
                function (string $attribute, mixed $value, callable $fail): void {
                    if ($this->input('type') === DiscountType::Percentage->value && $value > 100) {
                        $fail('Persentase tidak boleh lebih dari 100%.');
                    }
                },
            ],
            'max_discount' => ['nullable', 'integer', 'min:0'],
            'min_spend' => ['nullable', 'integer', 'min:0'],
            'expiry_date' => ['required', 'date', 'after:today'],
            'usage_limit' => ['required', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'Kode voucher sudah digunakan.',
            'expiry_date.after' => 'Tanggal kedaluwarsa harus setelah hari ini.',
            'usage_limit.min' => 'Batas penggunaan minimal 1.',
        ];
    }
}
