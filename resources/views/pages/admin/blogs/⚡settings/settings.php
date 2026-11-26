<?php

use App\Enums\BlogTagType;
use App\Fields\Image;
use App\Livewire\Concerns\EditsSeo;
use App\Livewire\Forms\PublishingForm;
use App\Models\Blog;
use App\Rules\Slug;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Tags\Tag;

new #[Layout('layouts::admin')] class extends Component
{
    use EditsSeo;

    public Blog $blog;

    public string $title = '';

    public string $slug = '';

    public string $excerpt = '';

    public string $imageUrl = '';

    /**
     * The names of the categories. Names that do not exist yet are made when the blog is saved.
     *
     * @var array<int, string>
     */
    public array $categories = [];

    /**
     * The names of the tags. Names that do not exist yet are made when the blog is saved.
     *
     * @var array<int, string>
     */
    public array $tags = [];

    public PublishingForm $publishing;

    public function mount(Blog $blog): void
    {
        $this->authorize('update', $blog);

        $this->blog = $blog;
        $this->title = $blog->title;
        $this->slug = $blog->slug;
        $this->excerpt = $blog->excerpt ?? '';
        $this->imageUrl = $blog->image_url ?? '';
        $this->fillTags();
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
                new Slug,
                Rule::unique('blogs', 'slug')->ignore($this->blog),
            ],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'imageUrl' => $this->imageField()->getRules(),
            'categories' => ['array', 'max:10'],
            'categories.*' => ['string', 'max:50'],
            'tags' => ['array', 'max:30'],
            'tags.*' => ['string', 'max:50'],
        ], [], [
            'imageUrl' => __('image'),
            'categories.*' => __('category'),
            'tags.*' => __('tag'),
        ]);

        $this->blog->update([
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt ?: null,
            'image_url' => $this->imageUrl ?: null,
            ...$this->validatedSeo(),
            ...$this->publishing->toAttributes(),
        ]);

        $this->saveTags();

        Flux::toast(variant: 'success', text: __('Settings saved.'));
    }

    /**
     * Store the categories and tags. Names that do not exist yet are made.
     */
    protected function saveTags(): void
    {
        $this->blog->syncTagsWithType(self::cleanNames($this->categories), BlogTagType::Category->value);
        $this->blog->syncTagsWithType(self::cleanNames($this->tags), BlogTagType::Tag->value);

        $this->blog->load('tags');

        $this->fillTags();
    }

    protected function fillTags(): void
    {
        $this->categories = $this->blog->tagsOfType(BlogTagType::Category)->pluck('name')->all();
        $this->tags = $this->blog->tagsOfType(BlogTagType::Tag)->pluck('name')->all();
    }

    /**
     * Trimmed names without empty ones, and one of the names that only differ in capitals.
     *
     * @param  array<int, string>  $names
     * @return array<int, string>
     */
    protected static function cleanNames(array $names): array
    {
        return collect($names)
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->unique(fn (string $name) => mb_strtolower($name))
            ->values()
            ->all();
    }

    /**
     * Every tag of the type, to choose from.
     *
     * @return array<int, string>
     */
    public function tagNames(BlogTagType $type): array
    {
        return Tag::getWithType($type->value)
            ->whereInstanceOf(Tag::class)
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->pluck('name')
            ->all();
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
