<?php

use App\Bricks\Brickable;
use App\Livewire\Concerns\ManagesMedia;
use App\Models\Blog;
use Livewire\Attributes\Layout;
use Illuminate\View\View;
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

    public function render(): View
    {
        return $this->view()->title(__(':title media', ['title' => $this->blog->title]));
    }
}; 