<?php

use App\Enums\BlogTagType;
use App\Fields\Image;
use App\Livewire\Concerns\EditsSeo;
use App\Livewire\Forms\PublishingForm;
use App\Models\Blog;
use App\Rules\Slug;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
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
     * The ids of the chosen categories.
     *
     * @var array<int, string>
     */
    public array $categories = [];

    /**
     * A category to add, it is made when the blog is saved.
     */
    public string $newCategory = '';

    /**
     * The tags, separated by commas.
     */
    public string $tags = '';

    public PublishingForm $publishing;

    public function mount(Blog $blog): void
    {
        $this->authorize('update', $blog);

        $this->blog = $blog;
        $this->title = $blog->title;
        $this->slug = $blog->slug;
        $this->excerpt = $blog->excerpt ?? '';
        $this->imageUrl = $blog->image_url ?? '';
        $this->categories = $blog->tagsOfType(BlogTagType::Category)->map(fn (Tag $tag) => (string) $tag->id)->all();
        $this->tags = $blog->tagsOfType(BlogTagType::Tag)->pluck('name')->implode(', ');
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
            'categories' => ['array'],
            'categories.*' => [Rule::exists('tags', 'id')->where('type', BlogTagType::Category->value)],
            'newCategory' => ['nullable', 'string', 'max:50'],
            'tags' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'imageUrl' => __('image'),
            'newCategory' => __('new category'),
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
     * Store the categories and tags. Tags and a new category are made when they do not exist yet.
     */
    protected function saveTags(): void
    {
        $categoryNames = $this->allCategories()
            ->whereIn('id', array_map('intval', $this->categories))
            ->pluck('name')
            ->when(filled(trim($this->newCategory)), fn ($names) => $names->push(trim($this->newCategory)))
            ->unique()
            ->all();

        $tagNames = collect(explode(',', $this->tags))
            ->map(fn (string $tag) => trim($tag))
            ->filter()
            ->unique(fn (string $tag) => mb_strtolower($tag))
            ->take(30)
            ->all();

        $this->blog->syncTagsWithType($categoryNames, BlogTagType::Category->value);
        $this->blog->syncTagsWithType($tagNames, BlogTagType::Tag->value);

        $this->blog->load('tags');
        unset($this->allCategories);

        $this->categories = $this->blog->tagsOfType(BlogTagType::Category)->map(fn (Tag $tag) => (string) $tag->id)->all();
        $this->tags = $this->blog->tagsOfType(BlogTagType::Tag)->pluck('name')->implode(', ');
        $this->reset('newCategory');
    }

    /**
     * Every category, to choose from.
     *
     * @return Collection<int, Tag>
     */
    #[Computed]
    public function allCategories(): Collection
    {
        return Tag::getWithType(BlogTagType::Category->value)
            ->whereInstanceOf(Tag::class)
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
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
