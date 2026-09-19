<?php

namespace App\Domain\Applications\Services;

use App\Models\Identity\Application;
use App\Models\User;

final class ApplicationAccessEvaluator
{
    public function canLaunch(User $user, Application $application): bool
    {
        if (! $user->isAccountActive()
            || $application->status !== 'active'
            || $application->organization?->status !== 'active'
        ) {
            return false;
        }

        return $application->userAccess()
            ->where('user_id', $user->getKey())
            ->where('status', 'active')
            ->exists();
    }
}
