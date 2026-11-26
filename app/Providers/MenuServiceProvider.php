<?php

namespace App\Providers;

use App\Features\Blogs;
use App\Features\Forms;
use App\Features\SiteMenu;
use App\Menu\MenuLink;
use App\Models\Blog;
use App\Models\Form;
use App\Models\Menu as MenuModel;
use App\Models\Page;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;
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
                ->addIf(Feature::active(Blogs::class) && Gate::allows('viewAny', Blog::class), MenuLink::toRoute('admin.blogs.index', __('Blogs'))->icon('newspaper'))
                ->addIf(Feature::active(Forms::class) && Gate::allows('viewAny', Form::class), MenuLink::toRoute('admin.forms.index', __('Forms'))->icon('queue-list'))
                ->addIf(Feature::active(SiteMenu::class) && Gate::allows('viewAny', MenuModel::class), MenuLink::toRoute('admin.menus.index', __('Menus'))->icon('bars-3'))
                ->addIf(Gate::allows('viewAny', Media::class), MenuLink::toRoute('admin.media.index', __('Media'))->icon('photo'))
                ->addIf(Gate::allows('viewAny', User::class), MenuLink::toRoute('admin.users.index', __('Users'))->icon('users'))
                ->addIf(Gate::allows('manageSettings'), MenuLink::toRoute('admin.settings', __('Settings'))->icon('cog-6-tooth'))
                ->setActiveFromRequest();
        });

        /*
         * The website menus come from the menu builder. The header also links to the admin
         * for those who may see it.
         */
        Menu::macro('main', function (): Menu {
            return Menu::website(MenuModel::MainHandle)
                ->addIf(Gate::allows('accessAdmin'), MenuLink::toRoute('admin.dashboard', __('Admin'))->icon('shield-check'));
        });

        Menu::macro('footer', fn (): Menu => Menu::website(MenuModel::FooterHandle));

        Menu::macro('website', function (string $handle): Menu {
            $menu = Menu::new();

            if (Feature::inactive(SiteMenu::class)) {
                return $menu;
            }

            foreach (MenuModel::visibleItems($handle) as $item) {
                $menu->add(MenuLink::to($item->url(), $item->label)->exact());
            }

            return $menu->setActiveFromRequest();
        });
    }
}
