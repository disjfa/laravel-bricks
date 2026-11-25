<x-layouts::site :title="$page->title" :description="$page->meta_description">
    @unless ($page->isPublished())
        <div class="bg-zinc-100 px-4 py-2 text-center text-sm text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
            {{ __('This page is a draft and only visible to editors.') }}
        </div>
    @endunless

    <article class="mx-auto max-w-3xl space-y-8 px-6 py-12">
        @foreach ($page->bricks as $brick)
            @if ($definition = $brick->definition())
                @include($definition->view(), ['data' => $brick->data])
            @endif
        @endforeach
    </article>
</x-layouts::site>
