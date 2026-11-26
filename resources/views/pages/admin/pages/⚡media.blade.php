<?php

use App\Bricks\Brickable;
use App\Livewire\Concerns\ManagesMedia;
use App\Models\Page;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts::admin')] class extends Component {
    use ManagesMedia;
    use WithFileUploads;

    public Page $page;

    public function mount(Page $page): void
    {
        $this->authorize('update', $page);

        $this->page = $page;
    }

    protected function brickable(): Brickable
    {
        return $this->page;
    }

    public function render()
    {
        return $this->view()->title(__(':title media', ['title' => $this->page->title]));
    }
}; ?>

<x-admin.content-screen :model="$page" current="media">
    @include('partials.admin.media-panel', ['mediaItems' => $this->mediaItems])
</x-admin.content-screen>
