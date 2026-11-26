<?php

use App\Bricks\Brickable;
use App\Livewire\Concerns\EditsBricks;
use App\Models\Page;
use Livewire\Attributes\Layout;
use Illuminate\View\View;
use Livewire\Component;

new #[Layout('layouts::admin')] class extends Component {
    use EditsBricks;

    public Page $page;

    public function mount(Page $page): void
    {
        $this->authorize('update', $page);

        $this->page = $page;

        $this->loadBricks();
    }

    protected function brickable(): Brickable
    {
        return $this->page;
    }

    public function render(): View
    {
        return $this->view()->title(__('Edit :title', ['title' => $this->page->title]));
    }
}; 