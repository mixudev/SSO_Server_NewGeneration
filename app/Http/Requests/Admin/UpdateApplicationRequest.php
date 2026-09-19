<?php

namespace App\Http\Requests\Admin;

use App\Domain\Applications\Services\RedirectUriValidator;
use App\Models\Identity\Application;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('applications.update') === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
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
            'logout_redirect_uris' => ['nullable', 'array', 'max:20'],
            'logout_redirect_uris.*' => ['nullable', 'string', 'max:2048'],
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $application = $this->route('application');
            if ($application instanceof Application && $application->credential()->exists()) {
                if ($this->input('protocol_mode') !== $application->protocol_mode) {
                    $validator->errors()->add('protocol_mode', 'Protocol mode cannot change after credentials are issued. Create a new application instead.');
                }

                if ($this->input('client_type') !== $application->client_type) {
                    $validator->errors()->add('client_type', 'Client type cannot change after credentials are issued. Create a new application instead.');
                }
            }

            $canonicalUris = [];
            $redirectUriValidator = app(RedirectUriValidator::class);

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

            $canonicalLogoutUris = [];
            foreach ($this->input('logout_redirect_uris', []) as $index => $uri) {
                if ($uri === null || $uri === '') {
                    continue;
                }

                try {
                    $canonicalUri = $redirectUriValidator->canonicalize($uri);
                } catch (\InvalidArgumentException) {
                    $validator->errors()->add("logout_redirect_uris.{$index}", 'Logout redirect URI is invalid.');

                    continue;
                }

                if (in_array($canonicalUri, $canonicalLogoutUris, true)) {
                    $validator->errors()->add("logout_redirect_uris.{$index}", 'Logout redirect URI must be unique.');

                    continue;
                }

                $canonicalLogoutUris[] = $canonicalUri;
            }

            $this->merge([
                'canonical_redirect_uris' => $canonicalUris,
                'canonical_logout_redirect_uris' => $canonicalLogoutUris,
            ]);
        });
    }
}
