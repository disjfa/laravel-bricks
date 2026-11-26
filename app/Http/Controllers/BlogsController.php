<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BlogsController extends Controller
{
    public function show(Blog $blog): View
    {
        abort_if(Gate::denies('view', $blog), 404);

        $blog->load('bricks');

        return view('site.blog', ['blog' => $blog]);
    }
}
