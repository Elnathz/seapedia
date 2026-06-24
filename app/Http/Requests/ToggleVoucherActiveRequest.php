<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ToggleVoucherActiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Thin by design — the voucher is identified by the route binding, not
     * the request body.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
