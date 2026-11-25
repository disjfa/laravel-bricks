<figure>
    <img src="{{ $data['url'] ?? '' }}" alt="{{ $data['alt'] ?? '' }}" loading="lazy" class="w-full rounded-xl object-cover">

    @if (filled($data['caption'] ?? null))
        <figcaption class="mt-3 text-center text-sm text-zinc-500 dark:text-zinc-400">{{ $data['caption'] }}</figcaption>
    @endif
</figure>
