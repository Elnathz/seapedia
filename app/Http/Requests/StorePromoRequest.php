<?php

namespace App\Http\Requests;

use App\Enums\DiscountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StorePromoRequest extends FormRequest
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
            'code' => ['required', 'string', 'max:32', 'unique:promos,code'],
            'type' => ['required', new Enum(DiscountType::class)],
            'value' => [
                'required', 'integer', 'min:1',
                function (string $attribute, mixed $value, callable $fail): void {
                    if ($this->input('type') === DiscountType::Percentage->value && $value > 100) {
                        $fail(__('Percentage value cannot exceed 100.'));
                    }
                },
            ],
            'max_discount' => ['nullable', 'integer', 'min:0'],
            'min_spend' => ['nullable', 'integer', 'min:0'],
            'expiry_date' => ['required', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
