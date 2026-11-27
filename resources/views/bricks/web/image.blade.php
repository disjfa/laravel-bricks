@php($isFullWidth = ($width ?? null) === App\Enums\BrickWidth::Full)

<figure @class(['-mx-6' => $isFullWidth])>
    <img src="{{ $data['url'] ?? '' }}" alt="{{ $data['alt'] ?? '' }}" loading="lazy" @class(['w-full object-cover', 'rounded-xl' => ! $isFullWidth])>

    @if (filled($data['caption'] ?? null))
        <figcaption class="mt-3 text-center text-sm text-zinc-500 dark:text-zinc-400">{{ $data['caption'] }}</figcaption>
    @endif
</figure>
