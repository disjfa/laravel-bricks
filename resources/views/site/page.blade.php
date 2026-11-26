<x-site-layout :seo="$page">
    @unless ($page->isPublished())
        <div class="bg-zinc-100 px-4 py-2 text-center text-sm text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
            {{ __('This page is not published (:state) and only visible to editors.', ['state' => mb_strtolower($page->publicationLabel())]) }}
        </div>
    @endunless

    <article class="space-y-8 py-12">
        @foreach ($page->bricks as $brick)
            @if ($definition = $brick->renderableDefinition())
                <x-brick :type="$definition" :data="$brick->data" :settings="$brick->settings ?? []" />
            @endif
        @endforeach
    </article>
</x-site-layout>
