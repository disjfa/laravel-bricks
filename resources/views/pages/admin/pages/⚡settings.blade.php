<?php

use App\Models\Page;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::admin')] class extends Component {
    public Page $page;

    public string $title = '';

    public string $slug = '';

    public string $metaDescription = '';

    public bool $published = false;

    public function mount(Page $page): void
    {
        $this->authorize('update', $page);

        $this->page = $page;
        $this->title = $page->title;
        $this->slug = $page->slug;
        $this->metaDescription = $page->meta_description ?? '';
        $this->published = $page->published_at !== null;
    }

    public function save(): void
    {
        $this->authorize('update', $this->page);

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('pages', 'slug')->ignore($this->page),
            ],
            'metaDescription' => ['nullable', 'string', 'max:255'],
        ], [
            'slug.regex' => __('The slug may only contain lowercase letters, numbers and dashes.'),
        ]);

        $this->page->update([
            'title' => $this->title,
            'slug' => $this->slug,
            'meta_description' => $this->metaDescription ?: null,
            'published_at' => $this->published ? ($this->page->published_at ?? now()) : null,
        ]);

        Flux::toast(variant: 'success', text: __('Settings saved.'));
    }

    public function render()
    {
        return $this->view()->title(__(':title settings', ['title' => $this->page->title]));
    }
}; ?>

<div>
    <x-admin.page-header :page="$page" current="settings">
        <x-slot:actions>
            <flux:button size="sm" variant="primary" icon="check" wire:click="save" data-test="save-settings">{{ __('Save') }}</flux:button>
        </x-slot:actions>
    </x-admin.page-header>

    <form wire:submit="save" class="max-w-xl space-y-6">
        <flux:input wire:model="title" :label="__('Title')" required />

        <flux:input wire:model="slug" :label="__('Slug')" :description:trailing="__('The page is available at :url', ['url' => url('/').'/'.$slug])" required />

        <flux:textarea wire:model="metaDescription" :label="__('Meta description')" :description:trailing="__('Shown by search engines. Keep it under 160 characters.')" rows="3" />

        <flux:switch wire:model="published" :label="__('Published')" :description="__('Only published pages are visible on the website.')" />

        {{-- Lets Enter submit the form, the visible save button lives in the header bar. --}}
        <button type="submit" class="hidden"></button>
    </form>
</div>
