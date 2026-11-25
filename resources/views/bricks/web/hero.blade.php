@php($hasImage = filled($data['image_url'] ?? null))

<section @class([
    'relative -mx-6 overflow-hidden rounded-2xl px-6 py-20 text-center sm:-mx-12 sm:px-12',
    'bg-linear-to-br from-zinc-100 to-zinc-200 dark:from-zinc-800 dark:to-zinc-950' => ! $hasImage,
    'bg-zinc-900 text-white' => $hasImage,
])>
    @if ($hasImage)
        <img src="{{ $data['image_url'] }}" alt="" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-black/50"></div>
    @endif

    <div class="relative">
        <h1 class="text-4xl font-bold tracking-tight text-balance sm:text-5xl">{{ $data['title'] ?? '' }}</h1>

        @if (filled($data['subtitle'] ?? null))
            <p @class(['mx-auto mt-6 max-w-xl text-lg text-pretty', 'text-zinc-600 dark:text-zinc-300' => ! $hasImage, 'text-white/85' => $hasImage])>{{ $data['subtitle'] }}</p>
        @endif

        @if (filled($data['button_label'] ?? null) && filled($data['button_url'] ?? null))
            <div class="mt-10">
                <flux:button variant="primary" :href="$data['button_url']">{{ $data['button_label'] }}</flux:button>
            </div>
        @endif
    </div>
</section>
