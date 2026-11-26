<?php

use App\Fields\Image;
use App\Livewire\Concerns\EditsSeo;
use App\Livewire\Forms\PublishingForm;
use App\Models\Blog;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::admin')] class extends Component {
    use EditsSeo;

    public Blog $blog;

    public string $title = '';

    public string $slug = '';

    public string $excerpt = '';

    public string $imageUrl = '';

    public PublishingForm $publishing;

    public function mount(Blog $blog): void
    {
        $this->authorize('update', $blog);

        $this->blog = $blog;
        $this->title = $blog->title;
        $this->slug = $blog->slug;
        $this->excerpt = $blog->excerpt ?? '';
        $this->imageUrl = $blog->image_url ?? '';
        $this->fillSeo($blog->only(['meta_title', 'meta_description', 'meta_image', 'noindex']));
        $this->publishing->fillFromModel($blog);
    }

    public function save(): void
    {
        $this->authorize('update', $this->blog);

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('blogs', 'slug')->ignore($this->blog),
            ],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'imageUrl' => $this->imageField()->getRules(),
        ], [
            'slug.regex' => __('The slug may only contain lowercase letters, numbers and dashes.'),
        ], [
            'imageUrl' => __('image'),
        ]);

        $this->blog->update([
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt ?: null,
            'image_url' => $this->imageUrl ?: null,
            ...$this->validatedSeo(),
            ...$this->publishing->toAttributes(),
        ]);

        Flux::toast(variant: 'success', text: __('Settings saved.'));
    }

    public function imageField(): Image
    {
        return Image::make('imageUrl')
            ->label(__('Image'))
            ->help(__('Shown above the blog and in the blog overview.'));
    }

    /**
     * @return array<int, array{id: int, name: string, url: string, thumb: string}>
     */
    #[Computed]
    public function imageMedia(): array
    {
        return $this->blog->imageMediaForPicker();
    }

    public function render()
    {
        return $this->view()->title(__(':title settings', ['title' => $this->blog->title]));
    }
}; ?>

<x-admin.content-screen :model="$blog" current="settings" save="save">
    <form wire:submit="save" class="grid items-start gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <flux:card class="space-y-6">
                <flux:heading size="lg">{{ __('General') }}</flux:heading>
                <div class="grid gap-6 sm:grid-cols-2">
                    <flux:input wire:model="title" :label="__('Title')" required />

                    <flux:input wire:model="slug" :label="__('Slug')" required />
                </div>

                <flux:textarea wire:model="excerpt" :label="__('Excerpt')" :description:trailing="__('Shown in the blog overview and above the blog.')" rows="3" />

                @include('fields.image', [
                    'field' => $this->imageField(),
                    'path' => 'imageUrl',
                    'value' => $imageUrl,
                    'imageMedia' => $this->imageMedia,
                    'mediaUrl' => route('admin.blogs.media', $blog),
                ])
            </flux:card>

            <flux:card class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('SEO') }}</flux:heading>
                    <flux:text class="mt-1">{{ __('What search engines and social media show. Empty fields use the defaults from the site settings.') }}</flux:text>
                </div>

                <x-admin.seo-fields :meta-image="$metaImage" :image-media="$blog->imageMediaForPicker()" :media-url="route('admin.blogs.media', $blog)" />
            </flux:card>
        </div>

        <flux:card class="space-y-6 lg:sticky lg:top-0">
            <div>
                <flux:heading size="lg">{{ __('Publishing') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Only active blogs within their publish dates are visible on the website.') }}</flux:text>
            </div>

            <x-admin.publishing-fields class="lg:grid-cols-1" />
        </flux:card>

        {{-- Lets Enter submit the form, the visible save button lives in the header bar. --}}
        <button type="submit" class="hidden"></button>
    </form>
</x-admin.content-screen>
