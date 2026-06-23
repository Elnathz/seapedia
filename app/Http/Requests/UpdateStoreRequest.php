<?php

namespace App\Http\Requests;

use App\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoreRequest extends FormRequest
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
        /** @var Store $store */
        $store = $this->route('store');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('stores', 'name')->ignore($store->id)],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
