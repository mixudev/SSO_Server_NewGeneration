<?php

namespace App\Http\Requests\Admin;

use App\Domain\Applications\Services\RedirectUriValidator;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('applications.create') === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'organization_id' => ['required', 'string', 'exists:organizations,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:255'],
            'protocol_mode' => ['required', 'in:oauth2,oidc'],
            'client_type' => ['required', 'in:confidential_web,public_spa,native'],
            'description' => ['nullable', 'string', 'max:5000'],
            'redirect_uris' => ['required', 'array', 'min:1', 'max:20'],
            'redirect_uris.*' => ['required', 'string', 'max:2048'],
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $redirectUriValidator = app(RedirectUriValidator::class);
            $canonicalUris = [];

            foreach ($this->input('redirect_uris', []) as $index => $uri) {
                try {
                    $canonicalUri = $redirectUriValidator->canonicalize($uri);
                } catch (\InvalidArgumentException) {
                    $validator->errors()->add("redirect_uris.{$index}", 'Redirect URI is invalid.');

                    continue;
                }

                if (in_array($canonicalUri, $canonicalUris, true)) {
                    $validator->errors()->add("redirect_uris.{$index}", 'Redirect URI must be unique.');

                    continue;
                }

                $canonicalUris[] = $canonicalUri;
            }

            $this->merge(['canonical_redirect_uris' => $canonicalUris]);
        });
    }
}
