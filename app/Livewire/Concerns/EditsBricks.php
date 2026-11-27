<?php

namespace App\Livewire\Concerns;

use App\Bricks\Brickable;
use App\Bricks\BrickRegistry;
use App\Bricks\BrickType;
use App\Models\Brick;
use Flux\Flux;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;

/**
 * The brick editor of a page or blog. Render it with the partials.admin.brick-editor view.
 */
trait EditsBricks
{
    /**
     * The bricks being edited, in display order. Saved bricks carry their database id.
     * The data is what the brick shows, the settings how it is shown (like its width).
     *
     * @var array<int, array{key: string, id: int|null, type: string, data: array<string, mixed>, settings: array<string, mixed>}>
     */
    public array $bricks = [];

    /**
     * The page or blog being edited.
     */
    abstract protected function brickable(): Brickable;

    protected function loadBricks(): void
    {
        $this->bricks = $this->brickable()->bricks()->get()
            ->filter(fn (Brick $brick) => $brick->definition() !== null)
            ->map(fn (Brick $brick) => [
                'key' => (string) Str::uuid(),
                'id' => $brick->id,
                'type' => $brick->type,
                'data' => array_merge($brick->definition()->defaults(), $brick->data),
                'settings' => array_merge($brick->definition()->settingsDefaults(), $brick->settings ?? []),
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
            'settings' => $brickType->settingsDefaults(),
        ];

        $index ??= count($this->bricks);

        array_splice($this->bricks, $index, 0, [$brick]);

        $this->dispatchBricksUpdated();
    }

    public function duplicateBrick(string $key): void
    {
        $index = $this->indexOfBrick($key);

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
        array_splice($this->bricks, $this->indexOfBrick($key), 1);

        $this->resetValidation();
        $this->dispatchBricksUpdated();
    }

    /**
     * Handler for the up and down buttons. The editor scrolls along with the moved brick (brick-moved).
     */
    public function moveBrick(string $key, int $direction): void
    {
        $this->sortBrick($key, $this->indexOfBrick($key) + $direction);

        $this->dispatch('brick-moved', key: $key);
    }

    /**
     * Handler for wire:sort.
     */
    public function sortBrick(string $key, int $position): void
    {
        $index = $this->indexOfBrick($key);
        $position = max(0, min($position, count($this->bricks) - 1));

        $brick = array_splice($this->bricks, $index, 1);
        array_splice($this->bricks, $position, 0, $brick);

        $this->resetValidation();
        $this->dispatchBricksUpdated();
    }

    public function save(): void
    {
        $brickable = $this->brickable();

        $this->authorize('update', $brickable);

        $this->validate([
            'bricks' => ['array'],
            'bricks.*.type' => ['required', Rule::in(app(BrickRegistry::class)->keys())],
        ]);

        $bricks = $this->validatedBricks();

        $brickable->recordingRevision(function () use ($brickable, $bricks) {
            $brickable->bricks()
                ->whereNotIn('id', array_filter(Arr::pluck($bricks, 'id')))
                ->delete();

            foreach ($bricks as $position => $brick) {
                $attributes = [
                    'type' => $brick['type'],
                    'data' => $brick['data'],
                    'settings' => $brick['settings'],
                    'position' => $position,
                ];

                $saved = $brick['id']
                    ? tap($brickable->bricks()->findOrFail($brick['id']))->update($attributes)
                    : $brickable->bricks()->create($attributes);

                $this->bricks[$position]['id'] = $saved->id;
            }

            $brickable->touch();
        });

        Flux::toast(variant: 'success', text: __('Saved.'));
    }

    /**
     * @return array<int, array{id: int, name: string, url: string, thumb: string}>
     */
    #[Computed]
    public function imageMedia(): array
    {
        return $this->brickable()->imageMediaForPicker();
    }

    /**
     * Let the live preview know the unsaved bricks changed.
     */
    protected function dispatchBricksUpdated(): void
    {
        $this->dispatch('bricks-updated', bricks: $this->bricks);
    }

    /**
     * Validate the data and settings of every brick against its type and keep only the known fields.
     *
     * @return array<int, array{key: string, id: int|null, type: string, data: array<string, mixed>, settings: array<string, mixed>}>
     */
    protected function validatedBricks(): array
    {
        $errors = [];
        $bricks = [];

        foreach ($this->bricks as $index => $brick) {
            $brickType = $this->brickType($brick['type']);
            $validated = [];

            foreach (['data' => $brickType->validator($brick['data']), 'settings' => $brickType->settingsValidator($brick['settings'])] as $part => $validator) {
                foreach ($validator->errors()->messages() as $field => $messages) {
                    $errors["bricks.{$index}.{$part}.{$field}"] = $messages;
                }

                $validated[$part] = $validator->errors()->isEmpty() ? $validator->validated() : [];
            }

            $bricks[] = [...$brick, ...$validated];
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

    protected function indexOfBrick(string $key): int
    {
        $index = collect($this->bricks)->search(fn (array $brick) => $brick['key'] === $key);

        abort_if($index === false, 404);

        return $index;
    }
}
