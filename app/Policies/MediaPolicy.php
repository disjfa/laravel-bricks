<?php

namespace App\Policies;

use App\Models\User;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Media $media): bool
    {
        return $user->isAdmin();
    }
}
