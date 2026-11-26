<?php

use App\Fields\Image;
use App\Livewire\Concerns\EditsSeo;
use App\Livewire\Forms\PublishingForm;
use App\Models\Blog;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::admin')] class extends Component
{
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

    public function render(): View
    {
        return $this->view()->title(__(':title settings', ['title' => $this->blog->title]));
    }
};
