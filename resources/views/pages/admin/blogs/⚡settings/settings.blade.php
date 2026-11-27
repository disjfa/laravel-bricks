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
                <flux:heading size="lg">{{ __('Categories and tags') }}</flux:heading>

                <x-admin.tag-select
                    model="categories"
                    :label="__('Categories')"
                    :options="$this->tagNames(App\Enums\BlogTagType::Category)"
                    :selected="$categories"
                    :placeholder="__('Choose or type a new category...')"
                    data-test="blog-categories"
                />

                <x-admin.tag-select
                    model="tags"
                    :label="__('Tags')"
                    :options="$this->tagNames(App\Enums\BlogTagType::Tag)"
                    :selected="$tags"
                    :placeholder="__('Choose or type a new tag...')"
                    data-test="blog-tags"
                />
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