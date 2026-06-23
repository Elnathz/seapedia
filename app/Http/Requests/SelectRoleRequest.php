<?php

namespace App\Http\Requests;

use App\Enums\RoleName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SelectRoleRequest extends FormRequest
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
            'role' => [
                'required',
                'string',
                Rule::in(array_column(RoleName::cases(), 'value')),
                function (string $attribute, string $value, callable $fail): void {
                    if (! $this->user()->hasRole(RoleName::from($value))) {
                        $fail('You do not own this role.');
                    }
                },
            ],
        ];
    }

    public function role(): RoleName
    {
        return RoleName::from($this->validated('role'));
    }
}
