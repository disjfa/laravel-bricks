<?php

use App\MenuLinks\MenuLinkRegistry;
use App\MenuLinks\MenuLinkType;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Rules\Slug;
use Flux\Flux;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::admin')] class extends Component
{
    public Menu $menu;

    public string $name = '';

    public string $handle = '';

    /**
     * The items being edited, in display order. Saved items carry their database id.
     * Like bricks, the link type of an item decides which data it has (App\MenuLinks).
     *
     * @var array<int, array{key: string, id: int|null, label: string, type: string, data: array<string, mixed>}>
     */
    public array $items = [];

    public function mount(Menu $menu): void
    {
        $this->authorize('update', $menu);

        $this->menu = $menu;
        $this->name = $menu->name;
        $this->handle = $menu->handle;

        $this->items = $menu->items
            ->filter(fn (MenuItem $item) => $item->definition() !== null)
            ->map(fn (MenuItem $item) => [
                'key' => (string) Str::uuid(),
                'id' => $item->id,
                'label' => $item->label,
                'type' => $item->type,
                'data' => array_merge($item->definition()->defaults(), $item->data),
            ])
            ->values()
            ->all();
    }

    public function addItem(string $type): void
    {
        $linkType = $this->linkType($type);
        $data = $linkType->defaults();

        $this->items[] = [
            'key' => (string) Str::uuid(),
            'id' => null,
            'label' => $linkType->suggestedLabel($data) ?? '',
            'type' => $linkType->key(),
            'data' => $data,
        ];
    }

    /**
     * Use the label the link type suggests (like the title of the picked page) when the label is still empty.
     */
    public function updatedItems(mixed $value, string $property): void
    {
        [$index, $part] = array_pad(explode('.', $property), 2, null);
        $index = (int) $index;

        if ($part !== 'data' || ! isset($this->items[$index]) || filled($this->items[$index]['label'])) {
            return;
        }

        $item = $this->items[$index];

        $this->items[$index]['label'] = $this->linkType($item['type'])->suggestedLabel($item['data']) ?? '';
    }

    public function removeItem(string $key): void
    {
        array_splice($this->items, $this->indexOf($key), 1);

        $this->resetValidation();
    }

    public function moveItem(string $key, int $direction): void
    {
        $this->sortItem($key, $this->indexOf($key) + $direction);
    }

    /**
     * Handler for wire:sort.
     */
    public function sortItem(string $key, int $position): void
    {
        $index = $this->indexOf($key);
        $position = max(0, min($position, count($this->items) - 1));

        $item = array_splice($this->items, $index, 1);
        array_splice($this->items, $position, 0, $item);

        $this->resetValidation();
    }

    public function save(): void
    {
        $this->authorize('update', $this->menu);

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'handle' => ['required', 'string', 'max:64', new Slug, Rule::unique('menus', 'handle')->ignore($this->menu)],
            'items' => ['array', 'max:50'],
            'items.*.label' => ['required', 'string', 'max:255'],
            'items.*.type' => ['required', Rule::in(app(MenuLinkRegistry::class)->keys())],
        ], [], [
            'items.*.label' => __('label'),
        ]);

        $items = $this->validatedItems();

        DB::transaction(function () use ($items) {
            $this->menu->update([
                'name' => $this->name,
                'handle' => $this->handle,
            ]);

            $this->menu->items()
                ->whereNotIn('id', array_filter(Arr::pluck($items, 'id')))
                ->delete();

            foreach ($items as $position => $item) {
                $attributes = [
                    'label' => $item['label'],
                    'type' => $item['type'],
                    'data' => $item['data'],
                    'position' => $position,
                ];

                $saved = $item['id']
                    ? tap($this->menu->items()->findOrFail($item['id']))->update($attributes)
                    : $this->menu->items()->create($attributes);

                $this->items[$position]['id'] = $saved->id;
            }
        });

        Flux::toast(variant: 'success', text: __('Menu saved.'));
    }

    /**
     * Validate the data of every item against its link type and keep only the known fields.
     *
     * @return array<int, array{key: string, id: int|null, label: string, type: string, data: array<string, mixed>}>
     */
    protected function validatedItems(): array
    {
        $errors = [];
        $items = [];

        foreach ($this->items as $index => $item) {
            $validator = $this->linkType($item['type'])->validator($item['data']);

            foreach ($validator->errors()->messages() as $field => $messages) {
                $errors["items.{$index}.data.{$field}"] = $messages;
            }

            $items[] = [...$item, 'data' => $validator->errors()->isEmpty() ? $validator->validated() : []];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $items;
    }

    public function linkType(string $key): MenuLinkType
    {
        $linkType = app(MenuLinkRegistry::class)->find($key);

        abort_if($linkType === null, 404);

        return $linkType;
    }

    protected function indexOf(string $key): int
    {
        $index = collect($this->items)->search(fn (array $item) => $item['key'] === $key);

        abort_if($index === false, 404);

        return $index;
    }

    public function render(): View
    {
        return $this->view()->title(__('Edit :name', ['name' => $this->menu->name]));
    }
};
