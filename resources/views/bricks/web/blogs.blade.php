<section class="space-y-6">
    @if (filled($data['title'] ?? null))
        <h2 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $data['title'] }}</h2>
    @endif

    <livewire:blog-archive
        :per-page="(int) ($data['per_page'] ?? 6)"
        :sort="$data['sort'] ?? 'newest'"
        :wire:key="'blog-archive-'.($data['per_page'] ?? 6).'-'.($data['sort'] ?? 'newest')"
    />
</section>
