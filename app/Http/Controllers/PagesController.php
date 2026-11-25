<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PagesController extends Controller
{
    /**
     * Show the page with the "home" slug, or a default welcome screen when there is none.
     */
    public function home(): View
    {
        $page = $this->findViewablePage('home');

        if (! $page) {
            return view('welcome');
        }

        return $this->render($page);
    }

    /**
     * Fallback route: resolve any unmatched path to a page by its slug.
     */
    public function show(Request $request): View
    {
        $page = $this->findViewablePage($request->path());

        abort_if($page === null, 404);

        return $this->render($page);
    }

    protected function findViewablePage(string $slug): ?Page
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->first();

        if (! $page) {
            return null;
        }

        if (Gate::denies('view', $page)) {
            return null;
        }

        return $page;
    }

    protected function render(Page $page): View
    {
        $page->load('bricks');

        return view('site.page', ['page' => $page]);
    }
}
