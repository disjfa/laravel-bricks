<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\BlogResource;
use App\Models\Blog;
use App\Queries\BlogQuery;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Published blogs, while the Blogs feature is turned on.
 */
#[Group('Blogs')]
class BlogsController extends ApiController
{
    /**
     * List blogs.
     */
    #[QueryParameter('filter[search]', description: 'Search in the title and slug.', type: 'string')]
    #[QueryParameter('filter[title]', description: 'Part of the title.', type: 'string')]
    #[QueryParameter('filter[slug]', description: 'The exact slug.', type: 'string')]
    #[QueryParameter('sort', description: 'One of published_at, seqnr, title, slug, created_at, updated_at. Prefix with - for descending.', type: 'string', default: '-published_at')]
    #[QueryParameter('include', description: 'Add "bricks" to include the bricks of every blog.', type: 'string')]
    #[QueryParameter('per_page', description: 'Blogs per page, at most 100.', type: 'int', default: 15)]
    public function index(Request $request): AnonymousResourceCollection
    {
        $blogs = BlogQuery::for(Blog::query()->published())
            ->paginate($this->perPage($request))
            ->withQueryString();

        return BlogResource::collection($blogs);
    }

    /**
     * Get a blog with its bricks.
     */
    public function show(string $slug): BlogResource
    {
        $blog = Blog::query()->published()->where('slug', $slug)->firstOrFail();

        return new BlogResource($blog->load('bricks'));
    }
}
