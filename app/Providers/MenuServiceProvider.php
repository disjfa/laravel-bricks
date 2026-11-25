<?php

namespace App\Providers;

use App\Menu\MenuLink;
use App\Models\Page;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Menu\Laravel\Menu;

class MenuServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Menu::macro('admin', function (): Menu {
            return Menu::new()
                ->add(MenuLink::toRoute('admin.dashboard', __('Dashboard'))->icon('home')->exact())
                ->addIf(Gate::allows('viewAny', Page::class), MenuLink::toRoute('admin.pages.index', __('Pages'))->icon('document-text'))
                ->addIf(Gate::allows('viewAny', Media::class), MenuLink::toRoute('admin.media.index', __('Media'))->icon('photo'))
                ->addIf(Gate::allows('viewAny', User::class), MenuLink::toRoute('admin.users.index', __('Users'))->icon('users'))
                ->setActiveFromRequest();
        });

        Menu::macro('main', function (): Menu {
            return Menu::new()
                ->add(MenuLink::toRoute('home', __('Home'))->exact())
                ->addIf(Gate::allows('accessAdmin'), MenuLink::toRoute('admin.dashboard', __('Admin')))
                ->setActiveFromRequest();
        });
    }
}
