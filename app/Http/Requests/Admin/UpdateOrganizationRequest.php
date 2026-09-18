<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('organizations.manage') === true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $organization = $this->route('organization');

        return [
            'name' => ['required', 'string', 'max:160'],
            'slug' => [
                'required',
                'string',
                'alpha_dash',
                'max:100',
                Rule::unique('organizations', 'slug')->ignore($organization),
            ],
            'status' => ['required', Rule::in(['active', 'suspended', 'revoked'])],
        ];
    }
}
