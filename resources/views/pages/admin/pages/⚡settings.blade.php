<?php

use App\Livewire\Concerns\EditsSeo;
use App\Livewire\Forms\PublishingForm;
use App\Models\Page;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::admin')] class extends Component {
    use EditsSeo;

    public Page $page;

    public string $title = '';

    public string $slug = '';

    public PublishingForm $publishing;

    public function mount(Page $page): void
    {
        $this->authorize('update', $page);

        $this->page = $page;
        $this->title = $page->title;
        $this->slug = $page->slug;
        $this->fillSeo($page->only(['meta_title', 'meta_description', 'meta_image', 'noindex']));
        $this->publishing->fillFromModel($page);
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
        ], [
            'slug.regex' => __('The slug may only contain lowercase letters, numbers and dashes.'),
        ]);

        $this->page->update([
            'title' => $this->title,
            'slug' => $this->slug,
            ...$this->validatedSeo(),
            ...$this->publishing->toAttributes(),
        ]);

        Flux::toast(variant: 'success', text: __('Settings saved.'));
    }

    public function render()
    {
        return $this->view()->title(__(':title settings', ['title' => $this->page->title]));
    }
}; ?>

<x-admin.content-screen :model="$page" current="settings" save="save">
    <form wire:submit="save" class="grid items-start gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <flux:card class="space-y-6">
                <flux:heading size="lg">{{ __('General') }}</flux:heading>
                <div class="grid gap-6 sm:grid-cols-2">
                    <flux:input wire:model="title" :label="__('Title')" required />

                    <flux:input wire:model="slug" :label="__('Slug')" required />
                </div>
            </flux:card>

            <flux:card class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('SEO') }}</flux:heading>
                    <flux:text class="mt-1">{{ __('What search engines and social media show. Empty fields use the defaults from the site settings.') }}</flux:text>
                </div>

                <x-admin.seo-fields :meta-image="$metaImage" :image-media="$page->imageMediaForPicker()" :media-url="route('admin.pages.media', $page)" />
            </flux:card>
        </div>

        <flux:card class="space-y-6 lg:sticky lg:top-0">
            <div>
                <flux:heading size="lg">{{ __('Publishing') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Only active pages within their publish dates are visible on the website.') }}</flux:text>
            </div>

            <x-admin.publishing-fields class="lg:grid-cols-1" />
        </flux:card>

        {{-- Lets Enter submit the form, the visible save button lives in the header bar. --}}
        <button type="submit" class="hidden"></button>
    </form>
</x-admin.content-screen>
