<x-layouts::site :seo="$blog">
    @unless ($blog->isPublished())
        <div class="bg-zinc-100 px-4 py-2 text-center text-sm text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
            {{ __('This blog is not published (:state) and only visible to editors.', ['state' => mb_strtolower($blog->publicationLabel())]) }}
        </div>
    @endunless

    <article class="space-y-8 py-12">
        <header class="mx-auto max-w-3xl space-y-3 px-6">
            @if ($date = $blog->displayDate())
                <time datetime="{{ $date->toDateString() }}" class="text-sm text-zinc-500 dark:text-zinc-400">{{ $date->isoFormat('LL') }}</time>
            @endif

            <h1 class="text-4xl font-bold tracking-tight text-balance text-zinc-900 sm:text-5xl dark:text-white">{{ $blog->title }}</h1>

            @if (filled($blog->excerpt))
                <p class="text-xl text-pretty text-zinc-600 dark:text-zinc-300">{{ $blog->excerpt }}</p>
            @endif
        </header>

        @if (filled($imageUrl = $blog->image_url))
            <div class="mx-auto max-w-3xl px-6">
                <img src="{{ $imageUrl }}" alt="" class="aspect-video w-full rounded-2xl object-cover">
            </div>
        @endif

        @foreach ($blog->bricks as $brick)
            @if ($definition = $brick->renderableDefinition())
                <x-brick :type="$definition" :data="$brick->data" :settings="$brick->settings ?? []" />
            @endif
        @endforeach
    </article>
</x-layouts::site>
