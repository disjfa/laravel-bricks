<?php

use App\Bricks\BrickRegistry;
use App\Bricks\BrickType;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Renders bricks with the website views. Listens for "bricks-updated", which the page editor
 * dispatches with its unsaved bricks whenever they change.
 */
new class extends Component {
    /**
     * @var array<int, array{key: string, type: string, data: array<string, mixed>, settings?: array<string, mixed>}>
     */
    public array $bricks = [];

    /**
     * @param  array<int, array{key: string, type: string, data: array<string, mixed>, settings?: array<string, mixed>}>  $bricks
     */
    #[On('bricks-updated')]
    public function updateBricks(array $bricks): void
    {
        $this->bricks = $bricks;
    }

    /**
     * Bricks that are not valid yet have no data and render as a placeholder,
     * so unsafe or half-typed input never reaches the website views.
     *
     * Settings that are not valid fall back to the defaults.
     *
     * @return array<int, array{key: string, type: BrickType, data: array<string, mixed>|null, settings: array<string, mixed>}>
     */
    #[Computed]
    public function renderableBricks(): array
    {
        $registry = app(BrickRegistry::class);

        return collect($this->bricks)
            ->filter(fn (array $brick) => $registry->find($brick['type'] ?? '')?->isAvailable() ?? false)
            ->map(function (array $brick) use ($registry): array {
                $type = $registry->find($brick['type']);
                $validator = $type->validator($brick['data'] ?? []);
                $settingsValidator = $type->settingsValidator($brick['settings'] ?? []);

                return [
                    'key' => (string) ($brick['key'] ?? ''),
                    'type' => $type,
                    'data' => $validator->fails() ? null : $validator->validated(),
                    'settings' => $settingsValidator->fails() ? $type->settingsDefaults() : $settingsValidator->validated(),
                ];
            })
            ->values()
            ->all();
    }
}; ?>

<div class="overflow-x-hidden rounded-xl border lg:min-h-full border-zinc-200 antialiased dark:border-zinc-700" data-site-theme>
    @include('partials.site-theme')

    <article class="space-y-8 py-12">
        @forelse ($this->renderableBricks as $brick)
            @if ($brick['data'] === null)
                <div wire:key="preview-{{ $brick['key'] }}" class="mx-auto max-w-3xl px-6">
                    @include('bricks.preview-placeholder', ['type' => $brick['type']])
                </div>
            @else
                <x-brick wire:key="preview-{{ $brick['key'] }}" :type="$brick['type']" :data="$brick['data']" :settings="$brick['settings']" />
            @endif
        @empty
            <flux:text class="text-center">{{ __('Add bricks to see them here.') }}</flux:text>
        @endforelse
    </article>
</div>
