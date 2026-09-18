<?php

namespace App\Http\Requests\Admin;

use App\Domain\Applications\Services\ScopeNameValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScopeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('scopes.manage') === true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $scope = $this->route('scope');

        return [
            'name' => ['required', 'string', 'max:128', Rule::unique('scopes', 'name')->ignore($scope), function (string $attribute, mixed $value, \Closure $fail): void {
                try {
                    app(ScopeNameValidator::class)->validate($value);
                } catch (\InvalidArgumentException) {
                    $fail('The scope name is invalid.');
                }
            }],
            'description' => ['nullable', 'string', 'max:2000'],
            'risk_level' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'status' => ['required', Rule::in(['active', 'revoked'])],
        ];
    }
}
