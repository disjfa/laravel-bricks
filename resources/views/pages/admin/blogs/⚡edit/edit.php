<?php

use App\Bricks\Brickable;
use App\Livewire\Concerns\EditsBricks;
use App\Models\Blog;
use Livewire\Attributes\Layout;
use Illuminate\View\View;
use Livewire\Component;

new #[Layout('layouts::admin')] class extends Component {
    use EditsBricks;

    public Blog $blog;

    public function mount(Blog $blog): void
    {
        $this->authorize('update', $blog);

        $this->blog = $blog;

        $this->loadBricks();
    }

    protected function brickable(): Brickable
    {
        return $this->blog;
    }

    public function render(): View
    {
        return $this->view()->title(__('Edit :title', ['title' => $this->blog->title]));
    }
}; 