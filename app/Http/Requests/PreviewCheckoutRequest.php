<?php

namespace App\Http\Requests;

use App\Enums\DeliveryMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class PreviewCheckoutRequest extends FormRequest
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
            'delivery_method' => ['required', new Enum(DeliveryMethod::class)],
        ];
    }
}
