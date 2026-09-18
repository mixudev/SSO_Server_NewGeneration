<?php

namespace App\Http\Requests\Admin;

use App\Domain\Applications\Services\ScopeNameValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScopeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('scopes.manage') === true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:128', function (string $attribute, mixed $value, \Closure $fail): void {
                try {
                    app(ScopeNameValidator::class)->validate($value);
                } catch (\InvalidArgumentException) {
                    $fail('The scope name is invalid.');
                }
            }],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', Rule::in(['standard', 'custom'])],
            'risk_level' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
        ];
    }
}
