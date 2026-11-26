<?php

use App\Bricks\Brickable;
use App\Livewire\Concerns\ManagesMedia;
use App\Models\Blog;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts::admin')] class extends Component {
    use ManagesMedia;
    use WithFileUploads;

    public Blog $blog;

    public function mount(Blog $blog): void
    {
        $this->authorize('update', $blog);

        $this->blog = $blog;
    }

    protected function brickable(): Brickable
    {
        return $this->blog;
    }

    public function render()
    {
        return $this->view()->title(__(':title media', ['title' => $this->blog->title]));
    }
}; ?>

<x-admin.content-screen :model="$blog" current="media">
    @include('partials.admin.media-panel', ['mediaItems' => $this->mediaItems])
</x-admin.content-screen>
