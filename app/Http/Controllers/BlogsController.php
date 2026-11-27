<?php

namespace App\Http\Controllers;

use App\Enums\BlogTagType;
use App\Models\Blog;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BlogsController extends Controller
{
    public function show(Blog $blog): View
    {
        abort_if(Gate::denies('view', $blog), 404);

        $blog->load(['bricks', 'tags']);

        return view('site.blog', ['blog' => $blog]);
    }

    /**
     * The published blogs of a category.
     */
    public function category(string $slug): View
    {
        return $this->archive(BlogTagType::Category, $slug);
    }

    /**
     * The published blogs with a tag.
     */
    public function tag(string $slug): View
    {
        return $this->archive(BlogTagType::Tag, $slug);
    }

    protected function archive(BlogTagType $type, string $slug): View
    {
        $tag = $type->findBySlug($slug);

        abort_if($tag === null, 404);

        return view('site.blog-archive', ['type' => $type, 'tag' => $tag]);
    }
}
