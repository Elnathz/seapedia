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
     * `days` is how many one-day ticks (§5.7) to jump — the admin UI offers a
     * +1 and a +3 shortcut. Optional; defaults to a single tick when absent.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'days' => ['nullable', 'integer', 'min:1', 'max:30'],
        ];
    }
}
