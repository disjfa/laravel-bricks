<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Blogs') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Blogs are shown at /blog/{slug} and listed by the blogs brick.') }}</flux:text>
        </div>

        <div class="flex items-center gap-2">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Search blogs...')" class="max-w-xs" clearable />

            @can('create', App\Models\Blog::class)
                <flux:modal.trigger name="create-blog">
                    <flux:button variant="primary" icon="plus" data-test="new-blog-button">{{ __('New blog') }}</flux:button>
                </flux:modal.trigger>
            @endcan
        </div>
    </div>

    <flux:table :paginate="$this->blogs">
        <flux:table.columns>
            <x-admin.sortable-column column="title" :direction="$this->sortDirection('title')">{{ __('Title') }}</x-admin.sortable-column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <x-admin.sortable-column column="published_at" :direction="$this->sortDirection('published_at')">{{ __('Publish date') }}</x-admin.sortable-column>
            <x-admin.sortable-column column="seqnr" :direction="$this->sortDirection('seqnr')">{{ __('Seq.') }}</x-admin.sortable-column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->blogs as $blog)
                <flux:table.row :key="$blog->id">
                    <flux:table.cell variant="strong">
                        <a href="{{ route('admin.blogs.edit', $blog) }}" wire:navigate class="hover:underline">{{ $blog->title }}</a>
                        <flux:text class="font-mono text-xs">/blog/{{ $blog->slug }}</flux:text>
                    </flux:table.cell>

                    <flux:table.cell>
                        <x-admin.publication-badge :model="$blog" inset="top bottom" />
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">{{ $blog->published_at?->isoFormat('LLL') ?? '—' }}</flux:table.cell>

                    <flux:table.cell class="tabular-nums">{{ $blog->seqnr }}</flux:table.cell>

                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-1">
                            <flux:button size="sm" variant="ghost" icon="eye" :href="$blog->url()" target="_blank" inset="top bottom" :aria-label="__('View')" />

                            @can('update', $blog)
                                <flux:button size="sm" variant="ghost" icon="pencil-square" :href="route('admin.blogs.edit', $blog)" wire:navigate inset="top bottom" :aria-label="__('Edit')" />
                            @endcan

                            @can('delete', $blog)
                                <flux:modal.trigger :name="'delete-blog-'.$blog->id">
                                    <flux:button size="sm" variant="ghost" icon="trash" inset="top bottom" :aria-label="__('Delete')" />
                                </flux:modal.trigger>

                                <flux:modal :name="'delete-blog-'.$blog->id" class="max-w-md">
                                    <div class="space-y-6 text-start">
                                        <div>
                                            <flux:heading size="lg">{{ __('Delete blog?') }}</flux:heading>
                                            <flux:text class="mt-2">{{ __('":title" will be removed.', ['title' => $blog->title]) }}</flux:text>
                                        </div>

                                        <div class="flex justify-end gap-2">
                                            <flux:modal.close>
                                                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                            </flux:modal.close>

                                            <flux:button variant="danger" wire:click="deleteBlog({{ $blog->id }})">{{ __('Delete') }}</flux:button>
                                        </div>
                                    </div>
                                </flux:modal>
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="py-8 text-center">{{ __('No blogs yet.') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="create-blog" :show="$errors->has('title')" class="max-w-lg">
        <form wire:submit="createBlog" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('New blog') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Give your blog a title, you can write it next.') }}</flux:text>
            </div>

            <flux:input wire:model="title" :label="__('Title')" required autofocus />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary">{{ __('Create blog') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>