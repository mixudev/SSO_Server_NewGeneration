<?php

namespace App\Http\Requests\Admin;

use App\Domain\Applications\Services\ClaimKeyValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClaimRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('claims.manage') === true;
    }

    public function rules(): array
    {
        $claim = $this->route('claim');

        return [
            'key' => ['required', 'string', 'max:128', Rule::unique('claims', 'key')->ignore($claim), function (string $attribute, mixed $value, \Closure $fail): void {
                try {
                    app(ClaimKeyValidator::class)->validate($value);
                } catch (\InvalidArgumentException) {
                    $fail('The claim key is invalid.');
                }
            }],
            'description' => ['nullable', 'string', 'max:2000'],
            'source' => ['required', 'string', 'max:64', 'regex:/^[a-z][a-z0-9_.-]*$/'],
            'value_type' => ['required', Rule::in(['string', 'boolean', 'array', 'json'])],
            'sensitivity' => ['required', Rule::in(['public', 'personal', 'sensitive'])],
            'status' => ['required', Rule::in(['active', 'revoked'])],
        ];
    }
}
