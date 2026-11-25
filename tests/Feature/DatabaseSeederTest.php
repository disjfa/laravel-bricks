<?php

use App\Enums\UserRole;
use App\Models\Page;
use App\Models\User;

it('seeds an admin, regular users and published demo pages', function () {
    $this->seed();

    expect(User::firstWhere('email', 'admin@example.com')->hasRole(UserRole::Admin))->toBeTrue()
        ->and(User::firstWhere('email', 'test@example.com')->hasRole(UserRole::User))->toBeTrue()
        ->and(User::role(UserRole::User)->count())->toBe(21)
        ->and(Page::published()->pluck('slug')->sort()->values()->all())->toBe(['about', 'home']);
});
