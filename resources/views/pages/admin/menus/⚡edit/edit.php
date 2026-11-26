<?php

use App\Enums\MenuItemType;
use App\Models\Blog;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Flux\Flux;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Illuminate\View\View;
use Livewire\Component;

new #[Layout('layouts::admin')] class extends Component {
    public Menu $menu;

    public string $name = '';

    public string $handle = '';

    /**
     * The items being edited, in display order. Saved items carry their database id.
     *
     * @var array<int, array{key: string, id: int|null, label: string, type: string, page_id: int|string|null, blog_id: int|string|null, url: string}>
     */
    public array $items = [];

    public function mount(Menu $menu): void
    {
        $this->authorize('update', $menu);

        $this->menu = $menu;
        $this->name = $menu->name;
        $this->handle = $menu->handle;

        $this->items = $menu->items
            ->map(fn (MenuItem $item) => [
                'key' => (string) Str::uuid(),
                'id' => $item->id,
                'label' => $item->label,
                'type' => $item->type->value,
                'page_id' => $item->page_id,
                'blog_id' => $item->blog_id,
                'url' => $item->url ?? '',
            ])
            ->all();
    }

    public function addItem(string $type): void
    {
        $itemType = MenuItemType::tryFrom($type);

        abort_if($itemType === null, 404);

        $this->items[] = [
            'key' => (string) Str::uuid(),
            'id' => null,
            'label' => '',
            'type' => $itemType->value,
            'page_id' => null,
            'blog_id' => null,
            'url' => '',
        ];
    }

    /**
     * Use the title of the picked page or blog as label when the label is still empty.
     */
    public function updatedItems(mixed $value, string $property): void
    {
        [$index, $attribute] = array_pad(explode('.', $property), 2, null);
        $index = (int) $index;

        if (! in_array($attribute, ['page_id', 'blog_id'], true) || ! isset($this->items[$index]) || filled($this->items[$index]['label'])) {
            return;
        }

        $title = $attribute === 'page_id'
            ? Page::query()->whereKey($value)->value('title')
            : Blog::query()->whereKey($value)->value('title');

        $this->items[$index]['label'] = is_string($title) ? $title : '';
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
            'handle' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('menus', 'handle')->ignore($this->menu)],
            'items' => ['array', 'max:50'],
            'items.*.label' => ['required', 'string', 'max:255'],
            'items.*.type' => ['required', Rule::enum(MenuItemType::class)],
            'items.*.page_id' => ['nullable', 'required_if:items.*.type,'.MenuItemType::Page->value, Rule::exists('pages', 'id')],
            'items.*.blog_id' => ['nullable', 'required_if:items.*.type,'.MenuItemType::Blog->value, Rule::exists('blogs', 'id')],
            // Relative links, anchors, mail links or http(s) URLs, which keeps `javascript:` URLs out of the menu.
            'items.*.url' => ['nullable', 'required_if:items.*.type,'.MenuItemType::Url->value, 'string', 'max:2048', 'regex:/^(\/|#|https?:\/\/|mailto:)/i'],
        ], [
            'handle.regex' => __('The handle may only contain lowercase letters, numbers and dashes.'),
            'items.*.page_id.required_if' => __('Pick a page.'),
            'items.*.blog_id.required_if' => __('Pick a blog.'),
            'items.*.url.required_if' => __('Enter a link.'),
        ], [
            'items.*.label' => __('label'),
            'items.*.url' => __('link'),
        ]);

        DB::transaction(function () {
            $this->menu->update([
                'name' => $this->name,
                'handle' => $this->handle,
            ]);

            $this->menu->items()
                ->whereNotIn('id', array_filter(Arr::pluck($this->items, 'id')))
                ->delete();

            foreach ($this->items as $position => $item) {
                $type = MenuItemType::from($item['type']);

                $attributes = [
                    'label' => $item['label'],
                    'type' => $type,
                    'page_id' => $type === MenuItemType::Page ? (int) $item['page_id'] : null,
                    'blog_id' => $type === MenuItemType::Blog ? (int) $item['blog_id'] : null,
                    'url' => $type === MenuItemType::Url ? $item['url'] : null,
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
     * @return array<int, string>
     */
    #[Computed]
    public function pageOptions(): array
    {
        return Page::query()->ordered()->pluck('title', 'id')->all();
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function blogOptions(): array
    {
        return Blog::query()->latest('published_at')->latest('id')->pluck('title', 'id')->all();
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