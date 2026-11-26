<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\PageResource;
use App\Models\Page;
use App\Queries\PageQuery;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Published pages. Concepts, closed pages and pages outside their publish dates are never returned.
 */
#[Group('Pages')]
class PagesController extends ApiController
{
    /**
     * List pages.
     */
    #[QueryParameter('filter[search]', description: 'Search in the title and slug.', type: 'string')]
    #[QueryParameter('filter[title]', description: 'Part of the title.', type: 'string')]
    #[QueryParameter('filter[slug]', description: 'The exact slug.', type: 'string')]
    #[QueryParameter('sort', description: 'One of seqnr, title, slug, published_at, created_at, updated_at. Prefix with - for descending.', type: 'string', default: 'seqnr')]
    #[QueryParameter('include', description: 'Add "bricks" to include the bricks of every page.', type: 'string')]
    #[QueryParameter('per_page', description: 'Pages per page, at most 100.', type: 'int', default: 15)]
    public function index(Request $request): AnonymousResourceCollection
    {
        $pages = PageQuery::for(Page::query()->published())
            ->paginate($this->perPage($request))
            ->withQueryString();

        return PageResource::collection($pages);
    }

    /**
     * Get a page with its bricks.
     */
    public function show(string $slug): PageResource
    {
        $page = Page::query()->published()->where('slug', $slug)->firstOrFail();

        return new PageResource($page->load('bricks'));
    }
}
