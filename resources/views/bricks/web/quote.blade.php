<figure class="border-s-4 border-zinc-900 ps-6 dark:border-white">
    <blockquote class="text-2xl font-medium text-pretty italic">
        &ldquo;{{ $data['quote'] ?? '' }}&rdquo;
    </blockquote>

    @if (filled($data['citation'] ?? null))
        <figcaption class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">&mdash; {{ $data['citation'] }}</figcaption>
    @endif
</figure>
