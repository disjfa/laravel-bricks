<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\MenuResource;
use App\Models\Menu;
use App\Queries\MenuQuery;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * The menus of the website, while the Site menu feature is turned on. Items that link to something
 * that is not published are left out.
 */
#[Group('Menus')]
class MenusController extends ApiController
{
    /**
     * List menus.
     */
    #[QueryParameter('filter[search]', description: 'Search in the name and handle.', type: 'string')]
    #[QueryParameter('filter[handle]', description: 'The exact handle, like "main" or "footer".', type: 'string')]
    #[QueryParameter('sort', description: 'One of name, handle, created_at. Prefix with - for descending.', type: 'string', default: 'name')]
    #[QueryParameter('include', description: 'Add "items" to include the items of every menu.', type: 'string')]
    #[QueryParameter('per_page', description: 'Menus per page, at most 100.', type: 'int', default: 15)]
    public function index(Request $request): AnonymousResourceCollection
    {
        $menus = MenuQuery::for(Menu::query())
            ->paginate($this->perPage($request))
            ->withQueryString();

        return MenuResource::collection($menus);
    }

    /**
     * Get a menu with its items.
     */
    public function show(string $handle): MenuResource
    {
        $menu = Menu::query()->where('handle', $handle)->firstOrFail();

        return new MenuResource($menu->load('items'));
    }
}
