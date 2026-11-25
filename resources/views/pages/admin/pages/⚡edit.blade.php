<?php

use App\Bricks\BrickRegistry;
use App\Bricks\BrickType;
use App\Models\Page;
use Flux\Flux;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::admin')] class extends Component {
    public Page $page;

    /**
     * The bricks being edited, in display order. Saved bricks carry their database id.
     *
     * @var array<int, array{key: string, id: int|null, type: string, data: array<string, mixed>}>
     */
    public array $bricks = [];

    public function mount(Page $page): void
    {
        $this->authorize('update', $page);

        $this->page = $page;

        $this->bricks = $page->bricks
            ->filter(fn ($brick) => $brick->definition() !== null)
            ->map(fn ($brick) => [
                'key' => (string) Str::uuid(),
                'id' => $brick->id,
                'type' => $brick->type,
                'data' => array_merge($brick->definition()->defaults(), $brick->data),
            ])
            ->values()
            ->all();
    }

    public function updated(string $property): void
    {
        if (Str::startsWith($property, 'bricks.')) {
            $this->dispatchBricksUpdated();
        }
    }

    public function addBrick(string $type, ?int $index = null): void
    {
        $brickType = $this->brickType($type);

        $brick = [
            'key' => (string) Str::uuid(),
            'id' => null,
            'type' => $brickType->key(),
            'data' => $brickType->defaults(),
        ];

        $index ??= count($this->bricks);

        array_splice($this->bricks, $index, 0, [$brick]);

        $this->dispatchBricksUpdated();
    }

    public function duplicateBrick(string $key): void
    {
        $index = $this->indexOf($key);

        $copy = [
            ...$this->bricks[$index],
            'key' => (string) Str::uuid(),
            'id' => null,
        ];

        array_splice($this->bricks, $index + 1, 0, [$copy]);

        $this->dispatchBricksUpdated();
    }

    public function removeBrick(string $key): void
    {
        array_splice($this->bricks, $this->indexOf($key), 1);

        $this->resetValidation();
        $this->dispatchBricksUpdated();
    }

    public function moveBrick(string $key, int $direction): void
    {
        $this->sortBrick($key, $this->indexOf($key) + $direction);
    }

    /**
     * Handler for wire:sort.
     */
    public function sortBrick(string $key, int $position): void
    {
        $index = $this->indexOf($key);
        $position = max(0, min($position, count($this->bricks) - 1));

        $brick = array_splice($this->bricks, $index, 1);
        array_splice($this->bricks, $position, 0, $brick);

        $this->resetValidation();
        $this->dispatchBricksUpdated();
    }

    public function save(): void
    {
        $this->authorize('update', $this->page);

        $this->validate([
            'bricks' => ['array'],
            'bricks.*.type' => ['required', Rule::in(app(BrickRegistry::class)->keys())],
        ]);

        $bricks = $this->validatedBricks();

        DB::transaction(function () use ($bricks) {
            $this->page->bricks()
                ->whereNotIn('id', array_filter(Arr::pluck($bricks, 'id')))
                ->delete();

            foreach ($bricks as $position => $brick) {
                $attributes = [
                    'type' => $brick['type'],
                    'data' => $brick['data'],
                    'position' => $position,
                ];

                $saved = $brick['id']
                    ? tap($this->page->bricks()->findOrFail($brick['id']))->update($attributes)
                    : $this->page->bricks()->create($attributes);

                $this->bricks[$position]['id'] = $saved->id;
            }

            $this->page->touch();
        });

        Flux::toast(variant: 'success', text: __('Page saved.'));
    }

    /**
     * Images attached to the page in the media panel, offered in the image pickers of the bricks.
     *
     * @return array<int, array{id: int, name: string, url: string, thumb: string}>
     */
    #[Computed]
    public function imageMedia(): array
    {
        return $this->page->media()
            ->where('collection_name', Page::MediaCollection)
            ->where('mime_type', 'like', 'image/%')
            ->latest('id')
            ->get()
            ->map(fn ($media) => [
                'id' => $media->id,
                'name' => $media->name,
                'url' => $media->getUrl(),
                'thumb' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
            ])
            ->all();
    }

    /**
     * Let the live preview know the unsaved bricks changed.
     */
    protected function dispatchBricksUpdated(): void
    {
        $this->dispatch('bricks-updated', bricks: $this->bricks);
    }

    /**
     * Validate every brick against the rules of its type and keep only the known fields.
     *
     * @return array<int, array{key: string, id: int|null, type: string, data: array<string, mixed>}>
     */
    protected function validatedBricks(): array
    {
        $errors = [];
        $bricks = [];

        foreach ($this->bricks as $index => $brick) {
            $validator = $this->brickType($brick['type'])->validator($brick['data']);

            foreach ($validator->errors()->messages() as $field => $messages) {
                $errors["bricks.{$index}.data.{$field}"] = $messages;
            }

            $bricks[] = [...$brick, 'data' => $validator->errors()->isEmpty() ? $validator->validated() : []];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $bricks;
    }

    protected function brickType(string $key): BrickType
    {
        $brickType = app(BrickRegistry::class)->find($key);

        abort_if($brickType === null, 404);

        return $brickType;
    }

    protected function indexOf(string $key): int
    {
        $index = collect($this->bricks)->search(fn (array $brick) => $brick['key'] === $key);

        abort_if($index === false, 404);

        return $index;
    }

    public function render()
    {
        return $this->view()->title(__('Edit :title', ['title' => $this->page->title]));
    }
}; ?>

{{--
    Full-bleed editor (flux:main drops its padding for data-full-bleed). From lg up it fills the screen:
    a header row, then two columns that each scroll on their own with their own padding.
--}}
<div data-full-bleed class="lg:grid lg:h-dvh lg:grid-rows-[auto_minmax(0,1fr)]">
    <x-admin.page-header :page="$page" current="content" class="mb-0! px-6 pt-6">
        <x-slot:actions>
            <flux:button size="sm" variant="primary" icon="check" wire:click="save" data-test="save-page">{{ __('Save') }}</flux:button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid lg:min-h-0 lg:grid-cols-2">
        {{-- Canvas --}}
        <div class="space-y-2 p-6 lg:min-h-0 lg:overflow-y-auto lg:overscroll-contain">
            <div wire:sort="sortBrick" class="space-y-2">
                @foreach ($bricks as $index => $brick)
                    @php($type = $this->brickType($brick['type']))

                    <div wire:key="brick-{{ $brick['key'] }}" wire:sort:item="{{ $brick['key'] }}" class="group/brick">
                        <flux:card class="space-y-4 p-4! transition group-hover/brick:ring-2 group-hover/brick:ring-zinc-200 dark:group-hover/brick:ring-zinc-600">
                            <div class="flex items-center gap-2">
                                <div wire:sort:handle class="cursor-grab text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200" title="{{ __('Drag to reorder') }}">
                                    <flux:icon name="bars-2" variant="micro" />
                                </div>

                                <flux:icon :name="$type->icon()" variant="micro" class="text-zinc-500" />
                                <flux:text size="sm" class="font-medium">{{ $type->label() }}</flux:text>

                                <flux:spacer />

                                <div wire:sort:ignore class="flex items-center gap-0.5 opacity-60 transition group-hover/brick:opacity-100">
                                    <flux:button size="xs" variant="ghost" icon="chevron-up" wire:click="moveBrick('{{ $brick['key'] }}', -1)" :disabled="$loop->first" :aria-label="__('Move up')" />
                                    <flux:button size="xs" variant="ghost" icon="chevron-down" wire:click="moveBrick('{{ $brick['key'] }}', 1)" :disabled="$loop->last" :aria-label="__('Move down')" />
                                    <flux:button size="xs" variant="ghost" icon="document-duplicate" wire:click="duplicateBrick('{{ $brick['key'] }}')" :aria-label="__('Duplicate')" />
                                    <flux:button size="xs" variant="ghost" icon="trash" wire:click="removeBrick('{{ $brick['key'] }}')" :aria-label="__('Remove')" />
                                </div>
                            </div>

                            <div wire:sort:ignore class="grid gap-4 sm:grid-cols-2">
                                @forelse ($type->fields() as $field)
                                    <div @class(['sm:col-span-2' => ! $field->isHalfWidth()]) wire:key="field-{{ $brick['key'] }}-{{ $field->name }}">
                                        @include($field->view(), [
                                            'field' => $field,
                                            'path' => "bricks.{$index}.data.{$field->name}",
                                            'value' => $brick['data'][$field->name] ?? null,
                                            'imageMedia' => $this->imageMedia,
                                        ])
                                    </div>
                                @empty
                                    <flux:text size="sm" class="sm:col-span-2">{{ __('This brick has no settings.') }}</flux:text>
                                @endforelse
                            </div>
                        </flux:card>

                        {{-- Inserter between bricks --}}
                        <div wire:sort:ignore class="flex h-6 items-center justify-center opacity-0 transition group-hover/brick:opacity-100 focus-within:opacity-100">
                            @include('bricks.editor.inserter', ['position' => $index + 1, 'compact' => true])
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($bricks === [])
                <div class="rounded-xl border-2 border-dashed border-zinc-200 px-6 py-16 text-center dark:border-zinc-700">
                    <flux:icon name="squares-plus" class="mx-auto size-10 text-zinc-400" />
                    <flux:heading class="mt-4">{{ __('This page has no bricks yet') }}</flux:heading>
                    <flux:text class="mt-1">{{ __('Add your first brick to start building.') }}</flux:text>
                </div>
            @endif

            <div class="flex justify-center pt-2">
                @include('bricks.editor.inserter', ['position' => null, 'compact' => false])
            </div>
        </div>

        {{-- Live preview, rendered with the website brick views --}}
        {{-- The column itself scrolls (like the canvas), so both scrollbars run the full height. --}}
        <div class="p-6 lg:min-h-0 lg:overflow-y-auto lg:overscroll-contain lg:border-s lg:border-zinc-200 lg:dark:border-zinc-700" data-test="page-preview">
            {{-- Zero-height sticky row: the badge sits on the card's top edge and stays pinned while scrolling. --}}
            <div class="pointer-events-none sticky top-6 z-10 flex h-0 justify-end">
                <div class="me-3 -mt-3 flex items-center gap-1.5 rounded-full bg-white/90 px-2.5 py-1 text-xs font-medium text-zinc-600 shadow-sm ring-1 ring-zinc-200 backdrop-blur dark:bg-zinc-800/90 dark:text-zinc-300 dark:ring-zinc-700" title="{{ __('Unsaved changes included') }}">
                    <span class="size-1.5 animate-pulse rounded-full bg-green-500"></span>
                    {{ __('Live preview') }}
                </div>
            </div>

            <livewire:brick-preview :bricks="$bricks" wire:key="brick-preview" />
        </div>
    </div>
</div>
