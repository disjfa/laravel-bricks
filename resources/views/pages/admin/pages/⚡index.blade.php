<?php

use App\Models\Page;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::admin'), Title('Pages')] class extends Component {
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public string $title = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function createPage(): void
    {
        $this->authorize('create', Page::class);

        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $page = Page::query()->create($validated);

        $this->redirectRoute('admin.pages.edit', ['page' => $page], navigate: true);
    }

    public function deletePage(int $pageId): void
    {
        $page = Page::query()->findOrFail($pageId);

        $this->authorize('delete', $page);

        $page->delete();

        Flux::modal("delete-page-{$pageId}")->close();

        Flux::toast(variant: 'success', text: __('Page deleted.'));
    }

    /**
     * @return LengthAwarePaginator<int, Page>
     */
    #[Computed]
    public function pages(): LengthAwarePaginator
    {
        $this->authorize('viewAny', Page::class);

        return Page::query()
            ->withCount('bricks')
            ->when($this->search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->ordered()
            ->paginate(15);
    }
}; ?>

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Pages') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Pages are built from bricks.') }}</flux:text>
        </div>

        <div class="flex items-center gap-2">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Search pages...')" class="max-w-xs" clearable />

            @can('create', App\Models\Page::class)
                <flux:modal.trigger name="create-page">
                    <flux:button variant="primary" icon="plus" data-test="new-page-button">{{ __('New page') }}</flux:button>
                </flux:modal.trigger>
            @endcan
        </div>
    </div>

    <flux:table :paginate="$this->pages">
        <flux:table.columns>
            <flux:table.column class="w-0">#</flux:table.column>
            <flux:table.column>{{ __('Title') }}</flux:table.column>
            <flux:table.column>{{ __('Slug') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column>{{ __('Bricks') }}</flux:table.column>
            <flux:table.column>{{ __('Updated') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->pages as $page)
                <flux:table.row :key="$page->id">
                    <flux:table.cell class="tabular-nums">{{ $page->seqnr }}</flux:table.cell>

                    <flux:table.cell variant="strong">
                        <a href="{{ route('admin.pages.edit', $page) }}" wire:navigate class="hover:underline">{{ $page->title }}</a>
                    </flux:table.cell>

                    <flux:table.cell class="font-mono text-xs">/{{ $page->slug }}</flux:table.cell>

                    <flux:table.cell>
                        <x-admin.publication-badge :model="$page" inset="top bottom" />
                    </flux:table.cell>

                    <flux:table.cell>{{ $page->bricks_count }}</flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">{{ $page->updated_at?->diffForHumans() }}</flux:table.cell>

                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-1">
                            <flux:button size="sm" variant="ghost" icon="eye" :href="$page->url()" target="_blank" inset="top bottom" :aria-label="__('View')" />

                            @can('update', $page)
                                <flux:button size="sm" variant="ghost" icon="pencil-square" :href="route('admin.pages.edit', $page)" wire:navigate inset="top bottom" :aria-label="__('Edit')" />
                            @endcan

                            @can('delete', $page)
                                <flux:modal.trigger :name="'delete-page-'.$page->id">
                                    <flux:button size="sm" variant="ghost" icon="trash" inset="top bottom" :aria-label="__('Delete')" />
                                </flux:modal.trigger>

                                <flux:modal :name="'delete-page-'.$page->id" class="max-w-md">
                                    <div class="space-y-6 text-start">
                                        <div>
                                            <flux:heading size="lg">{{ __('Delete page?') }}</flux:heading>
                                            <flux:text class="mt-2">{{ __('":title" and all of its bricks will be removed.', ['title' => $page->title]) }}</flux:text>
                                        </div>

                                        <div class="flex justify-end gap-2">
                                            <flux:modal.close>
                                                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                            </flux:modal.close>

                                            <flux:button variant="danger" wire:click="deletePage({{ $page->id }})">{{ __('Delete') }}</flux:button>
                                        </div>
                                    </div>
                                </flux:modal>
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="py-8 text-center">{{ __('No pages yet.') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="create-page" :show="$errors->has('title')" class="max-w-lg">
        <form wire:submit="createPage" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('New page') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Give your page a title, you can add bricks next.') }}</flux:text>
            </div>

            <flux:input wire:model="title" :label="__('Title')" required autofocus />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary">{{ __('Create page') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
