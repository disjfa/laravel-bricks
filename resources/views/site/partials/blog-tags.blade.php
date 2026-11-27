{{-- The categories and tags of a blog, as links to their archive pages. --}}
@php
    $categories = $blog->tagsOfType(App\Enums\BlogTagType::Category);
    $tags = $blog->tagsOfType(App\Enums\BlogTagType::Tag);
@endphp

@if ($categories->isNotEmpty() || $tags->isNotEmpty())
    <div class="flex flex-wrap items-center gap-2 text-sm" data-test="blog-tags">
        @foreach ($categories as $category)
            <a href="{{ App\Enums\BlogTagType::Category->url($category) }}" class="rounded-full bg-(--color-accent) px-3 py-1 font-medium text-(--color-accent-foreground) hover:opacity-90">{{ $category->name }}</a>
        @endforeach

        @foreach ($tags as $tag)
            <a href="{{ App\Enums\BlogTagType::Tag->url($tag) }}" class="rounded-full border border-current/20 px-3 py-1 hover:border-current/50">#{{ $tag->name }}</a>
        @endforeach
    </div>
@endif
