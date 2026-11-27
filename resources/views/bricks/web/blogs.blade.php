<section class="space-y-6">
    @if (filled($data['title'] ?? null))
        <h2 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $data['title'] }}</h2>
    @endif

    @php($categoryId = filled($data['category'] ?? null) ? (int) $data['category'] : null)

    <livewire:blog-archive
        :per-page="(int) ($data['per_page'] ?? 6)"
        :sort="$data['sort'] ?? 'newest'"
        :tag-id="$categoryId"
        :wire:key="'blog-archive-'.($data['per_page'] ?? 6).'-'.($data['sort'] ?? 'newest').'-'.$categoryId"
    />
</section>
