<x-site-layout :title="$tag->name" :description="__('Blogs about :name', ['name' => $tag->name])">
    <section class="mx-auto max-w-3xl space-y-8 px-6 py-12">
        <header class="space-y-2">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $type->label() }}</p>
            <h1 class="text-4xl font-bold tracking-tight text-balance text-zinc-900 sm:text-5xl dark:text-white">{{ $tag->name }}</h1>
        </header>

        <livewire:blog-archive :tag-id="$tag->id" />
    </section>
</x-site-layout>
