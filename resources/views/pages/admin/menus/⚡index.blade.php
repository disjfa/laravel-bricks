<?php

use App\Models\Menu;
use App\Rules\Slug;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::admin'), Title('Menus')] class extends Component {
    public string $name = '';

    public string $handle = '';

    public function createMenu(): void
    {
        $this->authorize('create', Menu::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'handle' => ['required', 'string', 'max:64', new Slug, Rule::unique('menus', 'handle')],
        ]);

        $menu = Menu::query()->create($validated);

        $this->redirectRoute('admin.menus.edit', ['menu' => $menu], navigate: true);
    }

    public function deleteMenu(int $menuId): void
    {
        $menu = Menu::query()->findOrFail($menuId);

        $this->authorize('delete', $menu);

        $menu->delete();

        Flux::modal("delete-menu-{$menuId}")->close();

        Flux::toast(variant: 'success', text: __('Menu deleted.'));
    }

    /**
     * @return Collection<int, Menu>
     */
    #[Computed]
    public function menus(): Collection
    {
        $this->authorize('viewAny', Menu::class);

        return Menu::query()->withCount('items')->orderBy('name')->get();
    }
}; ?>

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Menus') }}</flux:heading>
            <flux:text class="mt-1">{{ __('The website shows the menu with the handle ":main" in the header and ":footer" in the footer.', ['main' => App\Models\Menu::MainHandle, 'footer' => App\Models\Menu::FooterHandle]) }}</flux:text>
        </div>

        @can('create', App\Models\Menu::class)
            <flux:modal.trigger name="create-menu">
                <flux:button variant="primary" icon="plus" data-test="new-menu-button">{{ __('New menu') }}</flux:button>
            </flux:modal.trigger>
        @endcan
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Handle') }}</flux:table.column>
            <flux:table.column>{{ __('Items') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->menus as $menu)
                <flux:table.row :key="$menu->id">
                    <flux:table.cell variant="strong">
                        <a href="{{ route('admin.menus.edit', $menu) }}" wire:navigate class="hover:underline">{{ $menu->name }}</a>
                    </flux:table.cell>

                    <flux:table.cell class="font-mono text-xs">{{ $menu->handle }}</flux:table.cell>

                    <flux:table.cell>{{ $menu->items_count }}</flux:table.cell>

                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-1">
                            @can('update', $menu)
                                <flux:button size="sm" variant="ghost" icon="pencil-square" :href="route('admin.menus.edit', $menu)" wire:navigate inset="top bottom" :aria-label="__('Edit')" />
                            @endcan

                            @can('delete', $menu)
                                <flux:modal.trigger :name="'delete-menu-'.$menu->id">
                                    <flux:button size="sm" variant="ghost" icon="trash" inset="top bottom" :aria-label="__('Delete')" />
                                </flux:modal.trigger>

                                <flux:modal :name="'delete-menu-'.$menu->id" class="max-w-md">
                                    <div class="space-y-6 text-start">
                                        <div>
                                            <flux:heading size="lg">{{ __('Delete menu?') }}</flux:heading>
                                            <flux:text class="mt-2">{{ __('":name" and its items will be removed.', ['name' => $menu->name]) }}</flux:text>
                                        </div>

                                        <div class="flex justify-end gap-2">
                                            <flux:modal.close>
                                                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                            </flux:modal.close>

                                            <flux:button variant="danger" wire:click="deleteMenu({{ $menu->id }})">{{ __('Delete') }}</flux:button>
                                        </div>
                                    </div>
                                </flux:modal>
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="py-8 text-center">{{ __('No menus yet.') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="create-menu" :show="$errors->has('name') || $errors->has('handle')" class="max-w-lg">
        <form wire:submit="createMenu" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('New menu') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Use the handle ":main" for the header menu or ":footer" for the footer menu.', ['main' => App\Models\Menu::MainHandle, 'footer' => App\Models\Menu::FooterHandle]) }}</flux:text>
            </div>

            <flux:input wire:model="name" :label="__('Name')" required autofocus />
            <flux:input wire:model="handle" :label="__('Handle')" :placeholder="App\Models\Menu::MainHandle" required class:input="font-mono" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary">{{ __('Create menu') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
