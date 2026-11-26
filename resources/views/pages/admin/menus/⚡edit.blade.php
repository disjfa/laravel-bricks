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

        if (! in_array($attribute, ['page_id', 'blog_id'], true) || filled($this->items[$index]['label'] ?? null)) {
            return;
        }

        $title = $attribute === 'page_id'
            ? Page::query()->whereKey($value)->value('title')
            : Blog::query()->whereKey($value)->value('title');

        $this->items[$index]['label'] = $title ?? '';
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

    public function render()
    {
        return $this->view()->title(__('Edit :name', ['name' => $this->menu->name]));
    }
}; ?>

<x-admin.screen :title="$menu->name" :subtitle="$menu->handle" :back="route('admin.menus.index')" save="save">
    <form wire:submit="save" class="grid items-start gap-6 lg:grid-cols-3">
        <div class="space-y-2 lg:col-span-2">
            <div wire:sort="sortItem" class="space-y-2">
                @foreach ($items as $index => $item)
                    @php($type = App\Enums\MenuItemType::from($item['type']))

                    <div wire:key="item-{{ $item['key'] }}" wire:sort:item="{{ $item['key'] }}" class="group/item">
                        <flux:card class="p-4! transition group-hover/item:ring-2 group-hover/item:ring-zinc-200 dark:group-hover/item:ring-zinc-600">
                            <div class="flex items-start gap-3">
                                <div wire:sort:handle class="mt-8 cursor-grab text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200" title="{{ __('Drag to reorder') }}">
                                    <flux:icon name="bars-2" variant="micro" />
                                </div>

                                <div wire:sort:ignore class="grid flex-1 gap-4 sm:grid-cols-2">
                                    <flux:input wire:model="items.{{ $index }}.label" :label="__('Label')" />

                                    @if ($type === App\Enums\MenuItemType::Page)
                                        <flux:select wire:model.live="items.{{ $index }}.page_id" :label="__('Page')" :placeholder="__('Choose a page...')">
                                            @foreach ($this->pageOptions as $pageId => $pageTitle)
                                                <flux:select.option :value="$pageId">{{ $pageTitle }}</flux:select.option>
                                            @endforeach
                                        </flux:select>
                                    @elseif ($type === App\Enums\MenuItemType::Blog)
                                        <flux:select wire:model.live="items.{{ $index }}.blog_id" :label="__('Blog')" :placeholder="__('Choose a blog...')">
                                            @foreach ($this->blogOptions as $blogId => $blogTitle)
                                                <flux:select.option :value="$blogId">{{ $blogTitle }}</flux:select.option>
                                            @endforeach
                                        </flux:select>
                                    @else
                                        <flux:input wire:model="items.{{ $index }}.url" :label="__('Link')" placeholder="/contact" />
                                    @endif
                                </div>

                                <div wire:sort:ignore class="mt-6 flex items-center gap-0.5 opacity-60 transition group-hover/item:opacity-100">
                                    <flux:button size="xs" variant="ghost" icon="chevron-up" wire:click="moveItem('{{ $item['key'] }}', -1)" :disabled="$loop->first" :aria-label="__('Move up')" />
                                    <flux:button size="xs" variant="ghost" icon="chevron-down" wire:click="moveItem('{{ $item['key'] }}', 1)" :disabled="$loop->last" :aria-label="__('Move down')" />
                                    <flux:button size="xs" variant="ghost" icon="trash" wire:click="removeItem('{{ $item['key'] }}')" :aria-label="__('Remove')" />
                                </div>
                            </div>
                        </flux:card>
                    </div>
                @endforeach
            </div>

            @if ($items === [])
                <div class="rounded-xl border-2 border-dashed border-zinc-200 px-6 py-16 text-center dark:border-zinc-700">
                    <flux:icon name="bars-3" class="mx-auto size-10 text-zinc-400" />
                    <flux:heading class="mt-4">{{ __('This menu has no items yet') }}</flux:heading>
                    <flux:text class="mt-1">{{ __('Link to a page, a blog or any other address.') }}</flux:text>
                </div>
            @endif

            <div class="flex justify-center pt-2">
                <flux:dropdown position="bottom" align="center">
                    <flux:button icon="plus" data-test="add-menu-item">{{ __('Add item') }}</flux:button>

                    <flux:menu>
                        <flux:menu.item icon="document-text" wire:click="addItem('{{ App\Enums\MenuItemType::Page->value }}')">{{ App\Enums\MenuItemType::Page->label() }}</flux:menu.item>
                        @feature(App\Features\Blogs::class)
                            <flux:menu.item icon="newspaper" wire:click="addItem('{{ App\Enums\MenuItemType::Blog->value }}')">{{ App\Enums\MenuItemType::Blog->label() }}</flux:menu.item>
                        @endfeature
                        <flux:menu.item icon="link" wire:click="addItem('{{ App\Enums\MenuItemType::Url->value }}')">{{ App\Enums\MenuItemType::Url->label() }}</flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </div>

        <flux:card class="space-y-6 lg:sticky lg:top-0">
            <flux:heading size="lg">{{ __('Settings') }}</flux:heading>

            <flux:input wire:model="name" :label="__('Name')" required />

            <flux:input wire:model="handle" :label="__('Handle')" :description:trailing="__('Use :main for the header menu or :footer for the footer menu.', ['main' => App\Models\Menu::MainHandle, 'footer' => App\Models\Menu::FooterHandle])" required class:input="font-mono" />

            <flux:text size="sm">{{ __('Items that link to a page or blog that is not published are hidden on the website.') }}</flux:text>
        </flux:card>

        <button type="submit" class="hidden"></button>
    </form>
</x-admin.screen>
