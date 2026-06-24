<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TogglePromoActiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Thin by design — the promo is identified by the route binding, not
     * the request body.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
