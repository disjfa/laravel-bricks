<?php

use App\Livewire\Concerns\EditsSeo;
use App\Livewire\Forms\PublishingForm;
use App\Models\Page;
use App\Rules\Slug;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::admin')] class extends Component
{
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
                new Slug,
                Rule::unique('pages', 'slug')->ignore($this->page),
            ],
        ]);

        $this->page->recordingRevision(function (): void {
            $this->page->update([
                'title' => $this->title,
                'slug' => $this->slug,
                ...$this->validatedSeo(),
                ...$this->publishing->toAttributes(),
            ]);
        });

        Flux::toast(variant: 'success', text: __('Settings saved.'));
    }

    public function render(): View
    {
        return $this->view()->title(__(':title settings', ['title' => $this->page->title]));
    }
};
