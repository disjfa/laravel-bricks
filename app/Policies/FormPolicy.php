<?php

namespace App\Policies;

use App\Models\Form;
use App\Models\User;

class FormPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Form $form): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Form $form): bool
    {
        return $user->isAdmin();
    }
}
