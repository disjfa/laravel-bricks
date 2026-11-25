<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function impersonate(User $user, User $target): bool
    {
        if ($user->is($target)) {
            return false;
        }

        if (! $target->canBeImpersonated()) {
            return false;
        }

        return $user->canImpersonate();
    }
}
