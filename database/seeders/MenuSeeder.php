<?php

namespace Database\Seeders;

use App\Enums\MenuItemType;
use App\Models\Menu;
use App\Models\Page;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menu = Menu::query()->firstOrCreate(
            ['handle' => Menu::MainHandle],
            ['name' => 'Main menu'],
        );

        if ($menu->items()->exists()) {
            return;
        }

        $pages = Page::query()->whereIn('slug', ['home', 'about', 'blog', 'contact'])->get()->keyBy('slug');

        foreach (['home' => 'Home', 'about' => 'About', 'blog' => 'Blog', 'contact' => 'Contact'] as $slug => $label) {
            if (! $pages->has($slug)) {
                continue;
            }

            $menu->items()->create([
                'label' => $label,
                'type' => MenuItemType::Page,
                'page_id' => $pages[$slug]->id,
                'position' => $menu->items()->count(),
            ]);
        }
    }
}
