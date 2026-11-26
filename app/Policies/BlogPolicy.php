<?php

namespace App\Policies;

use App\Models\Blog;
use App\Models\User;

class BlogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Published blogs are public, the others are only visible to admins.
     */
    public function view(?User $user, Blog $blog): bool
    {
        if ($blog->isPublished()) {
            return true;
        }

        return $user?->isAdmin() ?? false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Blog $blog): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Blog $blog): bool
    {
        return $user->isAdmin();
    }
}
