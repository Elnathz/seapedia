<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdvanceClockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Thin by design — advancing the clock takes no input, it always
     * moves exactly one tick (§5.7).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
