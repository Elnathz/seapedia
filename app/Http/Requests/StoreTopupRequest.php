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
                'min:'.config('payment.topup.min_amount', 10000),
                'max:10000000', // Rp 10.000.000 per transaction (§balance-cap)
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.min' => 'Minimal top up Rp'.number_format(config('payment.topup.min_amount', 10000), 0, ',', '.').'.',
            'amount.max' => 'Maksimal top up per transaksi adalah Rp10.000.000.',
        ];
    }
}
