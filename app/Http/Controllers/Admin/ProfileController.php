<?php

namespace App\Http\Controllers\Admin;

use App\Infrastructure\Identity\ProfileSecuritySummary;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Vendor\LaravelAuthentication\Http\Controllers\PasskeyController;
use Vendor\LaravelAuthentication\Http\Controllers\PasswordResetController;
use Vendor\LaravelAuthentication\Http\Controllers\SessionController;
use Vendor\LaravelAuthentication\Http\Requests\ForgotPasswordRequest;
use Vendor\LaravelAuthentication\Services\Passkey\PasskeyService;
use Vendor\LaravelAuthentication\Services\Password\PasswordService;
use Vendor\LaravelAuthentication\Services\TwoFactor\TwoFactorService;

class ProfileController
{
    public function __construct(private readonly ProfileSecuritySummary $securitySummary) {}

    public function show(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('pages.admin.profile.show', [
            'user' => $user,
            'roles' => $user->getRoleNames(),
            'security' => $this->securitySummary->for($user, $request->session()->getId()),
        ]);
    }

    public function edit(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('pages.admin.profile.edit', [
            'user' => $user,
        ]);
    }

    public function security(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('pages.admin.profile.security', [
            'user' => $user,
            'security' => $this->securitySummary->for($user, $request->session()->getId()),
        ]);
    }

    public function avatarUpdate(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validate(['avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']]);

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->forceFill(['avatar_path' => $validated['avatar']->store('avatars', 'public')])->save();

        return to_route('admin.profile.show')->with('status', 'Profile photo updated successfully.');
    }

    public function avatarDestroy(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->forceFill(['avatar_path' => null])->save();
        }

        return to_route('admin.profile.show')->with('status', 'Profile photo removed.');
    }

    public function passwordUpdate(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        app(PasswordService::class)->updatePassword($user, $validated['password']);

        return to_route('admin.profile.security')->with('status', 'Password updated successfully.');
    }

    public function twoFactorSetup(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $setup = app(TwoFactorService::class)->setup($user);

        return view('pages.admin.profile.two-factor', [
            'setup' => $setup,
            'recoveryCodes' => $setup['recovery_codes'],
        ]);
    }

    public function twoFactorConfirm(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        abort_unless(app(TwoFactorService::class)->confirm($user, (string) $request->input('code')), 422, 'Invalid verification code.');

        return to_route('admin.profile.security')->with('status', 'Two-factor authentication enabled.');
    }

    public function passwordReset(Request $request): View
    {
        return view('pages.admin.profile.password-reset', [
            'email' => (string) $request->user()->email,
        ]);
    }

    public function passwordResetSend(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email:rfc', 'max:255']]);
        $packageRequest = ForgotPasswordRequest::createFrom($request);
        $packageRequest->setContainer(app())->setRedirector(app('redirect'));

        return app(PasswordResetController::class)->sendResetLinkEmail($packageRequest);
    }

    public function passkeyOptions(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json(app(PasskeyService::class)->generateCreationOptions($user)->toArray());
    }

    public function passkeyRegister(Request $request): JsonResponse
    {
        /** @var User $user */
        return app(PasskeyController::class)->register($request);
    }

    public function passkeyDestroy(Request $request, int|string $id): JsonResponse|RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        return app(PasskeyController::class)->destroy($request, $id);
    }

    public function sessionDestroy(Request $request, string $id): RedirectResponse|JsonResponse
    {
        return app(SessionController::class)->destroy($request, $id);
    }

    public function sessionsDestroyOthers(Request $request): RedirectResponse|JsonResponse
    {
        return app(SessionController::class)->destroyOthers($request);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email,'.$user->getKey()],
        ]);

        $user->fill($validated)->save();

        return to_route('admin.profile.show')->with('status', 'Profile updated successfully.');
    }
}
