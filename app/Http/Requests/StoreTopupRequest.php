<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTopupRequest extends FormRequest
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
            'amount' => [
                'required',
                'integer',
                'min:'.config('payment.topup.min_amount', 5000),
                'max:'.config('payment.topup.max_amount', 100000000),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.min' => 'Minimal top up Rp'.number_format(config('payment.topup.min_amount', 5000), 0, ',', '.').'.',
            'amount.max' => 'Maksimal top up per transaksi adalah Rp'.number_format(config('payment.topup.max_amount', 100000000), 0, ',', '.').'.',
        ];
    }
}
