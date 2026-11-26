<?php

use App\Enums\UserRole;
use App\Models\Blog;
use App\Models\Form;
use App\Models\Menu;
use App\Models\Page;
use App\Models\User;

it('seeds an admin, regular users, demo pages, blogs, a contact form and the main menu', function () {
    $this->seed();

    expect(User::firstWhere('email', 'admin@example.com')->hasRole(UserRole::Admin))->toBeTrue()
        ->and(User::firstWhere('email', 'test@example.com')->hasRole(UserRole::User))->toBeTrue()
        ->and(User::role(UserRole::User)->count())->toBe(21)
        ->and(Page::published()->pluck('slug')->sort()->values()->all())->toBe(['about', 'blog', 'contact', 'home'])
        ->and(Blog::published()->count())->toBe(8)
        ->and(Form::firstWhere('name', 'Contact')->fields)->toHaveCount(3)
        ->and(Menu::visibleItems(Menu::MainHandle)->pluck('label')->all())->toBe(['Home', 'About', 'Blog', 'Contact']);
});
