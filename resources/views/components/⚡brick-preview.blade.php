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
     * @var array<int, array{key: string, type: string, data: array<string, mixed>}>
     */
    public array $bricks = [];

    /**
     * @param  array<int, array{key: string, type: string, data: array<string, mixed>}>  $bricks
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
     * @return array<int, array{key: string, type: BrickType, data: array<string, mixed>|null}>
     */
    #[Computed]
    public function renderableBricks(): array
    {
        $registry = app(BrickRegistry::class);

        return collect($this->bricks)
            ->filter(fn (array $brick) => $registry->find($brick['type'] ?? '') !== null)
            ->map(function (array $brick) use ($registry): array {
                $type = $registry->find($brick['type']);
                $validator = $type->validator($brick['data'] ?? []);

                return [
                    'key' => (string) ($brick['key'] ?? ''),
                    'type' => $type,
                    'data' => $validator->fails() ? null : $validator->validated(),
                ];
            })
            ->values()
            ->all();
    }
}; ?>

<div class="overflow-x-hidden rounded-xl border lg:min-h-full border-zinc-200 bg-white text-zinc-800 antialiased dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
    <article class="mx-auto max-w-3xl space-y-8 px-6 py-12">
        @forelse ($this->renderableBricks as $brick)
            <div wire:key="preview-{{ $brick['key'] }}">
                @if ($brick['data'] === null)
                    @include('bricks.preview-placeholder', ['type' => $brick['type']])
                @else
                    @include($brick['type']->view(), ['data' => $brick['data']])
                @endif
            </div>
        @empty
            <flux:text class="text-center">{{ __('Add bricks to see them here.') }}</flux:text>
        @endforelse
    </article>
</div>
